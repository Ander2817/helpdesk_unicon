<?php
/**
 * includes/reporte_datos.php
 * Calcula todas las cifras del reporte para un período. Lo usan el PDF y el CSV, así los dos
 * archivos nunca pueden mostrar números distintos.
 *
 * Definiciones (también aparecen al final del PDF):
 *  - Creados:   tickets registrados dentro del período.
 *  - Resueltos: tickets cuya fecha de cierre cae dentro del período (sin importar cuándo se crearon).
 *  - SLA:       un ticket cumple si se resolvió dentro del tiempo objetivo de su prioridad.
 *  - Activos / sin asignar: foto del momento en que se genera el reporte (no dependen del período).
 */
require_once __DIR__ . '/../includes/constants.php';

const REPORTE_MAX_DIAS = 366;

/** Valida el rango pedido. Siempre devuelve fechas coherentes aunque lleguen mal. */
function reporte_rango(?string $desde, ?string $hasta): array {
    $hoy = new DateTimeImmutable('today');

    $leer = function (?string $texto) {
        if ($texto === null || $texto === '') return null;
        $f = DateTimeImmutable::createFromFormat('!Y-m-d', $texto);
        return ($f && $f->format('Y-m-d') === $texto) ? $f : null;   // rechaza fechas como 2026-02-31
    };

    $h = $leer($hasta) ?? $hoy;
    $d = $leer($desde) ?? $h->modify('-6 months')->modify('first day of this month');

    if ($d > $h) { [$d, $h] = [$h, $d]; }
    if ($h > $hoy) $h = $hoy;
    if ($d > $h) $d = $h;

    $ajustado = false;
    if ($d->diff($h)->days >= REPORTE_MAX_DIAS) {
        $d = $h->modify('-' . (REPORTE_MAX_DIAS - 1) . ' days');
        $ajustado = true;
    }
    return [
        'desde'    => $d->format('Y-m-d'),
        'hasta'    => $h->format('Y-m-d'),
        'dias'     => $d->diff($h)->days + 1,
        'ajustado' => $ajustado,
    ];
}

function reporte_formato_duracion(int $minutos): string {
    $h = intdiv($minutos, 60); $m = $minutos % 60;
    $t = trim(($h > 0 ? "{$h} h " : '') . ($m > 0 ? "{$m} min" : ''));
    return $t === '' ? '0 min' : $t;
}

function reporte_escalar(mysqli $c, string $sql, string $tipos = '', ...$params) {
    $st = $c->prepare($sql);
    if ($tipos !== '') $st->bind_param($tipos, ...$params);
    $st->execute();
    $fila = $st->get_result()->fetch_row();
    $st->close();
    return $fila[0] ?? 0;
}

function reporte_filas(mysqli $c, string $sql, string $tipos = '', ...$params): array {
    $st = $c->prepare($sql);
    if ($tipos !== '') $st->bind_param($tipos, ...$params);
    $st->execute();
    $filas = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    return $filas;
}

function reporte_obtener(mysqli $c, string $desde, string $hasta): array {
    $ini = $desde . ' 00:00:00';
    $fin = $hasta . ' 23:59:59';
    $cerrados = TICKET_RESUELTO . ',' . TICKET_CERRADO;

    $d = ['desde' => $desde, 'hasta' => $hasta];

    // ---------- Resumen ----------
    $d['creados'] = (int)reporte_escalar($c, "SELECT COUNT(*) FROM tickets WHERE fecha_creacion BETWEEN ? AND ?", 'ss', $ini, $fin);
    $d['resueltos'] = (int)reporte_escalar($c, "SELECT COUNT(*) FROM tickets WHERE id_estado IN ($cerrados) AND fecha_cierre BETWEEN ? AND ?", 'ss', $ini, $fin);
    $d['criticos_creados'] = (int)reporte_escalar($c, "
        SELECT COUNT(*) FROM tickets t JOIN prioridades p ON t.id_prioridad = p.id_prioridad
        WHERE p.nivel = (SELECT MAX(nivel) FROM prioridades) AND t.fecha_creacion BETWEEN ? AND ?", 'ss', $ini, $fin);
    $d['activos_hoy'] = (int)reporte_escalar($c, "SELECT COUNT(*) FROM tickets WHERE id_estado NOT IN ($cerrados)");
    $d['sin_asignar_hoy'] = (int)reporte_escalar($c, "SELECT COUNT(*) FROM tickets WHERE id_usuario_asignado IS NULL AND id_estado NOT IN ($cerrados)");

    $min = reporte_escalar($c, "
        SELECT AVG(TIMESTAMPDIFF(MINUTE, fecha_creacion, fecha_cierre)) FROM tickets
        WHERE id_estado IN ($cerrados) AND fecha_cierre BETWEEN ? AND ?", 'ss', $ini, $fin);
    $d['tiempo_prom_horas'] = ($min !== null && $d['resueltos'] > 0) ? round($min / 60, 1) : null;

    $sla = reporte_filas($c, "
        SELECT COUNT(*) total,
               COALESCE(SUM(TIMESTAMPDIFF(MINUTE, t.fecha_creacion, t.fecha_cierre) <= p.tiempo_respuesta_objetivo), 0) dentro
        FROM tickets t JOIN prioridades p ON t.id_prioridad = p.id_prioridad
        WHERE t.id_estado IN ($cerrados) AND t.fecha_cierre BETWEEN ? AND ?", 'ss', $ini, $fin)[0];
    $d['sla_total']  = (int)$sla['total'];
    $d['sla_dentro'] = (int)$sla['dentro'];
    $d['sla_pct']    = $d['sla_total'] > 0 ? (int)round($d['sla_dentro'] / $d['sla_total'] * 100) : null;

    // ---------- Volumen por mes ----------
    $creados_mes = array_column(reporte_filas($c, "
        SELECT DATE_FORMAT(fecha_creacion, '%Y-%m') m, COUNT(*) n FROM tickets
        WHERE fecha_creacion BETWEEN ? AND ? GROUP BY m", 'ss', $ini, $fin), 'n', 'm');
    $resueltos_mes = array_column(reporte_filas($c, "
        SELECT DATE_FORMAT(fecha_cierre, '%Y-%m') m, COUNT(*) n FROM tickets
        WHERE id_estado IN ($cerrados) AND fecha_cierre BETWEEN ? AND ? GROUP BY m", 'ss', $ini, $fin), 'n', 'm');

    $meses_es = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
    $d['por_mes'] = [];
    $cursor = (new DateTimeImmutable($desde))->modify('first day of this month');
    $limite = (new DateTimeImmutable($hasta))->modify('first day of this month');
    while ($cursor <= $limite) {
        $clave = $cursor->format('Y-m');
        $d['por_mes'][] = [
            'etiqueta'  => $meses_es[(int)$cursor->format('n')] . ' ' . $cursor->format('y'),
            'creados'   => (int)($creados_mes[$clave] ?? 0),
            'resueltos' => (int)($resueltos_mes[$clave] ?? 0),
        ];
        $cursor = $cursor->modify('+1 month');
    }

    // ---------- Rendimiento por técnico ----------
    $d['tecnicos'] = [];
    $filas = reporte_filas($c, "
        SELECT u.nombres, u.apellidos,
               COUNT(CASE WHEN t.id_estado IN ($cerrados) AND t.fecha_cierre BETWEEN ? AND ? THEN 1 END) AS resueltos,
               AVG(CASE WHEN t.id_estado IN ($cerrados) AND t.fecha_cierre BETWEEN ? AND ?
                        THEN TIMESTAMPDIFF(MINUTE, t.fecha_creacion, t.fecha_cierre) END) AS minutos_prom,
               COALESCE(SUM(CASE WHEN t.id_estado IN ($cerrados) AND t.fecha_cierre BETWEEN ? AND ?
                        THEN TIMESTAMPDIFF(MINUTE, t.fecha_creacion, t.fecha_cierre) <= p.tiempo_respuesta_objetivo END), 0) AS dentro_sla,
               COUNT(CASE WHEN t.id_estado IN (" . TICKET_PROCESO . "," . TICKET_PENDIENTE . ") THEN 1 END) AS activos
        FROM usuarios u
        LEFT JOIN tickets t ON t.id_usuario_asignado = u.id_usuario
        LEFT JOIN prioridades p ON t.id_prioridad = p.id_prioridad
        WHERE u.id_rol = " . ROL_TECNICO . " AND u.estado = 'activo'
        GROUP BY u.id_usuario
        ORDER BY resueltos DESC, u.nombres ASC", 'ssssss', $ini, $fin, $ini, $fin, $ini, $fin);
    foreach ($filas as $f) {
        $res = (int)$f['resueltos'];
        $d['tecnicos'][] = [
            'nombre'     => $f['nombres'] . ' ' . $f['apellidos'],
            'resueltos'  => $res,
            'activos'    => (int)$f['activos'],
            'horas_prom' => ($f['minutos_prom'] !== null && $res > 0) ? round($f['minutos_prom'] / 60, 1) : null,
            'sla_pct'    => $res > 0 ? (int)round(((int)$f['dentro_sla']) / $res * 100) : null,
        ];
    }

    // ---------- SLA por prioridad ----------
    $d['sla'] = [];
    $filas = reporte_filas($c, "
        SELECT p.nombre, p.nivel, p.tiempo_respuesta_objetivo AS objetivo,
               COUNT(t.id_tickets) AS resueltos,
               COALESCE(SUM(TIMESTAMPDIFF(MINUTE, t.fecha_creacion, t.fecha_cierre) <= p.tiempo_respuesta_objetivo), 0) AS dentro
        FROM prioridades p
        LEFT JOIN tickets t ON t.id_prioridad = p.id_prioridad
                           AND t.id_estado IN ($cerrados) AND t.fecha_cierre BETWEEN ? AND ?
        GROUP BY p.id_prioridad
        ORDER BY p.nivel DESC", 'ss', $ini, $fin);
    foreach ($filas as $f) {
        $res = (int)$f['resueltos'];
        $d['sla'][] = [
            'nombre'    => $f['nombre'],
            'objetivo'  => reporte_formato_duracion((int)$f['objetivo']),
            'resueltos' => $res,
            'dentro'    => (int)$f['dentro'],
            'pct'       => $res > 0 ? (int)round(((int)$f['dentro']) / $res * 100) : null,
        ];
    }

    // ---------- Por categoría y por departamento (tickets creados en el período) ----------
    $d['categorias'] = reporte_filas($c, "
        SELECT c.nombre, COUNT(t.id_tickets) AS total
        FROM categoria_incidencias c
        LEFT JOIN tickets t ON t.id_categoria = c.id_categoria AND t.fecha_creacion BETWEEN ? AND ?
        GROUP BY c.id_categoria ORDER BY total DESC, c.nombre ASC", 'ss', $ini, $fin);
    $d['departamentos'] = reporte_filas($c, "
        SELECT dp.nombre, COUNT(t.id_tickets) AS total
        FROM departamentos dp
        LEFT JOIN tickets t ON t.id_departamento = dp.id_departamento AND t.fecha_creacion BETWEEN ? AND ?
        GROUP BY dp.id_departamento ORDER BY total DESC, dp.nombre ASC", 'ss', $ini, $fin);

    return $d;
}

/** Detalle ticket por ticket (para el CSV): los creados O resueltos dentro del período. */
function reporte_detalle(mysqli $c, string $desde, string $hasta): array {
    $ini = $desde . ' 00:00:00';
    $fin = $hasta . ' 23:59:59';
    return reporte_filas($c, "
        SELECT t.codigo_ticket, t.asunto,
               CONCAT(sol.nombres, ' ', sol.apellidos) AS solicitante,
               dep.nombre AS departamento, cat.nombre AS categoria,
               p.nombre AS prioridad, p.tiempo_respuesta_objetivo AS objetivo,
               e.nombre AS estado, e.id_estado_ticket AS id_estado,
               CONCAT(asig.nombres, ' ', asig.apellidos) AS tecnico,
               t.fecha_creacion, t.fecha_cierre,
               (SELECT COUNT(*) FROM adjuntos ad WHERE ad.id_ticket = t.id_tickets) AS adjuntos
        FROM tickets t
        JOIN usuarios sol ON t.id_usuario_solicitante = sol.id_usuario
        JOIN departamentos dep ON t.id_departamento = dep.id_departamento
        JOIN categoria_incidencias cat ON t.id_categoria = cat.id_categoria
        JOIN prioridades p ON t.id_prioridad = p.id_prioridad
        JOIN estados_ticket e ON t.id_estado = e.id_estado_ticket
        LEFT JOIN usuarios asig ON t.id_usuario_asignado = asig.id_usuario
        WHERE t.fecha_creacion BETWEEN ? AND ?
           OR (t.id_estado IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ") AND t.fecha_cierre BETWEEN ? AND ?)
        ORDER BY t.fecha_creacion ASC", 'ssss', $ini, $fin, $ini, $fin);
}

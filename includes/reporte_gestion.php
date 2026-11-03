<?php
// includes/reporte_gestion.php — Datos del «Reporte de gestión completo»: tendencia mensual, comparación con el período anterior,
// equipos con fallas repetidas, SLA por prioridad (con el tiempo «Pendiente» descontado) y satisfacción. Sin tablas nuevas.
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/sla.php';

function rg_filas(mysqli $c, string $sql, string $tipos = '', ...$p): array {
    try {
        $s = $c->prepare($sql); if ($tipos !== '') $s->bind_param($tipos, ...$p);
        $s->execute(); $r = $s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close(); return $r;
    } catch (Throwable $e) { return []; }
}

/** KPIs de un período [desde, hasta] (fechas Y-m-d incluidas). */
function rg_kpis(mysqli $c, string $desde, string $hasta): array {
    $fin = $hasta . ' 23:59:59'; $ini = $desde . ' 00:00:00';
    $cre = rg_filas($c, "SELECT COUNT(*) n FROM tickets WHERE fecha_creacion BETWEEN ? AND ?", 'ss', $ini, $fin)[0]['n'] ?? 0;
    $ef = sla_minutos_efectivos_sql('t');
    $cer = rg_filas($c, "SELECT COUNT(*) n, AVG($ef) prom, SUM($ef <= p.tiempo_respuesta_objetivo) ok, SUM(p.tiempo_respuesta_objetivo > 0) con_meta
                         FROM tickets t JOIN prioridades p ON p.id_prioridad = t.id_prioridad
                         WHERE t.fecha_cierre BETWEEN ? AND ?", 'ss', $ini, $fin)[0] ?? [];
    $sat = rg_filas($c, "SELECT COUNT(*) n, AVG(e.puntuacion) prom FROM encuestas_ticket e WHERE e.fecha BETWEEN ? AND ?", 'ss', $ini, $fin)[0] ?? [];
    $n = (int)($cer['n'] ?? 0);
    return [
        'creados' => (int)$cre, 'cerrados' => $n,
        'prom_horas' => $n ? round(((float)$cer['prom']) / 60, 1) : null,
        'sla_pct' => $n && (int)$cer['con_meta'] ? (int)round(100 * (int)$cer['ok'] / (int)$cer['con_meta']) : null,
        'sat_prom' => (int)($sat['n'] ?? 0) ? round((float)$sat['prom'], 2) : null, 'sat_n' => (int)($sat['n'] ?? 0),
    ];
}

function rg_obtener(mysqli $c, string $desde, string $hasta): array {
    $dias = (int)((strtotime($hasta) - strtotime($desde)) / 86400) + 1;
    $p_hasta = date('Y-m-d', strtotime("$desde -1 day")); $p_desde = date('Y-m-d', strtotime("$p_hasta -" . ($dias - 1) . " days"));
    $actual = rg_kpis($c, $desde, $hasta); $previo = rg_kpis($c, $p_desde, $p_hasta);

    // Tendencia mensual: creados vs cerrados
    $mes = [];
    for ($t = strtotime(date('Y-m-01', strtotime($desde))); $t <= strtotime($hasta); $t = strtotime('+1 month', $t)) $mes[date('Y-m', $t)] = ['mes' => date('Y-m', $t), 'creados' => 0, 'cerrados' => 0];
    foreach (rg_filas($c, "SELECT DATE_FORMAT(fecha_creacion,'%Y-%m') m, COUNT(*) n FROM tickets WHERE fecha_creacion BETWEEN ? AND ? GROUP BY m", 'ss', "$desde 00:00:00", "$hasta 23:59:59") as $r) if (isset($mes[$r['m']])) $mes[$r['m']]['creados'] = (int)$r['n'];
    foreach (rg_filas($c, "SELECT DATE_FORMAT(fecha_cierre,'%Y-%m') m, COUNT(*) n FROM tickets WHERE fecha_cierre BETWEEN ? AND ? GROUP BY m", 'ss', "$desde 00:00:00", "$hasta 23:59:59") as $r) if (isset($mes[$r['m']])) $mes[$r['m']]['cerrados'] = (int)$r['n'];

    // Equipos con fallas repetidas (2 o más tickets en el período)
    $equipos = rg_filas($c, "SELECT i.codigo_equipo, i.tipo_equipo, CONCAT(i.marca,' ',i.modelo) AS equipo, COALESCE(d.nombre,'') AS depto, COUNT(*) n, MAX(t.fecha_creacion) ultimo
                             FROM tickets t JOIN inventario_tecnologico i ON i.id_inventario = t.id_inventario LEFT JOIN departamentos d ON d.id_departamento = i.id_departamento
                             WHERE t.fecha_creacion BETWEEN ? AND ? GROUP BY i.id_inventario HAVING n >= 2 ORDER BY n DESC, ultimo DESC LIMIT 10", 'ss', "$desde 00:00:00", "$hasta 23:59:59");

    // SLA por prioridad (tickets cerrados en el período)
    $ef = sla_minutos_efectivos_sql('t');
    $sla = rg_filas($c, "SELECT p.nombre, p.nivel, p.tiempo_respuesta_objetivo obj, COUNT(*) n, SUM($ef <= p.tiempo_respuesta_objetivo) ok, AVG($ef) prom, AVG(" . sla_pausa_sql('t') . ") pausa
                         FROM tickets t JOIN prioridades p ON p.id_prioridad = t.id_prioridad WHERE t.fecha_cierre BETWEEN ? AND ?
                         GROUP BY p.id_prioridad ORDER BY p.nivel DESC", 'ss', "$desde 00:00:00", "$hasta 23:59:59");
    foreach ($sla as &$s) { $s['n'] = (int)$s['n']; $s['ok'] = (int)$s['ok']; $s['pct'] = $s['n'] ? (int)round(100 * $s['ok'] / $s['n']) : null; $s['prom'] = (int)round((float)$s['prom']); $s['pausa'] = (int)round((float)$s['pausa']); } unset($s);

    // Satisfacción
    $sat_dist = array_fill(1, 5, 0);
    foreach (rg_filas($c, "SELECT puntuacion p, COUNT(*) n FROM encuestas_ticket WHERE fecha BETWEEN ? AND ? GROUP BY puntuacion", 'ss', "$desde 00:00:00", "$hasta 23:59:59") as $r) if (isset($sat_dist[(int)$r['p']])) $sat_dist[(int)$r['p']] = (int)$r['n'];
    $sat_tec = rg_filas($c, "SELECT CONCAT(u.nombres,' ',u.apellidos) nombre, COUNT(*) n, ROUND(AVG(e.puntuacion),2) prom
                             FROM encuestas_ticket e JOIN tickets t ON t.id_tickets = e.id_ticket JOIN usuarios u ON u.id_usuario = t.id_usuario_asignado
                             WHERE e.fecha BETWEEN ? AND ? GROUP BY u.id_usuario ORDER BY prom DESC, n DESC LIMIT 10", 'ss', "$desde 00:00:00", "$hasta 23:59:59");

    return ['desde' => $desde, 'hasta' => $hasta, 'dias' => $dias, 'prev_desde' => $p_desde, 'prev_hasta' => $p_hasta,
            'actual' => $actual, 'previo' => $previo, 'mensual' => array_values($mes), 'equipos' => $equipos, 'sla' => $sla,
            'sat_dist' => $sat_dist, 'sat_tec' => $sat_tec];
}

/** Variación porcentual entre dos valores (null si no se puede calcular). */
function rg_delta($a, $b): ?float { return ($a === null || $b === null || (float)$b == 0.0) ? null : round(((float)$a - (float)$b) * 100 / (float)$b, 1); }

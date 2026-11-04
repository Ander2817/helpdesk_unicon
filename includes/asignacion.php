<?php
// includes/asignacion.php — Asignación AUTOMÁTICA de tickets (sin tablas nuevas). La asignación manual del admin sigue funcionando igual.
//
// Regla (en este orden):
//   1) Solo técnicos con estado «activo» (los que están de vacaciones, reposo, suspendidos o inactivos NO reciben tickets).
//   2) Modo «departamento»: primero los técnicos del MISMO departamento del ticket; si no hay, cualquiera.   Modo «carga»: ignora el departamento.
//   3) Entre los candidatos, el que tenga MENOS tickets abiertos (Abierto, En proceso y Pendiente).
//   4) Si hay empate, el que lleva más tiempo sin recibir un ticket (reparto equitativo).
//   5) Opcional: tope de tickets abiertos por técnico (0 = sin tope). Si todos están en el tope, el ticket queda «sin asignar» para el admin.
// La configuración vive en sistema_estado: asig_auto (1/0), asig_modo (departamento|carga), asig_max (número).

require_once __DIR__ . '/constants.php';

function asig_get(mysqli $c, string $clave, string $def = ''): string {
    try {
        $s = $c->prepare("SELECT valor FROM sistema_estado WHERE clave = ?");
        $s->bind_param("s", $clave); $s->execute();
        $r = $s->get_result()->fetch_row(); $s->close();
        return ($r && $r[0] !== null && $r[0] !== '') ? (string)$r[0] : $def;
    } catch (Throwable $e) { return $def; }
}
function asig_set(mysqli $c, string $clave, string $valor): void {
    $s = $c->prepare("INSERT INTO sistema_estado (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
    $s->bind_param("ss", $clave, $valor); $s->execute(); $s->close();
}
function asig_config(mysqli $c): array {
    $modo = asig_get($c, 'asig_modo', 'departamento');
    return ['activa' => asig_get($c, 'asig_auto', '1') === '1',
            'modo'   => in_array($modo, ['departamento', 'carga'], true) ? $modo : 'departamento',
            'max'    => max(0, (int)asig_get($c, 'asig_max', '0'))];
}

/** Todos los técnicos con su carga actual y si pueden recibir tickets. */
function asig_tecnicos(mysqli $c): array {
    $ab = TICKET_RESUELTO . ',' . TICKET_CERRADO;
    $r = $c->query("SELECT u.id_usuario, u.nombres, u.apellidos, u.id_departamento, u.estado, COALESCE(d.nombre,'') AS departamento,
                           (SELECT COUNT(*) FROM tickets t WHERE t.id_usuario_asignado = u.id_usuario AND t.id_estado NOT IN ($ab)) AS abiertos,
                           COALESCE((SELECT MAX(t2.id_tickets) FROM tickets t2 WHERE t2.id_usuario_asignado = u.id_usuario), 0) AS ultimo
                    FROM usuarios u LEFT JOIN departamentos d ON d.id_departamento = u.id_departamento
                    WHERE u.id_rol = " . ROL_TECNICO . " ORDER BY u.nombres, u.apellidos");
    $out = [];
    foreach ($r->fetch_all(MYSQLI_ASSOC) as $t) {
        $t['abiertos'] = (int)$t['abiertos']; $t['ultimo'] = (int)$t['ultimo'];
        $t['disponible'] = ($t['estado'] === 'activo');
        $out[] = $t;
    }
    return $out;
}

/** Elige el técnico para un ticket del departamento $id_dep según la configuración. null si no hay candidato. */
function asig_elegir(mysqli $c, int $id_dep, ?array $cfg = null, ?array $tecnicos = null): ?array {
    $cfg = $cfg ?? asig_config($c);
    $lista = array_values(array_filter($tecnicos ?? asig_tecnicos($c), fn($t) => $t['disponible'] && ($cfg['max'] === 0 || $t['abiertos'] < $cfg['max'])));
    if (!$lista) return null;
    usort($lista, function ($a, $b) use ($cfg, $id_dep) {
        if ($cfg['modo'] === 'departamento') {
            $da = (int)$a['id_departamento'] === $id_dep ? 0 : 1; $db = (int)$b['id_departamento'] === $id_dep ? 0 : 1;
            if ($da !== $db) return $da <=> $db;
        }
        return [$a['abiertos'], $a['ultimo'], $a['id_usuario']] <=> [$b['abiertos'], $b['ultimo'], $b['id_usuario']];
    });
    return $lista[0];
}

/** Asigna $id_tecnico al ticket (solo si sigue sin técnico y sin cerrar). Deja historial y avisa. Devuelve el nombre o null. */
function asig_aplicar(mysqli $c, int $id_ticket, array $tec, int $id_actor, string $etiqueta = ' (asignación automática)'): ?string {
    $nombre = trim($tec['nombres'] . ' ' . $tec['apellidos']); $id_tec = (int)$tec['id_usuario'];
    try {
        $c->begin_transaction();
        $q = $c->prepare("SELECT id_estado FROM tickets WHERE id_tickets = ? AND id_usuario_asignado IS NULL AND id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ") FOR UPDATE");
        $q->bind_param("i", $id_ticket); $q->execute(); $f = $q->get_result()->fetch_assoc(); $q->close();
        if (!$f) { $c->rollback(); return null; }                      // alguien lo asignó antes (el admin, otro proceso…)
        $era_abierto = ((int)$f['id_estado'] === TICKET_ABIERTO);
        $nuevo = $era_abierto ? TICKET_PROCESO : (int)$f['id_estado'];
        $u = $c->prepare("UPDATE tickets SET id_usuario_asignado = ?, id_estado = ? WHERE id_tickets = ?");
        $u->bind_param("iii", $id_tec, $nuevo, $id_ticket); $u->execute(); $u->close();
        $h = $c->prepare("INSERT INTO historial_ticket (id_ticket, id_usuario, campo_modificado, valor_anterior, valor_nuevo) VALUES (?, ?, ?, ?, ?)");
        $campo = 'Técnico asignado'; $ant = 'Sin asignar'; $nue = $nombre . $etiqueta;
        $h->bind_param("iisss", $id_ticket, $id_actor, $campo, $ant, $nue); $h->execute();
        if ($era_abierto) {
            $campo = 'Estado'; $ant = 'Abierto'; $nue = 'En Proceso';
            $h->bind_param("iisss", $id_ticket, $id_actor, $campo, $ant, $nue); $h->execute();
        }
        $h->close();
        $c->commit();
    } catch (Throwable $e) { try { $c->rollback(); } catch (Throwable $e2) {} error_log('[asignacion] ' . $e->getMessage()); return null; }
    try {
        if (!function_exists('noti_evento_asignado') && is_file(__DIR__ . '/notificaciones.php')) require_once __DIR__ . '/notificaciones.php';
        if (function_exists('noti_evento_asignado')) noti_evento_asignado($c, $id_ticket, $id_actor, $id_tec, $nombre);
    } catch (Throwable $e) { error_log('[asignacion] aviso: ' . $e->getMessage()); }
    return $nombre;
}

/** Intenta asignar automáticamente un ticket. $id_actor = quien queda en el historial (por defecto, el solicitante). Devuelve el nombre o null. */
function asig_auto(mysqli $c, int $id_ticket, ?int $id_actor = null, bool $ignorar_interruptor = false, ?array $tecnicos = null): ?string {
    try {
        $cfg = asig_config($c);
        if (!$cfg['activa'] && !$ignorar_interruptor) return null;
        $q = $c->prepare("SELECT id_departamento, id_usuario_solicitante FROM tickets WHERE id_tickets = ? AND id_usuario_asignado IS NULL AND id_estado = " . TICKET_ABIERTO);
        $q->bind_param("i", $id_ticket); $q->execute(); $t = $q->get_result()->fetch_assoc(); $q->close();
        if (!$t) return null;
        $tec = asig_elegir($c, (int)$t['id_departamento'], $cfg, $tecnicos);
        if (!$tec) return null;
        return asig_aplicar($c, $id_ticket, $tec, $id_actor ?? (int)$t['id_usuario_solicitante']);
    } catch (Throwable $e) { error_log('[asignacion] ' . $e->getMessage()); return null; }
}

/** Asigna todos los tickets «Abiertos» sin técnico (más urgentes primero). Devuelve cuántos asignó. */
function asig_pendientes(mysqli $c, ?int $id_actor = null, bool $ignorar_interruptor = false, int $limite = 100): int {
    try {
        if (!$ignorar_interruptor && !asig_config($c)['activa']) return 0;
        $ids = $c->query("SELECT t.id_tickets FROM tickets t JOIN prioridades p ON p.id_prioridad = t.id_prioridad
                          WHERE t.id_usuario_asignado IS NULL AND t.id_estado = " . TICKET_ABIERTO . "
                          ORDER BY p.nivel DESC, t.fecha_creacion ASC LIMIT " . (int)$limite)->fetch_all(MYSQLI_NUM);
        $n = 0;
        foreach ($ids as $r) {
            // se recalcula la carga en cada vuelta para repartir de forma pareja
            if (asig_auto($c, (int)$r[0], $id_actor, $ignorar_interruptor) !== null) $n++;
        }
        return $n;
    } catch (Throwable $e) { error_log('[asignacion] ' . $e->getMessage()); return 0; }
}

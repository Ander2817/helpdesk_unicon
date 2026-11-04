<?php
// includes/tareas_programadas.php — tareas automáticas SIN cron y SIN tablas nuevas.
// Las dispara el refresco de la campana de notificaciones (como máximo una vez cada 15 minutos) y se anotan en `sistema_estado`.
//  1) Autocierre: tickets «Resuelto» que llevan AUTOCIERRE_DIAS días sin que el usuario los reabra pasan a «Cerrado».
//  2) Aviso de mantenimiento: avisa cuando el `proximo` mantenimiento de un equipo está a MANT_AVISO_DIAS días o ya venció.
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/flujo_tickets.php';
require_once __DIR__ . '/notificaciones.php';
require_once __DIR__ . '/permisos.php';

const MANT_AVISO_DIAS = 7;                 // <-- cuántos días antes se avisa del mantenimiento
const AUTOCIERRE_DIAS = 7;                 // <-- días en «Resuelto» antes de cerrarse solo (mantenlo igual al plazo para reabrir: REABRIR_DIAS = 7)
function autocierre_dias(): int { return AUTOCIERRE_DIAS; }

/** Candado: devuelve true solo para UNA petición cada $minutos (lo guarda sistema_estado). */
function tareas_candado(mysqli $c, string $clave, int $minutos): bool {
    $i = $c->prepare("INSERT IGNORE INTO sistema_estado (clave, valor, actualizado) VALUES (?, '', '2000-01-01 00:00:00')");
    $i->bind_param("s", $clave); $i->execute(); $i->close();
    $u = $c->prepare("UPDATE sistema_estado SET actualizado = NOW() WHERE clave = ? AND actualizado < (NOW() - INTERVAL ? MINUTE)");
    $u->bind_param("si", $clave, $minutos); $u->execute();
    $ok = $u->affected_rows === 1; $u->close();
    return $ok;
}
function tareas_guardar(mysqli $c, string $clave, string $valor): void {
    $u = $c->prepare("INSERT INTO sistema_estado (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
    $u->bind_param("ss", $clave, $valor); $u->execute(); $u->close();
}

/** Cierra los tickets resueltos que cumplieron el plazo. Devuelve cuántos cerró. */
function tareas_autocerrar(mysqli $c): int {
    $dias = autocierre_dias(); $n = 0;
    $q = $c->prepare("SELECT id_tickets, codigo_ticket, id_usuario_solicitante, COALESCE(id_usuario_asignado, id_usuario_solicitante) AS actor
                      FROM tickets WHERE id_estado = ? AND fecha_cierre IS NOT NULL AND fecha_cierre < (NOW() - INTERVAL ? DAY) LIMIT 100");
    $res = TICKET_RESUELTO; $q->bind_param("ii", $res, $dias); $q->execute();
    $lista = $q->get_result()->fetch_all(MYSQLI_ASSOC); $q->close();
    foreach ($lista as $t) {
        try {
            $c->begin_transaction();
            $u = $c->prepare("UPDATE tickets SET id_estado = ? WHERE id_tickets = ? AND id_estado = ?");
            $cer = TICKET_CERRADO; $id = (int)$t['id_tickets']; $u->bind_param("iii", $cer, $id, $res); $u->execute();
            if ($u->affected_rows !== 1) { $c->rollback(); continue; }       // otro proceso ya lo cambió
            $u->close();
            $campo = 'Estado'; $ant = nombre_estado(TICKET_RESUELTO); $nue = nombre_estado(TICKET_CERRADO) . ' (automático)'; $actor = (int)$t['actor'];
            $h = $c->prepare("INSERT INTO historial_ticket (id_ticket, id_usuario, campo_modificado, valor_anterior, valor_nuevo) VALUES (?, ?, ?, ?, ?)");
            $h->bind_param("iisss", $id, $actor, $campo, $ant, $nue); $h->execute(); $h->close();
            $c->commit(); $n++;
            notificar($c, (int)$t['id_usuario_solicitante'], $id, "Tu ticket {$t['codigo_ticket']} se cerró automáticamente tras $dias días sin objeciones. Aún puedes calificar la atención recibida.");
        } catch (Throwable $e) { try { $c->rollback(); } catch (Throwable $e2) {} error_log('[autocierre] ' . $e->getMessage()); }
    }
    return $n;
}

/** Avisa de mantenimientos próximos o vencidos (una vez por equipo y por situación). Devuelve cuántos avisos nuevos creó. */
function tareas_mantenimientos(mysqli $c): int {
    $dias = MANT_AVISO_DIAS; $n = 0;
    $r = $c->query("SELECT m.id_mantenimiento, m.id_inventario, m.proximo, i.codigo_equipo, i.tipo_equipo
                    FROM mantenimientos_equipo m JOIN inventario_tecnologico i ON i.id_inventario = m.id_inventario
                    WHERE m.proximo IS NOT NULL AND i.estado = 'activo' AND m.proximo <= (CURDATE() + INTERVAL $dias DAY)
                      AND m.id_mantenimiento = (SELECT m2.id_mantenimiento FROM mantenimientos_equipo m2 WHERE m2.id_inventario = m.id_inventario
                                                ORDER BY m2.fecha DESC, m2.id_mantenimiento DESC LIMIT 1)
                    LIMIT 200");
    $filas = $r->fetch_all(MYSQLI_ASSOC);
    if (!$filas) return 0;
    $tecnicos = [];
    if (rol_tiene_permiso($c, ROL_TECNICO, 'inventario.mantenimiento')) {
        $tecnicos = array_column($c->query("SELECT id_usuario FROM usuarios WHERE id_rol = " . ROL_TECNICO . " AND estado = 'activo'")->fetch_all(), 0);
    }
    $admins = noti_ids_admins($c);
    $get = $c->prepare("SELECT valor FROM sistema_estado WHERE clave = ?");
    foreach ($filas as $m) {
        $clave = 'mant_aviso_' . (int)$m['id_mantenimiento'];
        $vencido = $m['proximo'] < date('Y-m-d');
        $marca = $m['proximo'] . '|' . ($vencido ? 'venc' : 'prox');
        $get->bind_param("s", $clave); $get->execute();
        $ya = $get->get_result()->fetch_row()[0] ?? null;
        if ($ya === $marca) continue;
        $fecha = date('d/m/Y', strtotime($m['proximo'])); $eq = "{$m['codigo_equipo']} ({$m['tipo_equipo']})";
        $msg = $vencido ? "Mantenimiento VENCIDO desde el $fecha: equipo $eq." : "Mantenimiento programado para el $fecha: equipo $eq.";
        $id = (int)$m['id_inventario'];
        foreach ($admins as $u)   notificar($c, (int)$u, null, $msg, 'mantenimiento', 'admin/inventario_historial.php?id=' . $id);
        foreach ($tecnicos as $u) notificar($c, (int)$u, null, $msg, 'mantenimiento', 'tecnico/inventario_historial.php?id=' . $id);
        tareas_guardar($c, $clave, $marca); $n++;
    }
    $get->close();
    return $n;
}

/** Punto de entrada: se llama desde procesos/notificaciones.php. Nunca rompe la página. */
function tareas_ejecutar(mysqli $c, bool $forzar = false): void {
    try {
        if ($forzar) { $c->query("UPDATE sistema_estado SET actualizado = '2000-01-01 00:00:00' WHERE clave = 'tareas_revision'"); }
        if (!tareas_candado($c, 'tareas_revision', 15)) return;
        $a = 0; $m = 0; $as = 0;
        try { $a = tareas_autocerrar($c); } catch (Throwable $e) { error_log('[tareas] autocierre: ' . $e->getMessage()); }
        try { $m = tareas_mantenimientos($c); } catch (Throwable $e) { error_log('[tareas] mantenimientos: ' . $e->getMessage()); }
        // Asignación automática: reparte los tickets que quedaron sin técnico (p. ej. cuando no había nadie disponible al crearlos)
        try { if (is_file(__DIR__ . '/asignacion.php')) { require_once __DIR__ . '/asignacion.php'; $as = asig_pendientes($c); } } catch (Throwable $e) { error_log('[tareas] asignacion: ' . $e->getMessage()); }
        tareas_guardar($c, 'tareas_revision', "autocerrados=$a;avisos_mantenimiento=$m;autoasignados=$as");
    } catch (Throwable $e) { error_log('[tareas] ' . $e->getMessage()); }
}

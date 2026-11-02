<?php
// includes/notificaciones_extra.php — avisos por correo + eventos de reapertura y calificación.
// Lo carga includes/notificaciones.php (no se usa directamente).

/** Copia de una notificación por correo (solo si config/correo.php tiene 'avisos_por_correo' => true). */
function noti_correo(mysqli $c, int $id_usuario, ?int $id_ticket, string $mensaje, string $tipo): void {
    try {
        if (!in_array($tipo, ['ticket', 'sla'], true)) return;
        require_once __DIR__ . '/correo.php';
        if (empty(correo_config()['avisos_por_correo'])) return;
        $q = $c->prepare("SELECT correo, nombres, id_rol FROM usuarios WHERE id_usuario = ? AND estado = 'activo'");
        $q->bind_param("i", $id_usuario); $q->execute();
        $u = $q->get_result()->fetch_assoc(); $q->close();
        if (!$u || !$u['correo']) return;
        $carpeta = [ROL_ADMIN => 'admin', ROL_TECNICO => 'tecnico', ROL_USUARIO => 'usuario'][(int)$u['id_rol']] ?? 'usuario';
        $url = $id_ticket ? URL_BASE . $carpeta . '/ver_ticket.php?id=' . $id_ticket : null;
        $asunto = '[' . NOMBRE_SISTEMA . '] ' . (mb_strlen($mensaje) > 80 ? mb_substr($mensaje, 0, 79) . '…' : $mensaje);
        correo_enviar($u['correo'], $asunto,
            correo_plantilla('Tienes una notificación', '<p>' . htmlspecialchars($mensaje) . '</p>', $url ? 'Ver ticket' : null, $url), $u['nombres']);
    } catch (Throwable $e) { error_log('[noti_correo] ' . $e->getMessage()); }
}

/** El solicitante reabrió un ticket resuelto: avisa al técnico y a los administradores. */
function noti_evento_reabierto(mysqli $c, int $id_ticket, int $id_solicitante, string $motivo): void {
    try {
        $t = noti_ticket($c, $id_ticket); if (!$t) return;
        $dest = noti_ids_admins($c);
        if (!empty($t['id_usuario_asignado'])) $dest[] = (int)$t['id_usuario_asignado'];
        notificar_varios($c, $dest, $id_ticket,
            noti_nombre($c, $id_solicitante) . " reabrió el ticket {$t['codigo_ticket']}: «" . noti_cortar($motivo, 120) . "»", 'ticket', null, $id_solicitante);
    } catch (Throwable $e) { error_log('[notificaciones] ' . $e->getMessage()); }
}

/** El solicitante calificó la atención: avisa al técnico asignado. */
function noti_evento_calificado(mysqli $c, int $id_ticket, int $id_solicitante, int $puntos): void {
    try {
        $t = noti_ticket($c, $id_ticket); if (!$t || empty($t['id_usuario_asignado'])) return;
        notificar($c, (int)$t['id_usuario_asignado'], $id_ticket,
            noti_nombre($c, $id_solicitante) . " calificó la atención del ticket {$t['codigo_ticket']}: " . str_repeat('★', $puntos) . str_repeat('☆', 5 - $puntos));
    } catch (Throwable $e) { error_log('[notificaciones] ' . $e->getMessage()); }
}

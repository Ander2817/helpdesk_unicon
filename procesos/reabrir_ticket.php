<?php
// procesos/reabrir_ticket.php — el solicitante reabre un ticket RESUELTO (hasta 7 días después de la resolución).
// Vuelve a "En Proceso" con el mismo técnico (o a "Abierto" si no tenía), queda el motivo como comentario y se avisa.
session_start();
require_once __DIR__ . '/../includes/csrf.php'; csrf_verificar(); /* csrf */
require_once '../includes/constants.php';
require_once '../includes/functions.php';
require_once '../includes/flujo_tickets.php';
include '../config/conexion.php';
require_once '../includes/bitacora.php';
require_once '../includes/notificaciones.php';
header('Content-Type: text/html; charset=utf-8');

const REABRIR_DIAS = 7;
function res_reab(string $tipo, string $m): never { echo "<div class='alert alert-$tipo'>$m</div>"; exit(); }

if (!isset($_SESSION['usser'])) { http_response_code(403); res_reab('danger', 'No autorizado.'); }
$yo = noti_id_actual($conexion);
$id_ticket = (int)($_POST['id_ticket'] ?? 0);
$motivo = trim((string)($_POST['motivo'] ?? ''));
if ($id_ticket <= 0) res_reab('warning', 'Ticket no válido.');
if (mb_strlen($motivo) < 10 || mb_strlen($motivo) > 500) res_reab('warning', 'Explica por qué lo reabres (entre 10 y 500 caracteres).');

$conexion->begin_transaction();
try {
    $q = $conexion->prepare("SELECT id_usuario_solicitante, id_usuario_asignado, id_estado, fecha_cierre FROM tickets WHERE id_tickets = ? FOR UPDATE");
    $q->bind_param("i", $id_ticket); $q->execute();
    $t = $q->get_result()->fetch_assoc(); $q->close();

    if (!$t) { $conexion->rollback(); res_reab('danger', 'El ticket no existe.'); }
    if ((int)$t['id_usuario_solicitante'] !== $yo) { $conexion->rollback(); http_response_code(403); res_reab('danger', 'Solo quien creó el ticket puede reabrirlo.'); }
    if ((int)$t['id_estado'] !== TICKET_RESUELTO) { $conexion->rollback(); res_reab('warning', 'Solo se puede reabrir un ticket que está en estado «Resuelto».'); }
    if (!empty($t['fecha_cierre']) && strtotime($t['fecha_cierre']) < time() - REABRIR_DIAS * 86400) {
        $conexion->rollback(); res_reab('warning', 'Pasaron más de ' . REABRIR_DIAS . ' días desde la resolución. Crea un ticket nuevo.');
    }

    $nuevo = !empty($t['id_usuario_asignado']) ? TICKET_PROCESO : TICKET_ABIERTO;
    $u = $conexion->prepare("UPDATE tickets SET id_estado = ?, fecha_cierre = NULL WHERE id_tickets = ?");
    $u->bind_param("ii", $nuevo, $id_ticket); $u->execute(); $u->close();

    $h = $conexion->prepare("INSERT INTO historial_ticket (id_ticket, id_usuario, campo_modificado, valor_anterior, valor_nuevo) VALUES (?, ?, ?, ?, ?)");
    $campo = 'Estado'; $de = nombre_estado(TICKET_RESUELTO); $a = nombre_estado($nuevo) . ' (reabierto por el solicitante)';
    $h->bind_param("iisss", $id_ticket, $yo, $campo, $de, $a); $h->execute(); $h->close();

    $c = $conexion->prepare("INSERT INTO comentarios (id_ticket, id_usuario, comentario) VALUES (?, ?, ?)");
    $texto = 'Ticket reabierto. Motivo: ' . $motivo;
    $c->bind_param("iis", $id_ticket, $yo, $texto); $c->execute(); $c->close();
    $conexion->commit();
} catch (mysqli_sql_exception $e) {
    $conexion->rollback();
    error_log('[reabrir_ticket] ' . $e->getMessage());
    res_reab('danger', 'No se pudo reabrir el ticket.');
}
registrar_bitacora($conexion, 'UPDATE', 'tickets', $id_ticket, "Reabrió el ticket #$id_ticket", ['id_estado' => TICKET_RESUELTO], ['id_estado' => $nuevo, 'motivo' => $motivo]);
noti_evento_reabierto($conexion, $id_ticket, $yo, $motivo);
res_reab('success', "<i class='fas fa-check-circle me-2'></i>Ticket reabierto. El equipo de soporte fue notificado.");

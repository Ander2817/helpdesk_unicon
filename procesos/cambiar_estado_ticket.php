<?php
session_start();
require_once '../includes/functions.php';
require_once '../config/constants.php';
include '../config/conexion.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    http_response_code(403);
    echo "<div class='alert alert-danger'>No autorizado.</div>";
    exit();
}

$rol_id            = (int)$_SESSION['id_rol'];
$id_ticket         = (int)($_POST['id_ticket'] ?? 0);
$nuevo_estado      = (int)($_POST['nuevo_estado'] ?? 0);
$solucion          = procesar_opcional($_POST['solucion'] ?? null);
$id_usuario_asig_nuevo = isset($_POST['id_usuario_asignado']) && $_POST['id_usuario_asignado'] !== ''
    ? (int)$_POST['id_usuario_asignado'] : null;

$nombres_estado = [
    TICKET_ABIERTO   => 'Abierto',
    TICKET_PROCESO   => 'En Proceso',
    TICKET_PENDIENTE => 'Pendiente',
    TICKET_RESUELTO  => 'Resuelto',
    TICKET_CERRADO   => 'Cerrado',
];

if ($id_ticket <= 0 || !isset($nombres_estado[$nuevo_estado])) {
    echo "<div class='alert alert-danger'>Datos inválidos.</div>";
    exit();
}

// Usuario en sesión (para el historial)
$stmt_yo = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
$stmt_yo->bind_param("s", $_SESSION['usser']);
$stmt_yo->execute();
$yo = $stmt_yo->get_result()->fetch_assoc();
$stmt_yo->close();
$id_usuario_actual = $yo['id_usuario'] ?? 0;

// Ticket actual
$stmt_t = $conexion->prepare("SELECT id_estado, id_usuario_asignado FROM tickets WHERE id_tickets = ?");
$stmt_t->bind_param("i", $id_ticket);
$stmt_t->execute();
$ticket = $stmt_t->get_result()->fetch_assoc();
$stmt_t->close();

if (!$ticket) {
    echo "<div class='alert alert-danger'>El ticket no existe.</div>";
    exit();
}

// Permisos: admin siempre puede; técnico solo si el ticket es suyo
if ($rol_id === ROL_TECNICO && (int)$ticket['id_usuario_asignado'] !== (int)$id_usuario_actual) {
    http_response_code(403);
    echo "<div class='alert alert-danger'>No tienes permiso para gestionar este ticket.</div>";
    exit();
}
if ($rol_id === ROL_USUARIO) {
    http_response_code(403);
    echo "<div class='alert alert-danger'>No tienes permiso para cambiar el estado de un ticket.</div>";
    exit();
}

// Si se marca como Resuelto, la solución es obligatoria
if ($nuevo_estado === TICKET_RESUELTO && empty($solucion)) {
    echo "<div class='alert alert-warning'>Describe la solución antes de marcar el ticket como resuelto.</div>";
    exit();
}

$conexion->begin_transaction();
try {
    if ($nuevo_estado === TICKET_RESUELTO) {
        $stmt = $conexion->prepare("UPDATE tickets SET id_estado = ?, solucion = ?, fecha_cierre = NOW() WHERE id_tickets = ?");
        $stmt->bind_param("isi", $nuevo_estado, $solucion, $id_ticket);
    } else {
        $stmt = $conexion->prepare("UPDATE tickets SET id_estado = ? WHERE id_tickets = ?");
        $stmt->bind_param("ii", $nuevo_estado, $id_ticket);
    }
    $stmt->execute();

    // Log del cambio de estado
    $estado_anterior_nombre = $nombres_estado[$ticket['id_estado']] ?? (string)$ticket['id_estado'];
    $estado_nuevo_nombre    = $nombres_estado[$nuevo_estado];
    $campo = 'Estado';
    $stmt_h = $conexion->prepare("INSERT INTO historial_ticket (id_ticket, id_usuario, campo_modificado, valor_anterior, valor_nuevo) VALUES (?, ?, ?, ?, ?)");
    $stmt_h->bind_param("iisss", $id_ticket, $id_usuario_actual, $campo, $estado_anterior_nombre, $estado_nuevo_nombre);
    $stmt_h->execute();

    // Reasignación de técnico (solo admin la puede enviar; el form de técnico no manda este campo)
    if ($rol_id === ROL_ADMIN && $id_usuario_asig_nuevo !== null && $id_usuario_asig_nuevo !== (int)$ticket['id_usuario_asignado']) {
        $stmt_r = $conexion->prepare("UPDATE tickets SET id_usuario_asignado = ? WHERE id_tickets = ?");
        $stmt_r->bind_param("ii", $id_usuario_asig_nuevo, $id_ticket);
        $stmt_r->execute();

        $campo_asig = 'Técnico asignado';
        $stmt_hr = $conexion->prepare("INSERT INTO historial_ticket (id_ticket, id_usuario, campo_modificado, valor_anterior, valor_nuevo) VALUES (?, ?, ?, ?, ?)");
        $valor_ant = (string)($ticket['id_usuario_asignado'] ?? 'Sin asignar');
        $valor_nue = (string)$id_usuario_asig_nuevo;
        $stmt_hr->bind_param("iisss", $id_ticket, $id_usuario_actual, $campo_asig, $valor_ant, $valor_nue);
        $stmt_hr->execute();
    }

    $conexion->commit();
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Ticket actualizado correctamente.</div>";
} catch (mysqli_sql_exception $e) {
    $conexion->rollback();
    echo "<div class='alert alert-danger'>Error al actualizar el ticket.</div>";
}

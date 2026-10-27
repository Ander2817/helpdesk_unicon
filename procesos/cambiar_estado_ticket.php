<?php
session_start();
require_once '../includes/functions.php';
require_once '../includes/constants.php';
require_once '../includes/flujo_tickets.php';
include '../config/conexion.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    http_response_code(403);
    echo "<div class='alert alert-danger'>No autorizado.</div>";
    exit();
}

$rol_id       = (int)$_SESSION['id_rol'];
$id_ticket    = (int)($_POST['id_ticket'] ?? 0);
$nuevo_estado = (int)($_POST['nuevo_estado'] ?? 0);
$solucion     = procesar_opcional($_POST['solucion'] ?? null);

if ($id_ticket <= 0 || nombre_estado($nuevo_estado) === (string)$nuevo_estado) {
    echo "<div class='alert alert-danger'>Datos inválidos.</div>";
    exit();
}

if ($rol_id === ROL_USUARIO) {
    http_response_code(403);
    echo "<div class='alert alert-danger'>No tienes permiso para cambiar el estado de un ticket.</div>";
    exit();
}

// Usuario en sesión (para el historial y para validar al técnico)
$stmt_yo = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
$stmt_yo->bind_param("s", $_SESSION['usser']);
$stmt_yo->execute();
$id_usuario_actual = (int)($stmt_yo->get_result()->fetch_assoc()['id_usuario'] ?? 0);
$stmt_yo->close();

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

// Un técnico solo gestiona los tickets que el administrador le asignó
if ($rol_id === ROL_TECNICO && ((int)$ticket['id_usuario_asignado'] !== $id_usuario_actual || $id_usuario_actual === 0)) {
    http_response_code(403);
    echo "<div class='alert alert-danger'>No tienes permiso para gestionar este ticket.</div>";
    exit();
}

// Sin técnico no hay atención: primero el administrador debe asignarlo
if (empty($ticket['id_usuario_asignado'])) {
    echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle me-2'></i>Este ticket aún no tiene técnico. Asígnalo primero.</div>";
    exit();
}

// La transición debe estar permitida desde el estado actual (misma regla que ve la pantalla)
if (!in_array($nuevo_estado, estados_siguientes((int)$ticket['id_estado'], $rol_id), true)) {
    echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle me-2'></i>No se puede pasar de «" . htmlspecialchars(nombre_estado((int)$ticket['id_estado'])) . "» a «" . htmlspecialchars(nombre_estado($nuevo_estado)) . "».</div>";
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

    $campo = 'Estado';
    $anterior = nombre_estado((int)$ticket['id_estado']);
    $nuevo    = nombre_estado($nuevo_estado);
    $stmt_h = $conexion->prepare("INSERT INTO historial_ticket (id_ticket, id_usuario, campo_modificado, valor_anterior, valor_nuevo) VALUES (?, ?, ?, ?, ?)");
    $stmt_h->bind_param("iisss", $id_ticket, $id_usuario_actual, $campo, $anterior, $nuevo);
    $stmt_h->execute();

    $conexion->commit();
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Ticket actualizado correctamente.</div>";
} catch (mysqli_sql_exception $e) {
    $conexion->rollback();
    echo "<div class='alert alert-danger'>Error al actualizar el ticket.</div>";
}

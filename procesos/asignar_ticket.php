<?php
session_start();
require_once '../includes/constants.php';
require_once '../includes/flujo_tickets.php';
include '../config/conexion.php';

header('Content-Type: text/html; charset=utf-8');

// SOLO el administrador asigna tickets
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== ROL_ADMIN) {
    http_response_code(403);
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Solo el administrador puede asignar tickets.</div>";
    exit();
}

$id_ticket  = (int)($_POST['id_ticket'] ?? 0);
$id_tecnico = (int)($_POST['id_usuario_asignado'] ?? 0);

if ($id_ticket <= 0 || $id_tecnico <= 0) {
    echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle me-2'></i>Selecciona un técnico para asignar el ticket.</div>";
    exit();
}

// Administrador que realiza la acción (para el historial)
$stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
$stmt->bind_param("s", $_SESSION['usser']);
$stmt->execute();
$id_admin = (int)($stmt->get_result()->fetch_assoc()['id_usuario'] ?? 0);
$stmt->close();

// Ticket actual (con el nombre del técnico anterior, si lo hay)
$stmt = $conexion->prepare("
    SELECT t.id_estado, t.id_usuario_asignado, CONCAT(a.nombres, ' ', a.apellidos) AS tecnico_anterior
    FROM tickets t
    LEFT JOIN usuarios a ON t.id_usuario_asignado = a.id_usuario
    WHERE t.id_tickets = ?
");
$stmt->bind_param("i", $id_ticket);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ticket) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>El ticket no existe.</div>";
    exit();
}

if (in_array((int)$ticket['id_estado'], [TICKET_RESUELTO, TICKET_CERRADO], true)) {
    echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle me-2'></i>No se puede asignar un ticket que ya está resuelto o cerrado.</div>";
    exit();
}

// El destino debe ser un técnico ACTIVO (no cualquier usuario)
$stmt = $conexion->prepare("SELECT CONCAT(nombres, ' ', apellidos) AS nombre FROM usuarios WHERE id_usuario = ? AND id_rol = ? AND estado = 'activo'");
$rol_tecnico = ROL_TECNICO;
$stmt->bind_param("ii", $id_tecnico, $rol_tecnico);
$stmt->execute();
$tecnico = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tecnico) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>El usuario seleccionado no es un técnico activo.</div>";
    exit();
}

if ((int)$ticket['id_usuario_asignado'] === $id_tecnico) {
    echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle me-2'></i>Ese técnico ya está asignado a este ticket.</div>";
    exit();
}

$era_abierto = ((int)$ticket['id_estado'] === TICKET_ABIERTO);
$anterior    = $ticket['tecnico_anterior'] ?: 'Sin asignar';

$conexion->begin_transaction();
try {
    // Si el ticket estaba "Abierto" (sin técnico), al asignarlo pasa solo a "En Proceso"
    $nuevo_estado = $era_abierto ? TICKET_PROCESO : (int)$ticket['id_estado'];
    $stmt = $conexion->prepare("UPDATE tickets SET id_usuario_asignado = ?, id_estado = ? WHERE id_tickets = ?");
    $stmt->bind_param("iii", $id_tecnico, $nuevo_estado, $id_ticket);
    $stmt->execute();
    $stmt->close();

    $campo = 'Técnico asignado';
    $stmt = $conexion->prepare("INSERT INTO historial_ticket (id_ticket, id_usuario, campo_modificado, valor_anterior, valor_nuevo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $id_ticket, $id_admin, $campo, $anterior, $tecnico['nombre']);
    $stmt->execute();

    if ($era_abierto) {
        $campo_estado = 'Estado';
        $de = nombre_estado(TICKET_ABIERTO);
        $a  = nombre_estado(TICKET_PROCESO);
        $stmt->bind_param("iisss", $id_ticket, $id_admin, $campo_estado, $de, $a);
        $stmt->execute();
    }
    $stmt->close();

    $conexion->commit();
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Ticket asignado a <strong>" . htmlspecialchars($tecnico['nombre']) . "</strong>.</div>";
} catch (mysqli_sql_exception $e) {
    $conexion->rollback();
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>No se pudo asignar el ticket.</div>";
}

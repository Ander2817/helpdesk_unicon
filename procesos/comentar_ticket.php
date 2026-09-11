<?php
session_start();
require_once '../includes/functions.php';
include '../config/conexion.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['usser'])) {
    http_response_code(403);
    echo "<div class='alert alert-danger'>No autorizado.</div>";
    exit();
}

$id_ticket  = (int)($_POST['id_ticket'] ?? 0);
$comentario = limpiar_inputs($_POST['comentario'] ?? '');

if ($id_ticket <= 0 || $comentario === '') {
    echo "<div class='alert alert-danger'>Escribe un comentario válido.</div>";
    exit();
}

// Obtener id_usuario del usuario en sesión
$stmt_yo = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
$stmt_yo->bind_param("s", $_SESSION['usser']);
$stmt_yo->execute();
$yo = $stmt_yo->get_result()->fetch_assoc();
$stmt_yo->close();

if (!$yo) {
    echo "<div class='alert alert-danger'>No se pudo identificar al usuario.</div>";
    exit();
}
$id_usuario = $yo['id_usuario'];

// Verificar que el ticket exista y que el usuario tenga relación con él
$stmt_t = $conexion->prepare("SELECT id_usuario_solicitante, id_usuario_asignado FROM tickets WHERE id_tickets = ?");
$stmt_t->bind_param("i", $id_ticket);
$stmt_t->execute();
$t = $stmt_t->get_result()->fetch_assoc();
$stmt_t->close();

if (!$t) {
    echo "<div class='alert alert-danger'>El ticket no existe.</div>";
    exit();
}

$rol_id = (int)($_SESSION['id_rol'] ?? 0);
$autorizado = ($rol_id === 3) // admin
    || ((int)$t['id_usuario_solicitante'] === (int)$id_usuario)
    || ((int)$t['id_usuario_asignado'] === (int)$id_usuario);

if (!$autorizado) {
    http_response_code(403);
    echo "<div class='alert alert-danger'>No tienes permiso para comentar en este ticket.</div>";
    exit();
}

$stmt = $conexion->prepare("INSERT INTO comentarios (id_ticket, id_usuario, comentario) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $id_ticket, $id_usuario, $comentario);
$stmt->execute();

echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Comentario agregado.</div>";

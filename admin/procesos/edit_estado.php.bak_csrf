<?php
session_start();
require_once '../includes/functions.php';
include '../config/conexion.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) {
    http_response_code(403);
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>No autorizado.</div>";
    exit();
}

$id_estado_ticket = (int)($_POST['id_estado_ticket'] ?? 0);
$nombre           = limpiar_inputs($_POST['nombre'] ?? '');
$descripcion      = procesar_opcional($_POST['descripcion'] ?? null);

if ($id_estado_ticket <= 0 || $nombre === '') {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Datos inválidos.</div>";
    exit();
}

try {
    $stmt = $conexion->prepare("UPDATE estados_ticket SET nombre = ?, descripcion = ? WHERE id_estado_ticket = ?");
    $stmt->bind_param("ssi", $nombre, $descripcion, $id_estado_ticket);
    $stmt->execute();
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Estado actualizado correctamente.</div>";
} catch (mysqli_sql_exception $e) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Error al actualizar el estado.</div>";
}

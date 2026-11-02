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

$id_rol       = (int)($_POST['id_rol'] ?? 0);
$nombre       = limpiar_inputs($_POST['nombre'] ?? '');
$descripcion  = procesar_opcional($_POST['descripcion'] ?? null);
$nivel_acceso = (int)($_POST['nivel_acceso'] ?? 0);

if ($id_rol <= 0 || $nombre === '' || $nivel_acceso <= 0) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Datos inválidos.</div>";
    exit();
}

try {
    $stmt = $conexion->prepare("UPDATE roles SET nombre = ?, descripcion = ?, nivel_acceso = ? WHERE id_rol = ?");
    $stmt->bind_param("ssii", $nombre, $descripcion, $nivel_acceso, $id_rol);
    $stmt->execute();
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Rol actualizado correctamente.</div>";
} catch (mysqli_sql_exception $e) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Error al actualizar el rol.</div>";
}

<?php
session_start();
require_once '../includes/functions.php';
include '../config/conexion.php';

header('Content-Type: text/html; charset=utf-8');

// Solo administradores
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) {
    http_response_code(403);
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>No autorizado.</div>";
    exit();
}

$nombre      = limpiar_inputs($_POST['nombre'] ?? '');
$descripcion = procesar_opcional($_POST['descripcion'] ?? null);
$id_prioridad_default = (!empty($_POST['id_prioridad_default'])) ? (int)$_POST['id_prioridad_default'] : null;

if ($nombre === '') {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>El nombre de la categoría es obligatorio.</div>";
    exit();
}

try {
    $stmt = $conexion->prepare("INSERT INTO categoria_incidencias (nombre, descripcion, id_prioridad_default, estado) VALUES (?, ?, ?, 'activo')");
    $stmt->bind_param("ssi", $nombre, $descripcion, $id_prioridad_default);
    $stmt->execute();
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Categoría creada correctamente.</div>";
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Ya existe una categoría con ese nombre.</div>";
    } else {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Error al crear la categoría.</div>";
    }
}

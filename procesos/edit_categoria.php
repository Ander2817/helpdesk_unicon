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

$id_categoria = (int)($_POST['id_categoria'] ?? 0);
$nombre       = limpiar_inputs($_POST['nombre'] ?? '');
$descripcion  = procesar_opcional($_POST['descripcion'] ?? null);
$id_prioridad_default = (!empty($_POST['id_prioridad_default'])) ? (int)$_POST['id_prioridad_default'] : null;

if ($id_categoria <= 0 || $nombre === '') {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Datos inválidos.</div>";
    exit();
}

try {
    $stmt = $conexion->prepare("UPDATE categoria_incidencias SET nombre = ?, descripcion = ?, id_prioridad_default = ? WHERE id_categoria = ?");
    $stmt->bind_param("ssii", $nombre, $descripcion, $id_prioridad_default, $id_categoria);
    $stmt->execute();
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Categoría actualizada correctamente.</div>";
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Ya existe otra categoría con ese nombre.</div>";
    } else {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Error al actualizar la categoría.</div>";
    }
}

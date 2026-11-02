<?php
session_start();
require_once __DIR__ . '/../includes/csrf.php'; csrf_verificar(); /* csrf */
include '../config/conexion.php';
require_once __DIR__ . '/../includes/bitacora.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) {
    http_response_code(403);
    echo "<div class='alert alert-danger'>No autorizado.</div>";
    exit();
}

$id_categoria = (int)($_POST['id_categoria'] ?? 0);
if ($id_categoria <= 0) {
    http_response_code(400);
    echo "<div class='alert alert-danger'>ID inválido.</div>";
    exit();
}

$antes = bitacora_fila($conexion, 'categoria_incidencias', 'id_categoria', $id_categoria);
$stmt = $conexion->prepare("UPDATE categoria_incidencias SET estado = IF(estado='activo','inactivo','activo') WHERE id_categoria = ?");
$stmt->bind_param("i", $id_categoria);
$stmt->execute();

registrar_bitacora($conexion, 'UPDATE', 'categoria_incidencias', $id_categoria,
    'Cambió el estado de la categoría ' . ($antes['nombre'] ?? "#$id_categoria"), $antes,
    bitacora_fila($conexion, 'categoria_incidencias', 'id_categoria', $id_categoria));

echo "<div class='alert alert-success'>Estado actualizado.</div>";

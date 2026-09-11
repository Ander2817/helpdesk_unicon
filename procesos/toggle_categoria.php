<?php
session_start();
include '../config/conexion.php';

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

$stmt = $conexion->prepare("UPDATE categoria_incidencias SET estado = IF(estado='activo','inactivo','activo') WHERE id_categoria = ?");
$stmt->bind_param("i", $id_categoria);
$stmt->execute();

echo "<div class='alert alert-success'>Estado actualizado.</div>";

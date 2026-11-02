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

$id_inventario   = (int)($_POST['id_inventario'] ?? 0);
$codigo_equipo   = limpiar_inputs($_POST['codigo_equipo'] ?? '');
$tipo_equipo     = limpiar_inputs($_POST['tipo_equipo'] ?? '');
$marca           = limpiar_inputs($_POST['marca'] ?? '');
$modelo          = limpiar_inputs($_POST['modelo'] ?? '');
$serial          = limpiar_inputs($_POST['serial'] ?? '');
$ubicacion       = procesar_opcional($_POST['ubicacion'] ?? null);
$estado          = $_POST['estado'] ?? 'activo';
$id_departamento = (int)($_POST['id_departamento'] ?? 0);

$estados_validos = ['activo', 'inactivo', 'mantenimiento'];
if (!in_array($estado, $estados_validos, true)) {
    $estado = 'activo';
}

if ($id_inventario <= 0 || $codigo_equipo === '' || $tipo_equipo === '' || $marca === '' || $modelo === '' || $serial === '' || $id_departamento <= 0) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Datos inválidos.</div>";
    exit();
}

try {
    $stmt = $conexion->prepare("
        UPDATE inventario_tecnologico
        SET codigo_equipo = ?, tipo_equipo = ?, marca = ?, modelo = ?, serial = ?, estado = ?, ubicacion = ?, id_departamento = ?
        WHERE id_inventario = ?
    ");
    $stmt->bind_param("sssssssii", $codigo_equipo, $tipo_equipo, $marca, $modelo, $serial, $estado, $ubicacion, $id_departamento, $id_inventario);
    $stmt->execute();
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Equipo actualizado correctamente.</div>";
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Ya existe otro equipo con ese código o ese serial.</div>";
    } else {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Error al actualizar el equipo.</div>";
    }
}

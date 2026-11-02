<?php
session_start();
require_once __DIR__ . '/../includes/csrf.php'; csrf_verificar(); /* csrf */
require_once '../includes/functions.php';
include '../config/conexion.php';
require_once __DIR__ . '/../includes/bitacora.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) {
    http_response_code(403);
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>No autorizado.</div>";
    exit();
}

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

if ($codigo_equipo === '' || $tipo_equipo === '' || $marca === '' || $modelo === '' || $serial === '' || $id_departamento <= 0) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Completa todos los campos obligatorios.</div>";
    exit();
}

try {
    $stmt = $conexion->prepare("
        INSERT INTO inventario_tecnologico (codigo_equipo, tipo_equipo, marca, modelo, serial, estado, ubicacion, id_departamento)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssssi", $codigo_equipo, $tipo_equipo, $marca, $modelo, $serial, $estado, $ubicacion, $id_departamento);
    $stmt->execute();
    registrar_bitacora($conexion, 'INSERT', 'inventario_tecnologico', $conexion->insert_id, "Registró el equipo $codigo_equipo", null,
        ['codigo_equipo'=>$codigo_equipo, 'tipo_equipo'=>$tipo_equipo, 'marca'=>$marca, 'modelo'=>$modelo, 'serial'=>$serial,
         'estado'=>$estado, 'ubicacion'=>$ubicacion, 'id_departamento'=>$id_departamento]);
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Equipo registrado correctamente.</div>";
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Ya existe un equipo con ese código o ese serial.</div>";
    } else {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Error al registrar el equipo.</div>";
    }
}

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

$nombre  = limpiar_inputs($_POST['nombre'] ?? '');
$nivel   = (int)($_POST['nivel'] ?? 0);
$horas   = (int)($_POST['horas'] ?? 0);
$minutos = (int)($_POST['minutos'] ?? 0);
$tiempo_total = ($horas * 60) + $minutos;

if ($nombre === '' || $nivel < 1 || $tiempo_total <= 0) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Completa nombre, nivel y un tiempo de respuesta válido.</div>";
    exit();
}

try {
    $stmt = $conexion->prepare("INSERT INTO prioridades (nombre, nivel, tiempo_respuesta_objetivo) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $nombre, $nivel, $tiempo_total);
    $stmt->execute();
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Prioridad creada correctamente.</div>";
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Ya existe una prioridad con ese nombre.</div>";
    } else {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Error al crear la prioridad.</div>";
    }
}

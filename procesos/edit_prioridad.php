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

$id_prioridad = (int)($_POST['id_prioridad'] ?? 0);
$nombre  = limpiar_inputs($_POST['nombre'] ?? '');
$nivel   = (int)($_POST['nivel'] ?? 0);
$horas   = (int)($_POST['horas'] ?? 0);
$minutos = (int)($_POST['minutos'] ?? 0);
$tiempo_total = ($horas * 60) + $minutos;

if ($id_prioridad <= 0 || $nombre === '' || $nivel < 1 || $tiempo_total <= 0) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Datos inválidos.</div>";
    exit();
}

try {
    $stmt = $conexion->prepare("UPDATE prioridades SET nombre = ?, nivel = ?, tiempo_respuesta_objetivo = ? WHERE id_prioridad = ?");
    $stmt->bind_param("siii", $nombre, $nivel, $tiempo_total, $id_prioridad);
    $stmt->execute();
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Prioridad actualizada correctamente.</div>";
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Ya existe otra prioridad con ese nombre.</div>";
    } else {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Error al actualizar la prioridad.</div>";
    }
}

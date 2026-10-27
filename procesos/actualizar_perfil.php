<?php
session_start();
require_once '../includes/functions.php';
include '../config/conexion.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['usser'])) {
    http_response_code(403);
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>No autorizado.</div>";
    exit();
}

$nombres   = limpiar_inputs($_POST['nombres'] ?? '');
$apellidos = limpiar_inputs($_POST['apellidos'] ?? '');
$correo    = limpiar_inputs($_POST['correo'] ?? '');
$telefono  = procesar_opcional($_POST['telefono'] ?? null);

if ($nombres === '' || $apellidos === '' || $correo === '') {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Nombres, apellidos y correo son obligatorios.</div>";
    exit();
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>El correo electrónico no es válido.</div>";
    exit();
}

if ($telefono !== null && !preg_match('/^[0-9+\-()\s]{6,20}$/', $telefono)) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>El teléfono solo puede contener números, espacios y los símbolos + - ( ).</div>";
    exit();
}

try {
    // Solo se actualiza la fila del usuario en sesión; el rol, departamento y login no se tocan.
    $stmt = $conexion->prepare("UPDATE usuarios SET nombres = ?, apellidos = ?, correo = ?, telefono = ? WHERE usuario_login = ?");
    $stmt->bind_param("sssss", $nombres, $apellidos, $correo, $telefono, $_SESSION['usser']);
    $stmt->execute();
    echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Perfil actualizado correctamente.</div>";
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Ese correo ya está registrado en otra cuenta.</div>";
    } else {
        echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Error al actualizar el perfil.</div>";
    }
}

<?php
session_start();
require_once '../includes/constants.php';
include '../config/conexion.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['usser'])) {
    http_response_code(403);
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>No autorizado.</div>";
    exit();
}

// Las contraseñas NO se limpian con trim/stripslashes: cualquier carácter forma parte de ella.
$clave_actual    = $_POST['clave_actual'] ?? '';
$clave_nueva     = $_POST['clave_nueva'] ?? '';
$clave_confirmar = $_POST['clave_confirmar'] ?? '';

if ($clave_actual === '' || $clave_nueva === '' || $clave_confirmar === '') {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Completa los tres campos.</div>";
    exit();
}

if ($clave_nueva !== $clave_confirmar) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>La confirmación no coincide con la nueva contraseña.</div>";
    exit();
}

// Requisitos de la nueva contraseña
$errores = [];
// Cuenta caracteres (no bytes) sin depender de la extensión mbstring
$largo_clave = preg_match_all('/./us', $clave_nueva) ?: strlen($clave_nueva);
if ($largo_clave < LONGITUD_MIN_PASS)             $errores[] = 'al menos ' . LONGITUD_MIN_PASS . ' caracteres';
if (!preg_match('/[A-Z]/', $clave_nueva))         $errores[] = 'una mayúscula';
if (!preg_match('/[a-z]/', $clave_nueva))         $errores[] = 'una minúscula';
if (!preg_match('/[0-9]/', $clave_nueva))         $errores[] = 'un número';
if (!preg_match('/[^A-Za-z0-9]/', $clave_nueva))  $errores[] = 'un símbolo';

if (!empty($errores)) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>La nueva contraseña debe tener: " . implode(', ', $errores) . ".</div>";
    exit();
}

// Verificar la contraseña actual
$stmt = $conexion->prepare("SELECT id_usuario, `contraseña` AS hash_actual FROM usuarios WHERE usuario_login = ?");
$stmt->bind_param("s", $_SESSION['usser']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario || !password_verify($clave_actual, $usuario['hash_actual'])) {
    echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>La contraseña actual es incorrecta.</div>";
    exit();
}

if (password_verify($clave_nueva, $usuario['hash_actual'])) {
    echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle me-2'></i>La nueva contraseña debe ser distinta a la actual.</div>";
    exit();
}

$nuevo_hash = password_hash($clave_nueva, PASSWORD_BCRYPT);

$stmt = $conexion->prepare("UPDATE usuarios SET `contraseña` = ? WHERE id_usuario = ?");
$stmt->bind_param("si", $nuevo_hash, $usuario['id_usuario']);
$stmt->execute();
$stmt->close();

// Renovar el ID de sesión tras un cambio de credenciales
session_regenerate_id(true);

echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Contraseña actualizada correctamente.</div>";

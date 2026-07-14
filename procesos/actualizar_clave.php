<?php
include('../config/conexion.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit();

$token      = trim($_POST['token'] ?? '');
$nueva_pass = $_POST['nueva_pass'] ?? '';
$confirm    = $_POST['confirm_pass'] ?? '';

if (empty($token) || empty($nueva_pass)) {
    die('<div class="alert alert-danger small">Datos incompletos.</div>');
}

if ($nueva_pass !== $confirm) {
    die('<div class="alert alert-warning small">Las contraseñas no coinciden.</div>');
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.,;]).{12,}$/', $nueva_pass)) {
    die('<div class="alert alert-danger small">La contraseña no cumple los requisitos de seguridad.</div>');
}

// Validar token
$stmt = $conexion->prepare("
    SELECT r.id, r.id_usuario, r.fecha_expiracion, r.usado
    FROM recuperaciones r
    WHERE r.token = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$datos = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$datos) {
    die('<div class="alert alert-danger small">Token invalido.</div>');
}
if ($datos['usado']) {
    die('<div class="alert alert-danger small">Este enlace ya fue utilizado.</div>');
}
if (strtotime($datos['fecha_expiracion']) < time()) {
    die('<div class="alert alert-danger small">El enlace ha expirado. Genera uno nuevo.</div>');
}

// Actualizar contraseña
$hash = password_hash($nueva_pass, PASSWORD_BCRYPT);
$upd = $conexion->prepare("UPDATE usuarios SET contraseña = ? WHERE id_usuario = ?");
$upd->bind_param("si", $hash, $datos['id_usuario']);
$upd->execute();
$upd->close();

// Marcar token como usado
$used = $conexion->prepare("UPDATE recuperaciones SET usado = 1 WHERE id = ?");
$used->bind_param("i", $datos['id']);
$used->execute();
$used->close();

echo '<div class="alert alert-success small">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Contraseña actualizada correctamente.</strong><br>
        <span style="color:#64748b;">Seras redirigido al inicio de sesion en un momento...</span>
      </div>';

$conexion->close();
exit();
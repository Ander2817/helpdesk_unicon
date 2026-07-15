<?php
session_start();
include('../config/conexion.php');
include('../includes/constants.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit();

$identificador = trim($_POST['identificador'] ?? ''); //trim limpia to

if (empty($identificador)) {
    die('<div class="alert alert-warning small">Ingresa tu correo o usuario.</div>');
}

// Buscar usuario por correo o usuario_login
$stmt = $conexion->prepare("SELECT id_usuario, nombres, correo, usuario_login FROM usuarios WHERE (correo = ? OR usuario_login = ?) AND estado = 'activo'");
$stmt->bind_param("ss", $identificador, $identificador);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

// MOstrrar error
if (!$usuario) {
    echo '<div class="alert alert-danger small">
            <i class="fas fa-times-circle me-2"></i>
            No se encontró ningún usuario activo con ese correo o usuario de login.
          </div>';
    exit();
}

// Eliminar tokens anteriores de este usuario
$del = $conexion->prepare("DELETE FROM recuperaciones WHERE id_usuario = ?");
$del->bind_param("i", $usuario['id_usuario']);
$del->execute();
$del->close();

// Generar token seguro
$token = bin2hex(random_bytes(32)); // algoritmo que genera una cadena de 64 caracteres de 32 bin pasa a 64 hex
$expiracion = date('Y-m-d H:i:s', strtotime('+60 minutes')); // para que expire

// Guardar token en BD
$ins = $conexion->prepare("INSERT INTO recuperaciones (id_usuario, token, fecha_expiracion) VALUES (?, ?, ?)");
$ins->bind_param("iss", $usuario['id_usuario'], $token, $expiracion);
$ins->execute();
$ins->close();

// Construir enlace
$enlace = URL_BASE . "auth/resetear.php?token=" . $token;


// CUANDO TENGAMOS EL  CORREO CAMBIAR ACA:
// Por ahora mostraremos e el enlace en pantalla (modo desarrollo)
echo '<div class="alert alert-success small">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Enlace generado correctamente.</strong><br>
        <span style="color:#64748b;">Cuando esta funcionalidad esté configurado, llegará  un enlace desde: <strong>SoporteTecnicoSistemas@unicon.com.ve </strong></span>
      </div>
      <div class="alert alert-warning small mt-2">
        <i class="fas fa-flask me-2"></i>
        <strong>Modo desarrollo:</strong> Usa este enlace para restablecer la contraseña:<br>
        <a href="' . htmlspecialchars($enlace) . '" class="d-block mt-1 text-break" style="word-break:break-all;">' . htmlspecialchars($enlace) . '</a>
        <small class="text-muted">Expira en 60 minutos.</small>
      </div>';

$conexion->close();
exit();
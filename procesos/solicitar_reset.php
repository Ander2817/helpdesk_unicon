<?php
// procesos/solicitar_reset.php — "Olvidé mi contraseña" (versión segura)
//  • El enlace se envía SOLO al correo registrado (nunca se muestra en pantalla).
//  • La respuesta es siempre la misma: no revela si el usuario o el correo existen.
//  • El token se guarda con hash (SHA-256): ni con acceso a la BD se pueden usar los enlaces vigentes.
//  • Límites: 3 solicitudes / 10 min por navegador y 1 enlace nuevo cada 2 min por cuenta.
session_start();
include('../config/conexion.php');
require_once('../includes/constants.php');
require_once('../includes/correo.php');
require_once('../includes/bitacora.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit();

$identificador = trim($_POST['identificador'] ?? '');
if ($identificador === '' || mb_strlen($identificador) > 150) {
    die('<div class="alert alert-warning small">Ingresa tu correo o tu usuario.</div>');
}

$_SESSION['reset_pedidos'] = array_values(array_filter($_SESSION['reset_pedidos'] ?? [], fn($t) => $t > time() - 600));
if (count($_SESSION['reset_pedidos']) >= 3) {
    die('<div class="alert alert-warning small">Hiciste varias solicitudes seguidas. Espera unos minutos e inténtalo de nuevo.</div>');
}
$_SESSION['reset_pedidos'][] = time();

$stmt = $conexion->prepare("SELECT id_usuario, nombres, correo, usuario_login FROM usuarios WHERE (correo = ? OR usuario_login = ?) AND estado = 'activo' LIMIT 1");
$stmt->bind_param("ss", $identificador, $identificador);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($usuario && $usuario['correo']) {
    $id = (int)$usuario['id_usuario'];

    // ¿Ya se envió uno hace menos de 2 minutos? (el enlace dura 60: si le quedan más de 58, es reciente)
    $limite = date('Y-m-d H:i:s', time() + 58 * 60);
    $q = $conexion->prepare("SELECT 1 FROM recuperaciones WHERE id_usuario = ? AND usado = 0 AND fecha_expiracion > ? LIMIT 1");
    $q->bind_param("is", $id, $limite); $q->execute();
    $reciente = (bool)$q->get_result()->fetch_row(); $q->close();

    // En producción solo se envía si URL_BASE está fijada en config/local.php (evita enlaces con un Host falsificado)
    $url_confiable = (APP_ENV !== 'produccion') || URL_BASE_CONFIGURADA;
    if (!$url_confiable) error_log('[recuperar] En producción define URL_BASE en config/local.php; no se envió el enlace.');

    if (!$reciente && $url_confiable) {
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $expira = date('Y-m-d H:i:s', time() + 60 * 60);

        $conexion->begin_transaction();
        $d = $conexion->prepare("DELETE FROM recuperaciones WHERE id_usuario = ?");
        $d->bind_param("i", $id); $d->execute(); $d->close();
        $i = $conexion->prepare("INSERT INTO recuperaciones (id_usuario, token, fecha_expiracion) VALUES (?, ?, ?)");
        $i->bind_param("iss", $id, $token_hash, $expira); $i->execute(); $i->close();
        $conexion->commit();

        $enlace = URL_BASE . 'auth/resetear.php?token=' . $token;
        correo_enviar($usuario['correo'], '[' . NOMBRE_SISTEMA . '] Restablece tu contraseña',
            correo_plantilla('Restablecer contraseña',
                '<p>Hola ' . htmlspecialchars($usuario['nombres']) . ',</p><p>Recibimos una solicitud para restablecer la contraseña de tu cuenta <strong>'
                . htmlspecialchars($usuario['usuario_login']) . '</strong>. El enlace es válido por 60 minutos y solo se puede usar una vez.</p>'
                . '<p>Si no fuiste tú, ignora este mensaje: tu contraseña no cambiará.</p>', 'Crear nueva contraseña', $enlace),
            $usuario['nombres']);
        registrar_bitacora($conexion, 'RECUPERAR', 'usuarios', $id, "Solicitud de restablecimiento de contraseña: {$usuario['usuario_login']}");
    }
}

// Misma respuesta exista o no la cuenta
echo '<div class="alert alert-success small">
        <i class="fas fa-check-circle me-2"></i>
        Si los datos corresponden a una cuenta activa, enviamos un enlace al correo registrado. Revisa tu bandeja de entrada (y la carpeta de spam).
        <br><span style="color:#64748b;">El enlace vence en 60 minutos.</span>
      </div>';
$conexion->close();
exit();

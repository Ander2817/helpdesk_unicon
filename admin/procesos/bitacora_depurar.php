<?php
// admin/procesos/bitacora_depurar.php
// Borra registros ANTIGUOS de la bitácora (solo Administrador, POST, CSRF + contraseña).
// Nunca borra las entradas de RESTORE ni de DEPURAR: esas quedan como constancia permanente.
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) {
    http_response_code(403); exit('Acceso denegado.');
}
if (empty($_SESSION['csrf_bitacora']) || !hash_equals($_SESSION['csrf_bitacora'], (string)($_POST['csrf'] ?? ''))) {
    http_response_code(403); exit('Token de seguridad inválido. Recarga la página.');
}

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/bitacora.php';
require_once __DIR__ . '/../../includes/notificaciones.php';

$volver = '../bitacora.php';
$dias   = (int)($_POST['dias'] ?? 0);
if (!in_array($dias, [90, 180, 365, 730], true)) {
    set_alerta_flash('danger', 'Periodo no válido.'); redirigir($volver);
}

// Contraseña del administrador en sesión
$q = $conexion->prepare("SELECT contraseña FROM usuarios WHERE usuario_login = ? AND id_rol = 3");
$q->bind_param("s", $_SESSION['usser']);
$q->execute();
$f = $q->get_result()->fetch_assoc();
$q->close();
if (!$f || !password_verify((string)($_POST['password'] ?? ''), $f['contraseña'])) {
    registrar_bitacora($conexion, 'DEPURAR', 'bitacora', null, 'Intento de depuración RECHAZADO: contraseña incorrecta');
    set_alerta_flash('danger', 'Contraseña incorrecta. No se borró nada.'); redirigir($volver);
}

try {
    $limite = date('Y-m-d H:i:s', strtotime("-$dias days"));
    $del = $conexion->prepare("DELETE FROM bitacora WHERE fecha < ? AND accion NOT IN ('RESTORE', 'DEPURAR')");
    $del->bind_param("s", $limite);
    $del->execute();
    $n = $del->affected_rows;
    $del->close();

    registrar_bitacora($conexion, 'DEPURAR', 'bitacora', null,
        "Depuró $n registros de la bitácora anteriores a $limite (más de $dias días)", null, ['registros_borrados' => $n, 'anteriores_a' => $limite]);
    noti_admins($conexion, "{$_SESSION['usser']} depuró $n registros de la bitácora (más de $dias días).", 'sistema', 'admin/bitacora.php', noti_id_actual($conexion));
    set_alerta_flash('success', "Se eliminaron <strong>$n</strong> registros con más de $dias días.");
} catch (Throwable $e) {
    error_log('[bitacora_depurar] ' . $e->getMessage());
    set_alerta_flash('danger', 'No se pudo depurar la bitácora.');
}
redirigir($volver);

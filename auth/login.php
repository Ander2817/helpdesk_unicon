<?php
// auth/login.php — Inicio de sesión (versión endurecida)
//  • Límite de intentos (5 fallos → espera de LOGIN_BLOQUEO_SEGUNDOS) y aviso de intentos restantes  • Mensaje único (no revela si el usuario existe)
//  • Nuevo id de sesión al entrar            • Bloquea cuentas inactivas/suspendidas
//  • Actualiza el hash si el algoritmo quedó viejo  • Registra LOGIN en la bitácora
session_start();
require_once('../config/conexion.php');
require_once('../includes/constants.php');
require_once('../includes/functions.php');
require_once('../includes/estados.php');
require_once('../includes/bitacora.php');
require_once('../includes/login_guard.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirigir('../index.php');

$usser    = limpiar_inputs($_POST['user_login'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($usser === '' || $password === '') {
    set_alerta_flash('danger', 'Ingresa tu usuario y contraseña.');
    redirigir('../index.php');
}

$resta = login_bloqueado($conexion, $usser);
if ($resta !== null) {
    set_alerta_flash('danger', 'Demasiados intentos fallidos. Intenta de nuevo en ' . login_texto_espera($resta) . '.');
    redirigir('../index.php');
}

$stmt = $conexion->prepare("SELECT id_usuario, usuario_login, contraseña, id_rol, estado FROM usuarios WHERE usuario_login = ?");
$stmt->bind_param("s", $usser);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Se verifica SIEMPRE una contraseña (aunque el usuario no exista) para que el tiempo de respuesta no delate si existe
$hash_falso = '$2y$10$svFckSJzXWeIkjGv6N.cLeUfhOCLjSzyOQEw/72NZTxUBAJb2pvO6';
$ok = password_verify($password, $row['contraseña'] ?? $hash_falso) && $row;

if (!$ok) {
    $bloqueado = login_registrar_fallo($conexion, $usser);
    if ($bloqueado) {
        $espera = login_bloqueado($conexion, $usser) ?? LOGIN_BLOQUEO_SEGUNDOS;
        registrar_bitacora($conexion, 'LOGIN', 'usuarios', $row['id_usuario'] ?? null, "Cuenta bloqueada " . LOGIN_BLOQUEO_SEGUNDOS . " s por intentos fallidos: $usser");
        set_alerta_flash('danger', 'Demasiados intentos fallidos. Intenta de nuevo en ' . login_texto_espera($espera) . '.');
    } else {
        $quedan = login_intentos_restantes($conexion, $usser);
        set_alerta_flash($quedan <= 2 ? 'warning' : 'danger',
            'Usuario o contraseña incorrectos. Te ' . ($quedan === 1 ? 'queda 1 intento' : "quedan $quedan intentos") . ' antes de un bloqueo temporal.');
    }
    redirigir('../index.php');
}

if (estado_bloquea_login($conexion, $row['estado'])) {
    set_alerta_flash('danger', 'Tu cuenta está ' . $row['estado'] . '. Contacta al administrador.');
    redirigir('../index.php');
}

// Todo bien: sesión nueva (evita fijación de sesión)
session_regenerate_id(true);
$_SESSION['usser']         = $row['usuario_login'];
$_SESSION['id_rol']        = (int)$row['id_rol'];
$_SESSION['id_usuario']    = (int)$row['id_usuario'];
$_SESSION['ultimo_acceso'] = time();
login_limpiar($conexion, $usser);

if (password_needs_rehash($row['contraseña'], PASSWORD_DEFAULT)) {
    $nuevo = password_hash($password, PASSWORD_DEFAULT);
    $u = $conexion->prepare("UPDATE usuarios SET contraseña = ? WHERE id_usuario = ?");
    $u->bind_param("si", $nuevo, $row['id_usuario']); $u->execute(); $u->close();
}

registrar_bitacora($conexion, 'LOGIN', 'usuarios', $row['id_usuario'], "Inició sesión: $usser");

if ($_SESSION['id_rol'] === ROL_ADMIN)        redirigir('../admin/dashboard.php');
elseif ($_SESSION['id_rol'] === ROL_TECNICO)  redirigir('../tecnico/dashboard.php');
else                                          redirigir('../usuario/dashboard.php');

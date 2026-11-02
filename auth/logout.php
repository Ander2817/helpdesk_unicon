<?php
// auth/logout.php — Cierra la sesión. Con ?motivo=inactividad (lo usa el temporizador del navegador) deja un aviso en el login.
session_start();
$motivo = $_GET['motivo'] ?? '';
$_SESSION = [];
session_destroy();

if ($motivo === 'inactividad') {
    session_start();
    $_SESSION['alerta_flash'] = ['tipo' => 'warning', 'mensaje' => 'Tu sesión se cerró por inactividad. Inicia sesión de nuevo.'];
}
header("Location: ../index.php");
exit();

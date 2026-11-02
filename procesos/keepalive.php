<?php
// procesos/keepalive.php — El navegador avisa que el usuario sigue activo (alarga el tiempo de sesión).
// Responde 401 si la sesión ya caducó para que la página pueda cerrar sola.
session_start();
require_once __DIR__ . '/../includes/csrf.php';
csrf_verificar();
header('Content-Type: application/json');
if (!isset($_SESSION['usser'])) { http_response_code(401); echo '{"ok":false}'; exit(); }
$_SESSION['ultimo_acceso'] = time();
echo '{"ok":true}';

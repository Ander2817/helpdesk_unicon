<?php
// admin/procesos/estados_catalogo.php — Agrega un estado nuevo al ENUM o define cuáles estados bloquean el login (solo admin).
session_start();
header('Content-Type: application/json; charset=utf-8');
$out = function (bool $ok, string $msg) { echo json_encode(['ok' => $ok, 'msg' => $msg], JSON_UNESCAPED_UNICODE); exit; };
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) $out(false, 'Acceso denegado.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') $out(false, 'Método no permitido.');
require_once '../../includes/constants.php';
if (is_file('../../includes/csrf.php')) { require_once '../../includes/csrf.php'; if (function_exists('csrf_verificar')) csrf_verificar(); }
require_once '../../includes/estados.php';
include '../../config/conexion.php';
if (is_file('../../includes/bitacora.php')) require_once '../../includes/bitacora.php';
$bit = function (string $accion, string $tabla, string $desc) use ($conexion) {
    if (function_exists('registrar_bitacora')) { try { registrar_bitacora($conexion, $accion, $tabla, 0, $desc); } catch (Throwable $e) {} }
};

$accion = $_POST['accion'] ?? '';
if ($accion === 'agregar') {
    [$ok, $msg] = est_agregar_valor($conexion, (string)($_POST['tipo'] ?? ''), (string)($_POST['valor'] ?? ''));
    if ($ok) $bit('NUEVO_ESTADO', 'estados', $msg);
    $out($ok, $msg);
}
if ($accion === 'bloqueo_login') {
    $lista = array_filter((array)($_POST['bloquean'] ?? []), 'is_string');
    est_guardar_bloqueantes($conexion, $lista);
    $bit('CONFIG_ESTADOS', 'estados', 'Estados que bloquean el login: ' . implode(', ', $lista));
    $out(true, 'Configuración guardada.');
}
$out(false, 'Acción no válida.');

<?php
// admin/procesos/asignacion.php — Acciones de la pantalla «Asignación de tickets» (solo admin, POST, JSON).
//   accion = config        → guarda activa (0/1), modo (departamento|carga), max (número)
//   accion = auto_uno      → asigna automáticamente el ticket id_ticket
//   accion = auto_pendientes → asigna todos los tickets sin técnico
session_start();
header('Content-Type: application/json; charset=utf-8');
$out = function (bool $ok, string $msg, array $x = []) { echo json_encode(['ok' => $ok, 'msg' => $msg] + $x, JSON_UNESCAPED_UNICODE); exit; };
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) $out(false, 'Acceso denegado.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') $out(false, 'Método no permitido.');
require_once '../../includes/constants.php';
if (is_file('../../includes/csrf.php')) { require_once '../../includes/csrf.php'; if (function_exists('csrf_verificar')) csrf_verificar(); }
include '../../config/conexion.php';
require_once '../../includes/asignacion.php';
if (is_file('../../includes/bitacora.php')) require_once '../../includes/bitacora.php';
$yo = (int)($_SESSION['id_usuario'] ?? 0);
if ($yo <= 0) {
    $s = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?"); $s->bind_param("s", $_SESSION['usser']); $s->execute();
    $yo = (int)($s->get_result()->fetch_row()[0] ?? 0); $s->close();
}
$bit = function (string $desc) use ($conexion) { if (function_exists('registrar_bitacora')) { try { registrar_bitacora($conexion, 'ASIGNACION_AUTO', 'tickets', 0, $desc); } catch (Throwable $e) {} } };

$accion = $_POST['accion'] ?? '';
if ($accion === 'config') {
    $activa = ($_POST['activa'] ?? '0') === '1' ? '1' : '0';
    $modo = in_array($_POST['modo'] ?? '', ['departamento', 'carga'], true) ? $_POST['modo'] : 'departamento';
    $max = max(0, min(999, (int)($_POST['max'] ?? 0)));
    asig_set($conexion, 'asig_auto', $activa); asig_set($conexion, 'asig_modo', $modo); asig_set($conexion, 'asig_max', (string)$max);
    $bit("Asignación automática: " . ($activa === '1' ? 'activada' : 'desactivada') . ", modo $modo, tope $max");
    $extra = [];
    if ($activa === '1') { $n = asig_pendientes($conexion, $yo); $extra['asignados'] = $n; }
    $out(true, 'Configuración guardada.' . (!empty($extra['asignados']) ? " Se asignaron {$extra['asignados']} ticket(s) que estaban sin técnico." : ''), $extra);
}
if ($accion === 'auto_uno') {
    $id = (int)($_POST['id_ticket'] ?? 0);
    if ($id <= 0) $out(false, 'Ticket no válido.');
    $nom = asig_auto($conexion, $id, $yo, true);
    if ($nom === null) $out(false, 'No se pudo asignar: no hay técnicos disponibles (o el ticket ya tiene técnico).');
    $bit("Ticket #$id asignado automáticamente a $nom por el administrador");
    $out(true, "Asignado a $nom.", ['tecnico' => $nom]);
}
if ($accion === 'auto_pendientes') {
    $n = asig_pendientes($conexion, $yo, true);
    if ($n > 0) $bit("Asignación de pendientes: $n ticket(s)");
    $out(true, $n > 0 ? "Se asignaron $n ticket(s)." : 'No había tickets por asignar o no hay técnicos disponibles.', ['asignados' => $n]);
}
$out(false, 'Acción no válida.');

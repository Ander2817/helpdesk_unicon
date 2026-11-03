<?php
// procesos/notificaciones.php — API JSON de la campana: lista | leer | leer_todas
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) { http_response_code(401); echo '{"error":"sesion"}'; exit(); }

require_once '../includes/constants.php';
require_once '../config/conexion.php';
require_once '../includes/notificaciones.php';
if (is_file(__DIR__ . '/../includes/sla.php')) require_once '../includes/sla.php';   // módulo SLA (opcional)

$id_usuario = noti_id_actual($conexion);
$rol  = (int)$_SESSION['id_rol'];
$csrf = (string)($_SESSION['csrf_notif'] ?? '');
session_write_close();
if (!$id_usuario) { http_response_code(401); echo '{"error":"usuario"}'; exit(); }

$accion = $_REQUEST['accion'] ?? 'lista';

// Las acciones que cambian datos exigen POST + token
if ($accion !== 'lista') {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $csrf === '' || !hash_equals($csrf, $token)) {
        http_response_code(403); echo '{"error":"csrf"}'; exit();
    }
}

if ($accion === 'leer') {
    $id = (int)($_POST['id'] ?? 0);
    $q = $conexion->prepare("UPDATE notificaciones SET leida = 1 WHERE id_notificacion = ? AND id_usuario = ?");
    $q->bind_param("ii", $id, $id_usuario); $q->execute();
    echo '{"ok":true}'; exit();
}
if ($accion === 'leer_todas') {
    $q = $conexion->prepare("UPDATE notificaciones SET leida = 1 WHERE id_usuario = ? AND leida = 0");
    $q->bind_param("i", $id_usuario); $q->execute();
    echo '{"ok":true}'; exit();
}

// ---- lista ----
if (function_exists('sla_revisar_vencidos')) sla_revisar_vencidos($conexion);   // como mucho una vez cada 10 min: avisa de tickets por vencer / vencidos
if (is_file(__DIR__ . '/../includes/tareas_programadas.php')) { require_once __DIR__ . '/../includes/tareas_programadas.php'; tareas_ejecutar($conexion); }   // autocierre y avisos de mantenimiento (cada 15 min)
function hace(int $ts): string {
    $d = time() - $ts;
    if ($d < 60)     return 'hace un momento';
    if ($d < 3600)   return 'hace ' . floor($d / 60) . ' min';
    if ($d < 86400)  return 'hace ' . floor($d / 3600) . ' h';
    if ($d < 604800) return 'hace ' . floor($d / 86400) . ' d';
    return date('d/m/Y', $ts);
}
$carpeta = [ROL_ADMIN => 'admin', ROL_TECNICO => 'tecnico', ROL_USUARIO => 'usuario'][$rol] ?? 'usuario';

$q = $conexion->prepare("SELECT * FROM notificaciones WHERE id_usuario = ? ORDER BY fecha DESC, id_notificacion DESC LIMIT 15");
$q->bind_param("i", $id_usuario); $q->execute();
$items = [];
foreach ($q->get_result() as $f) {
    $url = null;
    if (!empty($f['id_ticket'])) {
        $url = URL_BASE . $carpeta . '/ver_ticket.php?id=' . (int)$f['id_ticket'];
    } elseif (!empty($f['url']) && preg_match('#^[a-z0-9_/]+\.php(\?[\w=&%.-]*)?$#i', $f['url'])) {
        $url = URL_BASE . $f['url'];
    }
    $items[] = [
        'id' => (int)$f['id_notificacion'], 'mensaje' => $f['mensaje'], 'leida' => (bool)$f['leida'],
        'cuando' => hace(strtotime($f['fecha'])), 'url' => $url,
    ];
}
$q = $conexion->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leida = 0");
$q->bind_param("i", $id_usuario); $q->execute();
$no_leidas = (int)$q->get_result()->fetch_row()[0];

// Limpieza ocasional: las ya leídas con más de 90 días se borran
if (random_int(1, 100) === 1) {
    try { $conexion->query("DELETE FROM notificaciones WHERE leida = 1 AND fecha < (NOW() - INTERVAL 90 DAY)"); } catch (Throwable $e) {}
}
echo json_encode(['no_leidas' => $no_leidas, 'items' => $items], JSON_UNESCAPED_UNICODE);

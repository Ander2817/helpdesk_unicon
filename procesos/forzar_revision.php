<?php
// procesos/forzar_revision.php — (solo admin) ejecuta ahora las revisiones automáticas: SLA, autocierre y avisos de mantenimiento.
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) { http_response_code(403); echo '{"ok":false,"msg":"No autorizado."}'; exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo '{"ok":false}'; exit(); }
require_once '../includes/constants.php';
require_once '../includes/csrf.php'; csrf_verificar();
include '../config/conexion.php';
session_write_close();
try {
    require_once '../includes/sla.php';
    require_once '../includes/tareas_programadas.php';
    $conexion->query("UPDATE sistema_estado SET actualizado = '2000-01-01 00:00:00' WHERE clave = 'sla_revision'");
    sla_revisar_vencidos($conexion);
    tareas_ejecutar($conexion, true);
    echo json_encode(['ok' => true, 'msg' => 'Revisión ejecutada.']);
} catch (Throwable $e) {
    error_log('[forzar_revision] ' . $e->getMessage());
    http_response_code(500); echo json_encode(['ok' => false, 'msg' => 'No se pudo ejecutar la revisión.']);
}

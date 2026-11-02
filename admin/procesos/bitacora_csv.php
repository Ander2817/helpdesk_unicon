<?php
// admin/procesos/bitacora_csv.php
// Exporta la bitácora (con los mismos filtros de la pantalla) a CSV, compatible con Excel.
// Úsalo ANTES de depurar para conservar el historial.
session_start();
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) { http_response_code(403); exit('Acceso denegado.'); }
session_write_close();

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/bitacora.php';

$where = []; $tipos = ''; $params = [];
if (!empty($_GET['f_accion'])) { $where[] = 'accion = ?';         $tipos .= 's'; $params[] = $_GET['f_accion']; }
if (!empty($_GET['f_tabla']))  { $where[] = 'tabla_afectada = ?'; $tipos .= 's'; $params[] = $_GET['f_tabla']; }
if (!empty($_GET['f_desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['f_desde'])) { $where[] = 'fecha >= ?'; $tipos .= 's'; $params[] = $_GET['f_desde'] . ' 00:00:00'; }
if (!empty($_GET['f_hasta']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['f_hasta'])) { $where[] = 'fecha <= ?'; $tipos .= 's'; $params[] = $_GET['f_hasta'] . ' 23:59:59'; }
$sql = "SELECT id_bitacora, fecha, usuario_login, accion, tabla_afectada, id_registro, descripcion, ip, datos_anteriores, datos_nuevos
        FROM bitacora" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY fecha DESC, id_bitacora DESC LIMIT 200000";
$st = $conexion->prepare($sql);
if ($tipos !== '') $st->bind_param($tipos, ...$params);
$st->execute();
$res = $st->get_result();

registrar_bitacora($conexion, 'EXPORT', 'bitacora', null, 'Exportó la bitácora a CSV (' . $res->num_rows . ' registros)');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="bitacora_' . date('Ymd_His') . '.csv"');
header('X-Content-Type-Options: nosniff');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM: Excel abre bien las tildes

// Evita inyección de fórmulas al abrir en Excel (=, +, -, @ al inicio de una celda)
$seguro = fn($v) => (is_string($v) && $v !== '' && strpbrk($v[0], "=+-@\t\r") !== false) ? "'" . $v : $v;

fputcsv($out, ['ID', 'Fecha', 'Usuario', 'Acción', 'Tabla', 'ID registro', 'Descripción', 'IP', 'Datos anteriores', 'Datos nuevos'], ';');
while ($r = $res->fetch_assoc()) {
    fputcsv($out, array_map($seguro, array_values($r)), ';');
}
fclose($out);

<?php
// admin/procesos/bitacora_data.php
// Endpoint JSON para DataTables (paginación del lado del servidor) y detalle de un registro.
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}
session_write_close(); // no bloquear otras peticiones del mismo usuario

require_once __DIR__ . '/../../config/conexion.php';

// ---------- Detalle de un registro (modal "Ver") ----------
if (isset($_GET['detalle'])) {
    $id = (int)$_GET['detalle'];
    $q = $conexion->prepare("SELECT id_bitacora, usuario_login, accion, tabla_afectada, id_registro, descripcion,
                                    datos_anteriores, datos_nuevos, ip, user_agent, fecha
                             FROM bitacora WHERE id_bitacora = ?");
    $q->bind_param("i", $id);
    $q->execute();
    $fila = $q->get_result()->fetch_assoc();
    echo json_encode($fila ?: ['error' => 'No encontrado'], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    exit();
}

// ---------- Listado server-side ----------
$draw   = (int)($_POST['draw'] ?? 0);
$start  = max(0, (int)($_POST['start'] ?? 0));
$length = (int)($_POST['length'] ?? 25);
if ($length < 1 || $length > 200) $length = 25;

// Columnas en el mismo orden que en la tabla HTML (lista blanca para ORDER BY)
$columnas = ['id_bitacora', 'fecha', 'usuario_login', 'accion', 'tabla_afectada', 'descripcion', 'ip'];
$ordCol = (int)($_POST['order'][0]['column'] ?? 1);
$ordCol = $columnas[$ordCol] ?? 'fecha';
$ordDir = (($_POST['order'][0]['dir'] ?? 'desc') === 'asc') ? 'ASC' : 'DESC';

$where  = [];
$tipos  = '';
$params = [];

$busqueda = trim($_POST['search']['value'] ?? '');
if ($busqueda !== '') {
    $where[] = "(usuario_login LIKE ? OR descripcion LIKE ? OR tabla_afectada LIKE ? OR id_registro LIKE ? OR ip LIKE ?)";
    $like = '%' . $busqueda . '%';
    for ($i = 0; $i < 5; $i++) { $tipos .= 's'; $params[] = $like; }
}
if (!empty($_POST['f_accion'])) { $where[] = "accion = ?";         $tipos .= 's'; $params[] = $_POST['f_accion']; }
if (!empty($_POST['f_tabla']))  { $where[] = "tabla_afectada = ?"; $tipos .= 's'; $params[] = $_POST['f_tabla']; }
if (!empty($_POST['f_desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['f_desde'])) {
    $where[] = "fecha >= ?"; $tipos .= 's'; $params[] = $_POST['f_desde'] . ' 00:00:00';
}
if (!empty($_POST['f_hasta']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['f_hasta'])) {
    $where[] = "fecha <= ?"; $tipos .= 's'; $params[] = $_POST['f_hasta'] . ' 23:59:59';
}
$sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

function ejecutar(mysqli $c, string $sql, string $tipos, array $params) {
    $st = $c->prepare($sql);
    if ($tipos !== '') $st->bind_param($tipos, ...$params);
    $st->execute();
    return $st->get_result();
}

$total    = (int)$conexion->query("SELECT COUNT(*) FROM bitacora")->fetch_row()[0];
$filtrado = (int)ejecutar($conexion, "SELECT COUNT(*) FROM bitacora" . $sqlWhere, $tipos, $params)->fetch_row()[0];

$sql = "SELECT id_bitacora, fecha, usuario_login, accion, tabla_afectada, id_registro, descripcion, ip
        FROM bitacora $sqlWhere ORDER BY $ordCol $ordDir, id_bitacora DESC LIMIT ? OFFSET ?";
$res = ejecutar($conexion, $sql, $tipos . 'ii', array_merge($params, [$length, $start]));

$data = [];
while ($r = $res->fetch_assoc()) {
    $e = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    $data[] = [
        'id'          => (int)$r['id_bitacora'],
        'fecha'       => $e(date('d/m/Y H:i:s', strtotime($r['fecha']))),
        'usuario'     => $e($r['usuario_login']),
        'accion'      => $e($r['accion']),
        'tabla'       => $e($r['tabla_afectada']),
        'id_registro' => $e($r['id_registro']),
        'descripcion' => $e($r['descripcion']),
        'ip'          => $e($r['ip']),
    ];
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total,
    'recordsFiltered' => $filtrado,
    'data'            => $data,
], JSON_UNESCAPED_UNICODE);

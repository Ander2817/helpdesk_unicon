<?php
// procesos/buscar.php — Buscador de la barra superior. Cada rol solo encuentra lo que puede ver.
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) { http_response_code(401); echo '{"error":"sesion"}'; exit(); }

require_once '../includes/constants.php';
require_once '../config/conexion.php';
require_once '../includes/permisos.php';
require_once '../includes/notificaciones.php';

$rol = (int)$_SESSION['id_rol'];
$yo  = noti_id_actual($conexion);
session_write_close();

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) < 2 || mb_strlen($q) > 60 || !$yo) { echo '{"grupos":[]}'; exit(); }
$like = '%' . addcslashes($q, '%_\\') . '%';
$carpeta = [ROL_ADMIN => 'admin', ROL_TECNICO => 'tecnico', ROL_USUARIO => 'usuario'][$rol] ?? 'usuario';
$grupos = [];

// ---- Tickets ----
$sql = "SELECT t.id_tickets, t.codigo_ticket, t.asunto, e.nombre AS estado
        FROM tickets t JOIN estados_ticket e ON t.id_estado = e.id_estado_ticket
        WHERE (t.codigo_ticket LIKE ? OR t.asunto LIKE ? OR t.descripcion LIKE ?)";
$tipos = 'sss'; $par = [$like, $like, $like];
if ($rol === ROL_USUARIO) {
    $sql .= " AND t.id_usuario_solicitante = ?"; $tipos .= 'i'; $par[] = $yo;
} elseif ($rol === ROL_TECNICO) {
    $extra = rol_tiene_permiso($conexion, $rol, 'tickets.tomar') ? " OR t.id_usuario_asignado IS NULL" : '';
    $sql .= " AND (t.id_usuario_asignado = ? OR t.id_usuario_solicitante = ?$extra)"; $tipos .= 'ii'; $par[] = $yo; $par[] = $yo;
}
$sql .= " ORDER BY t.fecha_creacion DESC LIMIT 6";
$st = $conexion->prepare($sql); $st->bind_param($tipos, ...$par); $st->execute();
$items = [];
foreach ($st->get_result() as $f) {
    $items[] = ['t' => $f['codigo_ticket'] . ' — ' . $f['asunto'], 's' => $f['estado'],
                'u' => URL_BASE . $carpeta . '/ver_ticket.php?id=' . (int)$f['id_tickets']];
}
if ($items) $grupos[] = ['titulo' => $rol === ROL_USUARIO ? 'Mis tickets' : 'Tickets', 'items' => $items];

// ---- Usuarios (solo admin) ----
if ($rol === ROL_ADMIN) {
    $st = $conexion->prepare("SELECT nombres, apellidos, usuario_login, correo FROM usuarios
                              WHERE nombres LIKE ? OR apellidos LIKE ? OR usuario_login LIKE ? OR correo LIKE ?
                              ORDER BY nombres LIMIT 5");
    $st->bind_param("ssss", $like, $like, $like, $like); $st->execute();
    $items = [];
    foreach ($st->get_result() as $f) {
        $items[] = ['t' => $f['nombres'] . ' ' . $f['apellidos'], 's' => '@' . $f['usuario_login'] . ' · ' . $f['correo'],
                    'u' => URL_BASE . 'admin/procesos/usuarios.php?q=' . urlencode($f['usuario_login'])];
    }
    if ($items) $grupos[] = ['titulo' => 'Usuarios', 'items' => $items];
}

// ---- Equipos (admin, o técnico con permiso) ----
if ($rol === ROL_ADMIN || ($rol === ROL_TECNICO && rol_tiene_permiso($conexion, $rol, 'inventario.ver'))) {
    $st = $conexion->prepare("SELECT codigo_equipo, tipo_equipo, marca, modelo, serial FROM inventario_tecnologico
                              WHERE codigo_equipo LIKE ? OR tipo_equipo LIKE ? OR marca LIKE ? OR modelo LIKE ? OR serial LIKE ? OR ubicacion LIKE ?
                              ORDER BY codigo_equipo LIMIT 5");
    $st->bind_param("ssssss", $like, $like, $like, $like, $like, $like); $st->execute();
    $items = [];
    foreach ($st->get_result() as $f) {
        $items[] = ['t' => $f['codigo_equipo'] . ' — ' . $f['tipo_equipo'], 's' => trim($f['marca'] . ' ' . $f['modelo']) . ' · ' . $f['serial'],
                    'u' => URL_BASE . $carpeta . '/inventario.php?q=' . urlencode($f['codigo_equipo'])];
    }
    if ($items) $grupos[] = ['titulo' => 'Equipos', 'items' => $items];
}
echo json_encode(['grupos' => $grupos], JSON_UNESCAPED_UNICODE);

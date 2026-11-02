<?php
// procesos/kb_buscar.php — sugerencias mientras se escribe el asunto de un ticket (artículos publicados).
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) { http_response_code(401); echo '{"items":[]}'; exit(); }
require_once '../includes/constants.php';
include '../config/conexion.php';
$rol = (int)$_SESSION['id_rol'];
session_write_close();

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) < 4 || mb_strlen($q) > 80) { echo '{"items":[]}'; exit(); }
$carpeta = [ROL_ADMIN => 'admin', ROL_TECNICO => 'tecnico', ROL_USUARIO => 'usuario'][$rol] ?? 'usuario';

// Cada palabra de 4+ letras cuenta; gana el artículo que coincide con más palabras
$palabras = array_slice(array_values(array_unique(array_filter(preg_split('/\s+/u', mb_strtolower($q)), fn($w) => mb_strlen($w) >= 4))), 0, 5);
if (!$palabras) { echo '{"items":[]}'; exit(); }
try {
    $puntos = []; $tipos = ''; $par = [];
    foreach ($palabras as $w) { $puntos[] = '(a.titulo LIKE ? OR a.contenido LIKE ?)'; $l = '%' . addcslashes($w, '%_\\') . '%'; $tipos .= 'ss'; $par[] = $l; $par[] = $l; }
    $sql = "SELECT a.id_articulo, a.titulo, (" . implode(' + ', $puntos) . ") AS pts FROM kb_articulos a WHERE a.publicado = 1 HAVING pts > 0 ORDER BY pts DESC, a.vistas DESC LIMIT 3";
    $st = $conexion->prepare($sql); $st->bind_param($tipos, ...$par); $st->execute();
    $items = [];
    foreach ($st->get_result() as $f) $items[] = ['t' => $f['titulo'], 'u' => URL_BASE . $carpeta . '/kb.php?id=' . (int)$f['id_articulo']];
    echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) { echo '{"items":[]}'; }

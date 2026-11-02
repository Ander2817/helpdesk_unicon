<?php
// procesos/kb_guardar.php — crea o edita un artículo (admin, o técnico con permiso «kb.editar»).
session_start();
require_once __DIR__ . '/../includes/csrf.php'; csrf_verificar(); /* csrf */
require_once '../includes/constants.php';
require_once '../includes/functions.php';
require_once '../includes/permisos.php';
include '../config/conexion.php';
require_once '../includes/bitacora.php';
require_once '../includes/notificaciones.php';
header('Content-Type: text/html; charset=utf-8');

function res_kb(string $tipo, string $m): never { echo "<div class='alert alert-$tipo'>$m</div>"; exit(); }

$rol = (int)($_SESSION['id_rol'] ?? 0);
if (!isset($_SESSION['usser']) || !rol_tiene_permiso($conexion, $rol, 'kb.editar')) { http_response_code(403); res_kb('danger', 'No tienes permiso para editar la base de conocimiento.'); }

$id    = (int)($_POST['id'] ?? 0);
$tit   = trim((string)($_POST['titulo'] ?? ''));
$cont  = trim((string)($_POST['contenido'] ?? ''));
$cat   = (int)($_POST['id_categoria'] ?? 0);
$pub   = !empty($_POST['publicado']) ? 1 : 0;
if (mb_strlen($tit) < 5 || mb_strlen($tit) > 200) res_kb('warning', 'El título debe tener entre 5 y 200 caracteres.');
if (mb_strlen($cont) < 10 || mb_strlen($cont) > 20000) res_kb('warning', 'El contenido debe tener entre 10 y 20.000 caracteres.');
$cat_db = null;
if ($cat > 0) {
    $q = $conexion->prepare("SELECT 1 FROM categoria_incidencias WHERE id_categoria = ?"); $q->bind_param("i", $cat); $q->execute();
    if (!$q->get_result()->fetch_row()) res_kb('warning', 'La categoría elegida no existe.');
    $cat_db = $cat;
}
$yo = noti_id_actual($conexion);

if ($id > 0) {
    $antes = bitacora_fila($conexion, 'kb_articulos', 'id_articulo', $id);
    if (!$antes) res_kb('danger', 'El artículo no existe.');
    $u = $conexion->prepare("UPDATE kb_articulos SET titulo = ?, contenido = ?, id_categoria = ?, publicado = ? WHERE id_articulo = ?");
    $u->bind_param("ssiii", $tit, $cont, $cat_db, $pub, $id); $u->execute(); $u->close();
    registrar_bitacora($conexion, 'UPDATE', 'kb_articulos', $id, "Editó el artículo de ayuda «{$tit}»", ['titulo' => $antes['titulo'], 'publicado' => $antes['publicado']], ['titulo' => $tit, 'publicado' => $pub]);
    res_kb('success', "<i class='fas fa-check-circle me-2'></i>Artículo actualizado.");
}
$i = $conexion->prepare("INSERT INTO kb_articulos (titulo, contenido, id_categoria, publicado, id_autor) VALUES (?, ?, ?, ?, ?)");
$i->bind_param("ssiii", $tit, $cont, $cat_db, $pub, $yo); $i->execute();
$nuevo = $conexion->insert_id; $i->close();
registrar_bitacora($conexion, 'INSERT', 'kb_articulos', $nuevo, "Creó el artículo de ayuda «{$tit}»", null, ['titulo' => $tit, 'publicado' => $pub]);
res_kb('success', "<i class='fas fa-check-circle me-2'></i>Artículo creado.");

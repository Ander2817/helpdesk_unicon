<?php
session_start();
require_once __DIR__ . '/../includes/csrf.php'; csrf_verificar(); /* csrf */
require_once '../includes/constants.php';
require_once '../includes/permisos.php';
include '../config/conexion.php';
require_once '../includes/bitacora.php';
header('Content-Type: text/html; charset=utf-8');

$rol = (int)($_SESSION['id_rol'] ?? 0);
if (!isset($_SESSION['usser']) || !rol_tiene_permiso($conexion, $rol, 'kb.editar')) { http_response_code(403); exit("<div class='alert alert-danger'>No autorizado.</div>"); }
$id = (int)($_POST['id'] ?? 0);
$antes = bitacora_fila($conexion, 'kb_articulos', 'id_articulo', $id);
if (!$antes) exit("<div class='alert alert-danger'>El artículo no existe.</div>");
$d = $conexion->prepare("DELETE FROM kb_articulos WHERE id_articulo = ?"); $d->bind_param("i", $id); $d->execute();
registrar_bitacora($conexion, 'DELETE', 'kb_articulos', $id, "Eliminó el artículo de ayuda «{$antes['titulo']}»", ['titulo' => $antes['titulo'], 'publicado' => $antes['publicado']]);
echo "<div class='alert alert-success'>Artículo eliminado.</div>";

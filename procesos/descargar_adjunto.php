<?php
/**
 * procesos/descargar_adjunto.php?id=N            -> descarga el archivo
 * procesos/descargar_adjunto.php?id=N&ver=1      -> si es una imagen, la muestra en el navegador
 *
 * Es la ÚNICA forma de obtener un adjunto (la carpeta uploads/ está cerrada al acceso directo).
 * Quien lo pide debe poder ver el ticket, con las mismas reglas que ver_ticket.php:
 *   admin    -> cualquiera
 *   técnico  -> los que le asignaron o los que él creó
 *   usuario  -> solo los suyos
 */
session_start();
require_once '../includes/constants.php';
require_once '../includes/adjuntos.php';
include '../config/conexion.php';

function salir_con_error(int $codigo, string $mensaje): void {
    http_response_code($codigo);
    header('Content-Type: text/plain; charset=utf-8');
    echo $mensaje;
    exit();
}

if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    salir_con_error(403, 'Debes iniciar sesión.');
}

$id_adjunto = (int)($_GET['id'] ?? 0);
if ($id_adjunto <= 0) salir_con_error(400, 'Solicitud inválida.');

$stmt = $conexion->prepare("
    SELECT a.nombre_archivo, a.ruta_archivo, a.tipo_archivo,
           t.id_usuario_solicitante, t.id_usuario_asignado
    FROM adjuntos a
    JOIN tickets t ON a.id_ticket = t.id_tickets
    WHERE a.id_adjunto = ?
");
$stmt->bind_param("i", $id_adjunto);
$stmt->execute();
$adj = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$adj) salir_con_error(404, 'El archivo no existe.');

// ---- Permisos: los mismos que para ver el ticket ----
$stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
$stmt->bind_param("s", $_SESSION['usser']);
$stmt->execute();
$id_yo = (int)($stmt->get_result()->fetch_assoc()['id_usuario'] ?? 0);
$stmt->close();

$rol = (int)$_SESSION['id_rol'];
$autorizado = false;
if ($rol === ROL_ADMIN) {
    $autorizado = true;
} elseif ($id_yo > 0 && $rol === ROL_TECNICO) {
    $autorizado = ((int)$adj['id_usuario_asignado'] === $id_yo) || ((int)$adj['id_usuario_solicitante'] === $id_yo);
} elseif ($id_yo > 0 && $rol === ROL_USUARIO) {
    $autorizado = ((int)$adj['id_usuario_solicitante'] === $id_yo);
}
if (!$autorizado) salir_con_error(403, 'No tienes permiso para ver este archivo.');

// ---- Ubicar el archivo sin permitir salirse de la carpeta de adjuntos ----
$base = realpath(adjuntos_directorio());
$ruta = $base ? realpath($base . '/' . $adj['ruta_archivo']) : false;
if (!$ruta || strpos($ruta, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($ruta)) {
    salir_con_error(404, 'El archivo ya no está disponible en el servidor.');
}

$ext  = strtolower($adj['tipo_archivo']);
$mime = adjunto_mime($ext);
$mostrar_en_pagina = (isset($_GET['ver']) && adjunto_es_imagen($ext));

// Nombre para el navegador: sin saltos de línea ni comillas que rompan la cabecera
$nombre = str_replace(['"', "\r", "\n"], '', $adj['nombre_archivo']);
$nombre_ascii = preg_replace('/[^A-Za-z0-9._-]+/', '_', $nombre) ?: 'archivo';

header('Content-Type: ' . ($mostrar_en_pagina ? $mime : 'application/octet-stream'));
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: ' . ($mostrar_en_pagina ? 'inline' : 'attachment')
       . '; filename="' . $nombre_ascii . '"; filename*=UTF-8\'\'' . rawurlencode($nombre));
header('X-Content-Type-Options: nosniff');            // el navegador no "adivina" otro tipo
header('Content-Security-Policy: default-src \'none\'; img-src \'self\'; style-src \'unsafe-inline\'');
header('Cache-Control: private, max-age=3600');

readfile($ruta);
exit();

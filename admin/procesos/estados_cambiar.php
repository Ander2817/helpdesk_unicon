<?php
// admin/procesos/estados_cambiar.php — Cambia el estado de un usuario / equipo / departamento (solo admin, POST, JSON).
session_start();
header('Content-Type: application/json; charset=utf-8');
$out = function (bool $ok, string $msg, array $extra = []) { echo json_encode(['ok' => $ok, 'msg' => $msg] + $extra, JSON_UNESCAPED_UNICODE); exit; };
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) $out(false, 'Acceso denegado.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') $out(false, 'Método no permitido.');
require_once '../../includes/constants.php';
if (is_file('../../includes/csrf.php')) { require_once '../../includes/csrf.php'; if (function_exists('csrf_verificar')) csrf_verificar(); }
require_once '../../includes/estados.php';
include '../../config/conexion.php';
if (is_file('../../includes/bitacora.php')) require_once '../../includes/bitacora.php';

$cat = est_catalogo();
$clave = $_POST['tipo'] ?? ''; $id = (int)($_POST['id'] ?? 0); $nuevo = (string)($_POST['estado'] ?? '');
if (!isset($cat[$clave]) || $id <= 0) $out(false, 'Datos no válidos.');
$tabla = $cat[$clave]['tabla']; $pk = $cat[$clave]['pk'];
if (!in_array($nuevo, est_valores($conexion, $tabla), true)) $out(false, 'Estado no válido para ' . $cat[$clave]['titulo'] . '.');

$q = $conexion->prepare("SELECT estado" . ($clave === 'usuarios' ? ", id_rol" : "") . " FROM `$tabla` WHERE `$pk` = ?");
$q->bind_param("i", $id); $q->execute(); $fila = $q->get_result()->fetch_assoc(); $q->close();
if (!$fila) $out(false, 'El registro no existe.');
$antes = $fila['estado'];
if ($antes === $nuevo) $out(true, 'Sin cambios.', ['estado' => $nuevo]);

$aviso = '';
if ($clave === 'usuarios') {
    $yo = (int)($_SESSION['id_usuario'] ?? 0);
    $bloquea = estado_bloquea_login($conexion, $nuevo);
    if ($id === $yo && $bloquea) $out(false, 'No puedes bloquear tu propia cuenta de administrador.');
    if ((int)$fila['id_rol'] === ROL_ADMIN && $bloquea) {
        $n = $conexion->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = " . ROL_ADMIN . " AND estado = 'activo' AND id_usuario <> $id")->fetch_row()[0];
        if ((int)$n < 1) $out(false, 'Debe quedar al menos un administrador activo.');
    }
    if ($nuevo !== 'activo') {
        $s = $conexion->prepare("SELECT COUNT(*) FROM tickets WHERE id_usuario_asignado = ? AND id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")");
        $s->bind_param("i", $id); $s->execute(); $n = (int)$s->get_result()->fetch_row()[0]; $s->close();
        if ($n > 0) $aviso = " Atención: tiene $n ticket(s) abiertos asignados; reasígnelos.";
    }
} elseif ($clave === 'equipos') {
    if ($nuevo === 'inactivo') {
        $s = $conexion->prepare("SELECT COUNT(*) FROM tickets WHERE id_inventario = ? AND id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")");
        $s->bind_param("i", $id); $s->execute(); $n = (int)$s->get_result()->fetch_row()[0]; $s->close();
        if ($n > 0) $aviso = " Atención: el equipo tiene $n ticket(s) abiertos.";
    }
} else {  // departamentos
    if ($nuevo !== 'activo') {
        $s = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE id_departamento = ? AND estado = 'activo'");
        $s->bind_param("i", $id); $s->execute(); $n = (int)$s->get_result()->fetch_row()[0]; $s->close();
        if ($n > 0) $aviso = " Atención: aún tiene $n usuario(s) activos asignados.";
    }
}

$u = $conexion->prepare("UPDATE `$tabla` SET estado = ? WHERE `$pk` = ?");
$u->bind_param("si", $nuevo, $id);
if (!$u->execute()) $out(false, 'No se pudo guardar: ' . $u->error);
$u->close();
if (function_exists('registrar_bitacora')) {
    try { registrar_bitacora($conexion, 'CAMBIO_ESTADO', $tabla, $id, "Estado de {$cat[$clave]['titulo']} #$id: $antes → $nuevo", ['estado' => $antes], ['estado' => $nuevo]); } catch (Throwable $e) {}
}
$out(true, 'Estado actualizado a «' . est_etiqueta($nuevo) . '».' . $aviso, ['estado' => $nuevo, 'color' => est_color($nuevo)]);

<?php
// Borrado LÓGICO: el usuario no se elimina de la BD (conserva tickets, historial y bitácora); pasa a estado 'inactivo'.
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) { die('Acceso denegado de forma segura.'); }
if (is_file('../../../includes/csrf.php')) { require_once '../../../includes/csrf.php'; if (function_exists('csrf_verificar')) csrf_verificar(); }
include('../../../config/conexion.php');
if (is_file('../../../includes/bitacora.php')) require_once '../../../includes/bitacora.php';

if (!isset($_POST['id'])) { echo 'No se recibió ningún identificador de usuario válido.'; exit(); }
$id = (int)$_POST['id'];
if (isset($_SESSION['id_usuario']) && $id === (int)$_SESSION['id_usuario']) { echo 'No puedes desactivar tu propia cuenta de administrador.'; exit(); }

$q = $conexion->prepare("SELECT id_rol, estado, CONCAT(nombres,' ',apellidos) n FROM usuarios WHERE id_usuario = ?");
$q->bind_param("i", $id); $q->execute(); $u = $q->get_result()->fetch_assoc(); $q->close();
if (!$u) { echo 'El usuario no existe.'; exit(); }
if ((int)$u['id_rol'] === 3 && $u['estado'] === 'activo') {
    $n = (int)$conexion->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 3 AND estado = 'activo' AND id_usuario <> $id")->fetch_row()[0];
    if ($n < 1) { echo 'Debe quedar al menos un administrador activo.'; exit(); }
}
if ($u['estado'] === 'inactivo') { echo 'success'; exit(); }

$s = $conexion->prepare("UPDATE usuarios SET estado = 'inactivo' WHERE id_usuario = ?");
$s->bind_param("i", $id);
if ($s->execute()) {
    if (function_exists('registrar_bitacora')) { try { registrar_bitacora($conexion, 'DESACTIVAR', 'usuarios', $id, "Usuario {$u['n']} desactivado (borrado lógico)", ['estado' => $u['estado']], ['estado' => 'inactivo']); } catch (Throwable $e) {} }
    echo 'success';
} else { echo 'Error en la Base de Datos: ' . htmlspecialchars($s->error); }
$s->close(); $conexion->close(); exit();

<?php
// Borrado LÓGICO de departamento: pasa a 'inactivo' (conserva el historial de tickets/equipos que lo referencian).
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) { die('<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Acceso denegado.</div>'); }
if (is_file('../../../includes/csrf.php')) { require_once '../../../includes/csrf.php'; if (function_exists('csrf_verificar')) csrf_verificar(); }
include('../../../config/conexion.php');
if (is_file('../../../includes/bitacora.php')) require_once '../../../includes/bitacora.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id === 0) die('<div class="alert alert-danger">ID no válido.</div>');

    $check = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE id_departamento = ? AND estado <> 'inactivo'");
    $check->bind_param("i", $id); $check->execute(); $n = (int)$check->get_result()->fetch_row()[0]; $check->close();
    if ($n > 0) die('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No se puede desactivar: hay ' . $n . ' usuario(s) activos asignados a este departamento.</div>');

    $u = $conexion->prepare("UPDATE departamentos SET estado = 'inactivo' WHERE id_departamento = ?");
    $u->bind_param("i", $id);
    if ($u->execute()) {
        if (function_exists('registrar_bitacora')) { try { registrar_bitacora($conexion, 'DESACTIVAR', 'departamentos', $id, "Departamento #$id desactivado (borrado lógico)", null, ['estado' => 'inactivo']); } catch (Throwable $e) {} }
        echo '<div class="alert alert-success"><i class="fas fa-eye-slash me-2"></i>Departamento desactivado (no se borra, conserva su historial).</div>
              <script>
                $("#depto-row-' . $id . '").fadeOut(400, function() { $(this).remove(); });
                setTimeout(function(){ $("#resultado-global").html(""); }, 3000);
              </script>';
    } else { echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($u->error) . '</div>'; }
    $u->close();
}
$conexion->close(); exit();

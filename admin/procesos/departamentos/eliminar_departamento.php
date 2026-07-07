<?php
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) {
    die('<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Acceso denegado.</div>');
}

include('../../../config/conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id === 0) {
        die('<div class="alert alert-danger">ID no válido.</div>');
    }

    // Validar si tiene usuarios asignados usando tu columna de llave foránea (id_departamento)
    $check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE id_departamento = ? LIMIT 1");
    $check->bind_param("i", $id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        die('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No se puede borrar: Existen usuarios asignados a este departamento.</div>');
    }
    $check->close();

    // Eliminar registro
    $delete_stmt = $conexion->prepare("DELETE FROM departamentos WHERE id_departamento = ?");
    $delete_stmt->bind_param("i", $id);

    if ($delete_stmt->execute()) {
        echo '<div class="alert alert-success"><i class="fas fa-trash-alt me-2"></i>Departamento eliminado con éxito.</div>
              <script>
                $("#depto-row-' . $id . '").fadeOut(400, function() { $(this).remove(); });
                setTimeout(function(){ $("#resultado-global").html(""); }, 3000);
              </script>';
    } else {
        echo '<div class="alert alert-danger">Error al borrar: ' . htmlspecialchars($delete_stmt->error) . '</div>';
    }

    $delete_stmt->close();
}
$conexion->close();
exit();
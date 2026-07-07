<?php
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) {
    die('<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Acceso denegado.</div>');
}

include('../../../config/conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    function filtrar(string $datos): string {
        return trim(stripslashes($datos));
    }

    $id          = (int)$_POST['id_departamento'];
    $nombre      = filtrar($_POST['nombre']);
    $descripcion = !empty(trim($_POST['descripcion'])) ? filtrar($_POST['descripcion']) : null;
    $estado      = filtrar($_POST['estado']);

    if (empty($nombre) || $id === 0) {
        die('<div class="alert alert-warning"><i class="fas fa-exclamation-circle me-2"></i>Datos insuficientes para actualizar.</div>');
    }

    // Validar duplicados exceptuando a sí mismo
    $check_stmt = $conexion->prepare("SELECT id_departamento FROM departamentos WHERE nombre = ? AND id_departamento != ?");
    $check_stmt->bind_param("si", $nombre, $id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $check_stmt->close();
        die('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>El nombre ya está asignado a otro departamento.</div>');
    }
    $check_stmt->close();

    // Actualización
    $update_stmt = $conexion->prepare("UPDATE departamentos SET nombre = ?, descripcion = ?, estado = ? WHERE id_departamento = ?");
    $update_stmt->bind_param("sssi", $nombre, $descripcion, $estado, $id);

    if ($update_stmt->execute()) {
        echo '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>¡Cambios guardados con éxito!</div>
              <script>setTimeout(function(){ location.reload(); }, 1200);</script>';
    } else {
        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($update_stmt->error) . '</div>';
    }

    $update_stmt->close();
}
$conexion->close();
exit();
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

    $nombre      = filtrar($_POST['nombre']);
    $descripcion = !empty(trim($_POST['descripcion'])) ? filtrar($_POST['descripcion']) : null;
    $estado      = filtrar($_POST['estado']);

    if (empty($nombre)) {
        die('<div class="alert alert-warning"><i class="fas fa-exclamation-circle me-2"></i>El nombre del departamento es requerido.</div>');
    }

    // Validar duplicados
    $check_stmt = $conexion->prepare("SELECT id_departamento FROM departamentos WHERE nombre = ?");
    $check_stmt->bind_param("s", $nombre);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $check_stmt->close();
        die('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Ya existe un departamento con ese nombre.</div>');
    }
    $check_stmt->close();

    // Insertar
    $insert_stmt = $conexion->prepare("INSERT INTO departamentos (nombre, descripcion, estado) VALUES (?, ?, ?)");
    
    if ($insert_stmt === false) {
        die('<div class="alert alert-danger">Error del sistema: ' . htmlspecialchars($conexion->error) . '</div>');
    }

    $insert_stmt->bind_param("sss", $nombre, $descripcion, $estado);

    if ($insert_stmt->execute()) {
        echo '<div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <div>¡Departamento registrado exitosamente! Ya puede salir de esta ventana.  </div>
              </div>
              <script>
                $("#form-crear")[0].reset();
                setTimeout(function(){ location.reload(); }, 1500);
              </script>';
    } else {
        echo '<div class="alert alert-danger">Error en el registro: ' . htmlspecialchars($insert_stmt->error) . '</div>';
    }

    $insert_stmt->close();
}
$conexion->close();
exit();
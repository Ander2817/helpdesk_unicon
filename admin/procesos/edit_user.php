<?php
// CORRECCIÓN 1: Faltaba verificar sesión
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) {
    die('<div class="alert alert-danger">Acceso denegado.</div>');
}

require_once('../../config/conexion.php');
require_once('../../includes/functions.php');
require_once('../../includes/constants.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // CORRECCIÓN 2: $depto, $rol e $id deben ser enteros, no strings
    $id           = (int)$_POST['id'];
    $name         = limpiar_inputs($_POST['name']);
    $last_name    = limpiar_inputs($_POST['lastname']);
    $email        = limpiar_inputs($_POST['email']);
    $user         = limpiar_inputs($_POST['user']);
    $phone_number = procesar_opcional($_POST['phone_number']); 
    $depto        = (int)$_POST['dpto'];
    $rol          = (int)$_POST['role'];
    $status       = limpiar_inputs($_POST['state']);

    $insert_stmt = $conexion->prepare("UPDATE usuarios 
                                       SET nombres = ?, apellidos = ?, correo = ?, usuario_login = ?, telefono = ?, id_departamento = ?, id_rol = ?, estado = ? 
                                       WHERE id_usuario = ?");

    if ($insert_stmt === false) {
        die('<div class="alert alert-danger">Error al preparar la consulta: ' . htmlspecialchars($conexion->error) . '</div>');
    }

    // CORRECCIÓN 3: Cadena correcta "ssssssiisi" — 5 strings, 2 ints, 1 string, 1 int
    $insert_stmt->bind_param("sssssiisi", $name, $last_name, $email, $user, $phone_number, $depto, $rol, $status, $id);

    if ($insert_stmt->execute()) {
    $insert_stmt->close();
    echo '<div class="alert alert-success d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            <div>¡Usuario actualizado correctamente!</div>
          </div>';
    exit();
    } else {
    echo '<div class="alert alert-danger d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div>Error al actualizar: ' . htmlspecialchars($insert_stmt->error) . '</div>
          </div>';
    $insert_stmt->close();
    }


}
?>
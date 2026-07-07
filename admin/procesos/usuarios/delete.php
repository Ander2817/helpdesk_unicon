<?php
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) {
    die('Acceso denegado de forma segura.');
}

include('../../../config/conexion.php');

if (isset($_POST['id'])) {
    $id_usuario = (int)$_POST['id'];

    // Evitar que el administrador se elimine a sí mismo
    if (isset($_SESSION['id_usuario']) && $id_usuario === (int)$_SESSION['id_usuario']) {
        die('No puedes eliminar tu propia cuenta de administrador.');
    }

    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    
    if ($stmt === false) {
        die('Error al preparar la consulta: ' . $conexion->error);
    }

    $stmt->bind_param("i", $id_usuario);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        // Código de error 1451: Restricción de clave foránea (Tiene tickets, inventario, etc.)
        if ($conexion->errno == 1451) {
            echo 'No se puede eliminar el usuario porque posee registros asociados en el sistema (Tickets, Inventario o historial activo). Se recomienda cambiar su estado a "Inactivo".';
        } else {
            echo 'Error en la Base de Datos: ' . htmlspecialchars($stmt->error);
        }
    }

    $stmt->close();
} else {
    echo 'No se recibió ningún identificador de usuario válido.';
}

$conexion->close();
exit();
?>
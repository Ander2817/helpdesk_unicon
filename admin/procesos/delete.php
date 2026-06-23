<?php
session_start();
// Validar sesión y privilegios de administrador corporativo
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) {
    die('Acceso denegado de forma segura.');
}

include('../../config/conexion.php');

if (isset($_POST['id'])) {
    $id_usuario = (int)$_POST['id'];

    // Evitar que el administrador se elimine a sí mismo por accidente
    // Asumiendo que guardas el ID del usuario en $_SESSION['id_usuario']
    if (isset($_SESSION['id_usuario']) && $id_usuario === (int)$_SESSION['id_usuario']) {
        die('No puedes eliminar tu propia cuenta de administrador.');
    }

    // Preparar la consulta usando el nombre exacto de tu columna: id_usuario
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    
    if ($stmt === false) {
        die('Error al preparar la consulta: ' . $conexion->error);
    }

    $stmt->bind_param("i", $id_usuario);

    if ($stmt->execute()) {
        // Si todo sale bien, devolvemos 'success' para que el JS dispare el modal verde
        echo 'success';
    } else {
        echo 'No se pudo eliminar el usuario de la base de datos: ' . $stmt->error;
    }

    $stmt->close();
} else {
    echo 'No se recibió ningún identificador de usuario válido.';
}

$conexion->close();
exit();
?>
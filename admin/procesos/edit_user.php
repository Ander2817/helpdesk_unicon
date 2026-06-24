<?php
require_once('../../config/conexion.php');
require_once('../../includes/functions.php');
// require_once('../../includes/constants.php'); // Descoméntalo si manejas constantes aquí

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Recolectamos y limpiamos las variables (Corregidos los signos y puntos y comas)
    $id           = limpiar_inputs($_POST['id']);
    $name         = limpiar_inputs($_POST['name']);
    $last_name    = limpiar_inputs($_POST['lastname']);
    $email        = limpiar_inputs($_POST['email']);
    $user         = limpiar_inputs($_POST['user']);
    $phone_number = procesar_opcional($_POST['phone_number']); 
    $depto        = limpiar_inputs($_POST['dpto']); // Ahora recibe el número ID del select
    $rol          = limpiar_inputs($_POST['role']); // Ahora recibe el número ID del select
    $status       = limpiar_inputs($_POST['state']);

    // 2. Preparamos la consulta limpia usando placeholders (?) protegiendo tu base de datos
    $insert_stmt = $conexion->prepare("UPDATE usuarios 
                                       SET nombres = ?, apellidos = ?, correo = ?, usuario_login = ?, telefono = ?, id_departamento = ?, id_rol = ?, estado = ? 
                                       WHERE id_usuario = ?");

    if ($insert_stmt === false) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    /* 3. Vinculamos los datos al orden de los signos '?'
       Tipos de datos: 
       s = string (texto)
       i = integer (número entero)
    */
    $insert_stmt->bind_param("sssssiisi", $name, $last_name, $email, $user, $phone_number, $depto, $rol, $status, $id);

    // 4. Ejecutamos la actualización
    if ($insert_stmt->execute()) {
        $insert_stmt->close();
        
        // Redirige al panel principal de usuarios (ajusta la ruta exacta si es necesario)
        header("Location: usuarios.php"); 
        exit();
    } else {
        echo "Error al actualizar el usuario: " . $insert_stmt->error;
    }

    $insert_stmt->close();
}
?>
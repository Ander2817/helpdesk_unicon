<?php
require_once('../config/conexion.php');
require_once('../includes/functions.php');
require_once('../includes/constants.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = limpiar_inputs($_POST['nombres']);
    $last_name = limpiar_inputs($_POST['apellidos']);
    $email = limpiar_inputs($_POST['correo']);
    $user = limpiar_inputs($_POST['usuario_login']);
    $phone_number = procesar_opcional($_POST['telefono']);
    $depto = limpiar_inputs($_POST['id_departamento']);
    $password = limpiar_inputs($_POST['contraseña']);
    $confirm_password = limpiar_inputs($_POST['confirm_contraseña']);

    if (strlen((string)$password) < 12) {
        set_alerta_flash('danger', 'Longitud de contraseña menor a 12. Ingrese una nueva contraseña nuevamente');
        redirigir('registro.php#registrarse');
        exit();
    }

    if ($password !== $confirm_password) {
        set_alerta_flash('danger', 'Las contraseñas no coinciden. Ingrese nuevamente su contraseña');
        redirigir('registro.php#registrarse');
        exit();
    }

    $crypted_password = (password_hash($password, PASSWORD_DEFAULT));


    $stmt = $conexion->prepare("SELECT correo, usuario_login FROM usuarios WHERE correo = ? OR usuario_login = ?");
    $stmt->bind_param("ss", $email, $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        set_alerta_flash('danger', 'El correo o usuario ya se encuentran registrados.');
        redirigir('registro.php#registrarse');
        exit();
    }

    $stmt->close();

    $insert_stmt = $conexion->prepare("INSERT INTO usuarios (nombres, apellidos, correo, usuario_login, contraseña, telefono, id_departamento) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("ssssssi", $name, $last_name, $email, $user, $crypted_password, $phone_number, $depto);

    if($insert_stmt->execute()) {
        $insert_stmt->close();
        set_alerta_flash('success', 'Usuario registrado exitosamente. Ya puedes iniciar sesion.');
        redirigir('../index.php');
    } else {
        $insert_stmt->close();
        set_alerta_flash('danger', 'Error al ingresar el usuario. Intente mas tarde.');
        redirigir('../index.php');
    }

    

} else {
    set_alerta_flash('info', 'Ingrese sesion previamente');
    redirigir('../index.php');
}

?>



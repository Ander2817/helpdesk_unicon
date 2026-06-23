<?php
session_start();
require_once('../config/conexion.php');
require_once('../includes/constants.php'); // Asegura que existan ROL_ADMIN y ROL_TECNICO
require_once('../includes/functions.php'); // Asegura que existan redirigir, sanear_entrada y set_alerta_flash

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Saneamos la entrada del usuario antes de buscar en la BD
    $usser   = limpiar_inputs($_POST['user_login']);
    $password = $_POST['password'];

    // Preparamos la consulta segura
    $stmt = $conexion->prepare("SELECT usuario_login, contraseña, id_rol FROM usuarios WHERE usuario_login = ?");
    $stmt->bind_param("s", $usser);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hash_password = $row['contraseña'];

        // Verificamos la contraseña encriptada
        if (password_verify($password, $hash_password)) {
            $_SESSION['usser']  = $usser;
            $_SESSION['id_rol'] = $row['id_rol'];
            $_SESSION['id_usuario'] = $row['id_usuario'];

            // Redirigir según rol usando la función centralizada
            if ($_SESSION['id_rol'] == ROL_ADMIN) {
                redirigir('../admin/dashboard.php');
            } elseif ($_SESSION['id_rol'] == ROL_TECNICO) {
                redirigir('../tecnico/dashboard.php');
            } else {
                redirigir('../usuario/dashboard.php');
            }

        } else {
            set_alerta_flash('danger', 'Credenciales incorrectas.');
            redirigir('../index.php');
        }
    } else {
        set_alerta_flash('danger', 'El usuario no existe.');
        redirigir('../index.php');
    }
    
} else {
    // Si intentan entrar escribiendo la URL directa en el navegador, los rebota al index
    redirigir('../index.php');
}
?>
<?php
session_start();
require_once('../config/conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $usser    = trim($_POST['user_login']);
    $password = $_POST['password'];

    $stmt = $conexion->prepare("SELECT usuario_login, contraseña, id_rol FROM usuarios WHERE usuario_login = ?");
    $stmt->bind_param("s", $usser);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hash_password = $row['contraseña'];

        if (password_verify($password, $hash_password)) {
            $_SESSION['usser']  = $usser;
            $_SESSION['id_rol'] = $row['id_rol'];

            // Redirigir según rol
            if ($_SESSION['id_rol'] == 3) {
                header("Location: ../admin/dashboard.php");
            } elseif ($_SESSION['id_rol'] == 2) {
                header("Location: ../tecnico/dashboard.php");
            } else {
                header("Location: ../usuario/dashboard.php");
            }
            exit();

        } else {
            header("Location: ../index.php?error=clave");
            exit();
        }
    } else {
        header("Location: ../index.php?error=usuario");
        exit();
    }

} else {
    header("Location: ../index.php");
    exit();
}
?>
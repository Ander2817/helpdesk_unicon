<?php
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) {
    die('<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Acceso denegado.</div>');
}

include('../../config/conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // CORRECCIÓN 1: función filtrar() movida fuera del if para evitar redeclaración fatal
    // y usando trim() sin htmlspecialchars (que va al mostrar, no al guardar)
    function filtrar(string $datos): string {
        return trim(stripslashes($datos));
    }

    $name         = filtrar($_POST['name']);
    $last_name    = filtrar($_POST['lastname']);
    $email        = filtrar($_POST['email']);
    $user         = filtrar($_POST['user']);
    $password     = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $phone_number = !empty(trim($_POST['phone_number'])) ? filtrar($_POST['phone_number']) : null; 
    $depto        = (int)$_POST['dpto']; 
    $rol          = (int)$_POST['role']; 
    $status       = filtrar($_POST['state']);
    
    if ($password !== $confirm_pass) {
        die('<div class="alert alert-warning"><i class="fas fa-exclamation-circle me-2"></i>Las contraseñas no coinciden.</div>');
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.;!@#$%^&*()_+\-=\[\]{}\':"\\|,.<>\/?]).{12,}$/', $password)) {
        die('<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>La contraseña no cumple con los requisitos de seguridad.</div>');
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $check_stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ? OR correo = ?");
    $check_stmt->bind_param("ss", $user, $email);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $check_stmt->close();
        die('<div class="alert alert-warning">El <strong>Usuario</strong> o el <strong>Correo</strong> ya existen.</div>');
    }
    $check_stmt->close();

    $insert_stmt = $conexion->prepare("INSERT INTO usuarios (nombres, apellidos, correo, usuario_login, contraseña, telefono, id_departamento, id_rol, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($insert_stmt === false) {
        die('<div class="alert alert-danger">Error del sistema: ' . htmlspecialchars($conexion->error) . '</div>');
    }

    $insert_stmt->bind_param("ssssssiis", $name, $last_name, $email, $user, $password_hash, $phone_number, $depto, $rol, $status);

    if ($insert_stmt->execute()) {
        echo '<div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <div>¡Usuario registrado exitosamente con contraseña segura!</div>
              </div>
              <script>$("#Form")[0].reset();</script>';
    } else {
        echo '<div class="alert alert-danger">Error en el registro: ' . htmlspecialchars($insert_stmt->error) . '</div>';
    }

    $insert_stmt->close();
}
$conexion->close();
exit();

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>HelpDesk Unicon - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <style>
        /* ========================================== */
        /* NUEVO: Fondo con Gradiente Premium         */
        /* ========================================== */
        body.login-page {
            /* Crea un degradado diagonal que va desde el azul corporativo a un tono más oscuro */
            background: linear-gradient(135deg, #1a365d 0%, #0f172a 100%) !important;
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            position: relative;
        }

        /* Estructura base para el grupo de inputs */
        .input-group .form-control,
        .input-group .input-group-text {
            border: 1px solid #ced4da;
            transition: all 0.2s ease-in-out;
        }

        /* Quitamos el borde derecho del campo de texto y el izquierdo del icono */
        .input-group .form-control {
            border-right: none !important;
        }
        .input-group .input-group-text {
            border-left: none !important;
            background-color: #fff !important;
        }

        /* EFECTO FOCUS: Sincronización perfecta */
        .input-group:focus-within .form-control,
        .input-group:focus-within .input-group-text {
            border-color: #80bdff !important;
        }

        /* Sombra de brillo unificada alrededor de toda la barra */
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            border-radius: 0.375rem;
        }

        /* Desactivamos el focus independiente de Bootstrap */
        .form-control:focus {
            box-shadow: none !important;
        }
    </style>
</head>
<body class="login-page">

<div class="position-absolute" style="top: 20px; right: 20px; z-index: 1000;">
    <img src="assets/img/logo_empresa.png" alt="Logo Unicon" class="img-fluid" style="max-height: 60px; width: auto;">
</div>

<div class="login-box">
    <!-- Hacemos que el texto del título sea blanco para que contraste con el nuevo fondo oscuro -->
    <div class="login-logo text-white">
        <b class="text-white">HelpDesk</b>
        <span class="texto-secundario text-white-50 fw-light">Unicon</span>
    </div>
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Inicia sesión</p>

            <div id="mensaje-error" class="alert alert-danger d-none"></div>

            <form id="form-login" action="auth/login.php" method="post">
                <!-- Input de Usuario -->
                <div class="input-group mb-3">
                    <input type="text" name="user_login" class="form-control" placeholder="Usuario">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-user"></i></div>
                    </div>
                </div>
                
                <!-- Input de Contraseña con Ojo -->
                <div class="input-group mb-3">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña">
                    <div class="input-group-append" onclick="togglePassword()" style="cursor: pointer;">
                        <div class="input-group-text">
                            <i id="eye-icon" class="fas fa-eye-slash"></i>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
            </form>
        </div>
    </div>
</div>

<!-- Hacemos el texto del footer más claro para que se lea perfectamente sobre el fondo azul oscuro -->
<footer class="text-center mt-4 text-white-50" style="font-size: 0.85rem;">
    <strong>Copyright &copy; 2026 <a href="#" class="text-white text-decoration-none fw-bold">Unicon</a>.</strong>
    <div class="d-inline ms-1">Todos los derechos reservados.</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    }
}
</script>
</body>
</html>
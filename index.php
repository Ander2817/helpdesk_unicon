<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>HelpDesk Unicon - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- NUEVO: Estilos para la micro-interacción (Efecto Focus) -->
    <style>
        /* Estilizamos el contenedor del input para que la transición sea suave */
        .input-group {
            border-radius: 0.375rem;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        /* Cuando el input de adentro esté activo (:focus-within), iluminamos todo el grupo */
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            border-color: #80bdff;
            border-radius: 0.375rem;
        }

        /* Quitamos el borde azul por defecto de Bootstrap en el input individual para que no se duplique */
        .input-group .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
        
        /* Mantenemos el borde limpio en el icono cuando hay focus */
        .input-group:focus-within .input-group-text {
            border-color: #80bdff;
        }
    </style>
</head>
<body class="login-page" style="min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; position: relative;">

<div class="position-absolute" style="top: 20px; right: 20px; z-index: 1000;">
    <img src="assets/img/logo_empresa.png" alt="Logo Unicon" class="img-fluid" style="max-height: 60px; width: auto;">
</div>

<div class="login-box">
    <div class="login-logo">
        <b>HelpDesk</b>
        <span class="texto-secundario">Unicon</span>
    </div>
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Inicia sesión</p>

            <div id="mensaje-error" class="alert alert-danger d-none"></div>

            <form id="form-login" action="auth/login.php" method="post">
                <div class="input-group mb-3">
                    <input type="text" name="user_login" class="form-control" placeholder="Usuario">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-user"></i></div>
                    </div>
                </div>
                
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

<footer class="text-center mt-4 text-secondary" style="font-size: 0.85rem;">
    <strong>Copyright &copy; 2026 <a href="#" class="text-decoration-none">Unicon</a>.</strong>
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
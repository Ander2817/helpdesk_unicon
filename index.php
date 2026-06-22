<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>HelpDesk Unicon - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="login-page">

    <!-- Logo superior derecho -->
    <div class="position-absolute" style="top: 20px; right: 30px; z-index: 1000;">
        <img src="assets/img/logo_empresa.png" alt="Logo Unicon" style="max-height: 55px; width: auto;">
    </div>

    <!-- Contenido principal -->
    <div class="login-wrapper">

        <!-- Título -->
        <h1 class="login-title">
            <span class="title-help">HelpDesk</span>
            <span class="title-unicon">Unicon</span>
        </h1>

        <!-- Tarjeta -->
        <div class="login-card">
            <div class="login-card-accent"></div>
            <div class="login-card-body">

                <p class="login-subtitle">Inicia sesión en tu cuenta</p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php
                            if ($_GET['error'] === 'clave')   echo '⚠️ Contraseña incorrecta.';
                            if ($_GET['error'] === 'usuario') echo '⚠️ Usuario no encontrado.';
                        ?>
                    </div>
                <?php else: ?>
                    <div id="mensaje-error" class="alert alert-danger d-none"></div>
                <?php endif; ?>

                <form id="form-login" action="auth/login.php" method="post">

                    <!-- Usuario -->
                    <div class="input-group mb-3">
                        <input type="text" id="username" name="user_login"
                               class="form-control" placeholder="Usuario" autocomplete="username">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>

                    <!-- Contraseña -->
                    <div class="input-group mb-2">
                        <input type="password" id="password" name="password"
                               class="form-control" placeholder="Contraseña" autocomplete="current-password">
                        <span class="input-group-text toggle-password" onclick="togglePassword()">
                            <i id="eye-icon" class="fas fa-eye-slash"></i>
                        </span>
                    </div>

                    <!-- Recordarme + Olvidaste contraseña -->
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recordarme" name="recordarme">
                            <label class="form-check-label text-muted small" for="recordarme">
                                Recordarme
                            </label>
                        </div>
                        <a href="auth/recuperar.php" class="link-naranja small">¿Olvidaste tu contraseña?</a>
                    </div>

                    <!-- Botón -->
                    <button type="submit" class="btn-ingresar w-100">
                        Ingresar
                    </button>

                    <!-- Registro -->
                    <p class="text-center mt-3 mb-0 small text-muted">
                        ¿No tienes una cuenta? 
                        <a href="auth/registro.php" class="link-naranja">Regístrate aquí</a>
                    </p>

                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <div>
                <h6 class="footer-titulo">SISTEMA</h6>
                <ul class="footer-lista">
                    <li>Inicio</li>
                    <li>Mis Tickets</li>
                    <li>Soporte</li>
                </ul>
            </div>
            <div>
                <h6 class="footer-titulo">EMPRESA</h6>
                <ul class="footer-lista">
                    <li>Industrias Unicon C.A.</li>
                    <li>ArcelorMittal</li>
                    <li>Zona Industrial La Chapa</li>
                    <li>La Victoria, Aragua</li>
                </ul>
            </div>
            <div>
                <h6 class="footer-titulo">CONTACTO</h6>
                <ul class="footer-lista">
                    <li>📧 soporte@unicon.com</li>
                    <li>📞 +58 (0243) 000-0000</li>
                    <li>🕐 Lun - Vie: 8:00am - 5:00pm</li>
                </ul>
            </div>
            <div>
                <h6 class="footer-titulo">HELPDESK UNICON</h6>
                <p class="footer-desc">
                    Plataforma de gestión de incidencias desarrollada
                    para el Departamento de Tecnología de la Información.
                </p>
            </div>
        </div>
        <hr class="footer-hr">
        <p class="footer-copy">
            Copyright © 2026 <span class="naranja">Unicon.</span> Todos los derechos reservados.
        </p>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('username').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('password').focus();
        }
    });

    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        }
    }
</script>
</body>
</html>
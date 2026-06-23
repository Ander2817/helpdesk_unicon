<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('includes/functions.php');
?>
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

    <div class="position-absolute" style="top: 20px; right: 30px; z-index: 1000;">
        <img src="assets/img/logo_empresa.png" alt="Logo Unicon" style="max-height: 55px; width: auto;">
    </div>

    <div class="login-wrapper">

        <h1 class="login-title">
            <span class="title-help">HelpDesk</span>
            <span class="title-unicon">Unicon</span>
        </h1>

        <div class="login-card">
            <div class="login-card-accent"></div>
            <div class="login-card-body">

                <p class="login-subtitle">Inicia sesión en tu cuenta</p>

                <div id="php-alert">
                    <?php echo mostrar_alerta_sistema(); ?>
                </div>

                <div id="mensaje-error" class="alert alert-danger d-none"></div>

                <form id="form-login" action="auth/login.php" method="post">

                    <div class="input-group mb-3">
                        <input type="text" id="username" name="user_login"
                               class="form-control" placeholder="Usuario" autocomplete="username">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>

                    <div class="input-group mb-2">
                        <input type="password" id="password" name="password"
                               class="form-control" placeholder="Contraseña" autocomplete="current-password">
                        <span class="input-group-text toggle-password" onclick="togglePassword()">
                            <i id="eye-icon" class="fas fa-eye-slash"></i>
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recordarme" name="recordarme">
                            <label class="form-check-label text-muted small" for="recordarme">
                                Recordarme
                            </label>
                        </div>
                        <a href="auth/recuperar.php" class="link-naranja small">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-ingresar w-100">
                        Ingresar
                    </button>

                    <p class="text-center mt-3 mb-0 small text-muted">
                        ¿No tienes una cuenta? 
                        <a href="auth/registro.php" class="link-naranja">Regístrate aquí</a>
                    </p>

                </form>
            </div>
        </div>
    </div>

    <footer>
    <div class="footer-grid" style="font-size: 1.05rem;">
        <div>
            <h6 class="footer-titulo mb-4">ACCESO</h6>
            <ul class="footer-lista list-unstyled p-0 m-0">
                <li class="mb-3">
                    <i class="fas fa-right-to-bracket me-2 naranja fs-5"></i>
                    <a href="../index.php" class="text-decoration-none text-white">Iniciar Sesión</a>
                </li>
                <li class="mb-3">
                    <i class="fas fa-user-plus me-2 naranja fs-5"></i>
                    <a href="auth/registro.php" class="text-decoration-none text-white">Crear Cuenta</a>
                </li>
                <li class="mb-3">
                    <i class="fas fa-key me-2 naranja fs-5"></i>
                    <a href="mailto:SoporteTecnicoSistemas@unicon.com.ve?subject=HelpDesk%20Unicon%20-%20Problemas%20para%20ingresar" class="text-decoration-none text-white">¿Problemas para ingresar?</a>
                </li>
            </ul>
        </div>

        <div>
            <h6 class="footer-titulo mb-4">EMPRESA</h6>
            <ul class="footer-lista text-white list-unstyled p-0 m-0">
                <li class="mb-1"><strong>Industrias Unicon C.A.</strong></li>
                <li class="mb-1 text-white">Filial de ArcelorMittal (Desde 2008)</li>
                <li class="mb-4 text-white">Fundada en 1959</li>
                
                <li class="mt-4 mb-1"><i class="fas fa-city me-2 naranja fs-5"></i><strong>Sede Principal:</strong></li>
                <li class="mb-1 text-white">Colinas de Bello Monte, Caracas</li>
                <li class="mb-4">
                    <a href="https://www.bing.com/maps/search?trk=org-locations_url&q=Av.+Beethoven%2C+Torre+Financiera.+Piso+9.++Colinas+de+Bello+Monte.+Caracas+1050+D.F.+VE&cp=10.485090%7E-66.876213&lvl=21&style=r" target="_blank" class="text-decoration-none naranja fw-bold">
                        <i class="fas fa-location-dot me-1"></i> Cómo llegar
                    </a>
                </li>
                
                <li class="mt-4 mb-1"><i class="fas fa-warehouse me-2 naranja fs-5"></i><strong>Planta # 02:</strong></li>
                <li class="mb-1 text-white">Zona Industrial La Chapa, La Victoria</li>
                <li class="mb-1">
                    <a href="https://www.bing.com/maps/search?trk=org-locations_url&q=2121+Planta+%23+02+La+Victoria+2121+Aragua+VE&cp=10.232945%7E-67.319962&lvl=16&style=r" target="_blank" class="text-decoration-none naranja fw-bold">
                        <i class="fas fa-location-dot me-1"></i> Cómo llegar
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <h6 class="footer-titulo mb-4">CONTACTO</h6>
            <ul class="footer-lista text-white list-unstyled p-0 m-0">
                <li class="mb-4">
                    <i class="fas fa-paper-plane me-2 naranja fs-5"></i>
                    <a href="mailto:SoporteTecnicoSistemas@unicon.com.ve" class="text-decoration-none text-white">SoporteTecnicoSistemas@unicon.com.ve</a>
                </li>
                <li class="mb-4">
                    <i class="fas fa-headset me-2 naranja fs-5"></i>
                    +58 (244) 400.4800 &nbsp;Ext 2386
                </li>
                <li class="mb-4">
                    <i class="fas fa-earth-americas me-2 naranja fs-5"></i>
                    <a href="http://www.unicon.com.ve" target="_blank" class="text-decoration-none naranja fw-bold">www.unicon.com.ve</a>
                </li>
                <li class="mt-4 text-white">
                    <i class="fas fa-business-time me-2 naranja fs-5"></i>
                    Lun - Vie: 8:00am - 4:30pm
                </li>
            </ul>
        </div>

        <div>
            <h6 class="footer-titulo mb-4">HELPDESK UNICON</h6>
            <p class="footer-desc text-white m-0" style="text-align: justify; text-justify: inter-word; line-height: 1.8;">
                Plataforma interna de gestión de incidencias e inventario, desarrollada exclusivamente para el Departamento de Tecnología de la Información.
            </p>
        </div>
    </div>
    
    <hr class="footer-hr my-4">
    <p class="footer-copy text-white text-center" style="font-size: 0.95rem;">
        Copyright © 2026 <span class="naranja fw-bold">Industrias Unicon C.A.</span> Todos los derechos reservados.
    </p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('form-login').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const mensajeError = document.getElementById('mensaje-error');
        const phpAlert = document.getElementById('php-alert');

        if (username === '' || password === '') {
            e.preventDefault(); 

            if (phpAlert) {
                phpAlert.innerHTML = ''; 
            }

            mensajeError.innerHTML = '⚠️ Introduzca sus datos.';
            mensajeError.classList.remove('d-none');
        }
    });

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
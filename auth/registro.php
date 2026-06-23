<?php
// Incluir archivo de conexion 
include_once ('../config/conexion.php'); 

if (!isset($conexion) || $conexion->connect_error) {
    $result_dept = false;
} else {
    $conexion->set_charset("utf8mb4");

    // Ejecutamos la consulta para traer los departamentos ordenados
    $sql = "SELECT id_departamento, nombre FROM departamentos ORDER BY nombre ASC";
    $result_dept = $conexion->query($sql);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpDesk Unicon - Registro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght=300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="login-page">

    <div class="position-absolute" style="top: 20px; right: 30px; z-index: 1000;">
        <img src="../assets/img/logo_empresa.png" alt="Logo Unicon" style="max-height: 55px; width: auto;">
    </div>

    <div class="login-wrapper" style="max-width: 460px; padding-top: 40px; padding-bottom: 40px;"> 
        <h1 class="login-title">
            <span class="title-help">HelpDesk</span>
            <span class="title-unicon">Unicon</span>
        </h1>

        <div class="login-card">
            <div class="login-card-accent"></div>
            <div class="login-card-body">
                <p class="login-subtitle">Crea una nueva cuenta en el sistema</p>

                <?php if (isset($_GET['error'])): // Manejo de errores corregido ?>
                    <div class="alert alert-danger py-2 small">
                        <?php
                            if ($_GET['error'] === 'vacio') echo '⚠️ Por favor, llena todos los campos obligatorios.';
                            if ($_GET['error'] === 'existe') echo '⚠️ El usuario o correo ya está registrado.';
                            if ($_GET['error'] === 'clave') echo '⚠️ Las contraseñas no coinciden.';
                            if ($_GET['error'] === 'db') echo '⚠️ Error interno del servidor. Intenta de nuevo.';
                        ?>
                    </div>
                <?php elseif (isset($_GET['success'])): ?>
                    <div class="alert alert-success py-2 small">
                        ✅ ¡Registro exitoso! Ya puedes <a href="../index.php" class="alert-link">iniciar sesión</a>.
                    </div>
                <?php endif; ?>

                <form id="form-registro" action="validar_registros.php" method="post">
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="input-group">
                                <input type="text" id="nombres" name="nombres" required class="form-control" placeholder="Nombres">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <input type="text" id="apellidos" name="apellidos" required class="form-control" placeholder="Apellidos">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="email" id="correo" name="correo" required class="form-control" placeholder="Correo electrónico">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>

                    <div class="input-group mb-3">
                        <input type="text" id="usuario_login" name="usuario_login" required class="form-control" placeholder="Nombre de usuario">
                        <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                    </div>

                    <div class="input-group mb-3">
                        <input type="tel" id="telefono" name="telefono" class="form-control" placeholder="Teléfono (Opcional)">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    </div>

                    <div class="input-group mb-3">
                        <select id="id_departamento" name="id_departamento" required class="form-select text-muted" style="font-size: 0.9rem; border-right: none !important;">
                            <option value="" disabled selected>Selecciona tu Departamento</option>
                            <?php  
                                if ($result_dept && $result_dept->num_rows > 0) {
                                    while ($dept = $result_dept->fetch_assoc()) {
                                        echo '<option value="' . $dept['id_departamento'] . '">';
                                        echo htmlspecialchars($dept['nombre']);
                                        echo '</option>';
                                    }
                                } else { 
                                    echo '<option value="1">Tecnología de Información</option>';
                                }
                            ?>
                        </select>
                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                    </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-bold text-muted">Contraseña</label>
                    <div class="input-group">
                        <input type="password" id="password" name="contraseña" class="form-control" required placeholder="Crea tu contraseña">
                        <span class="input-group-text toggle-password" onclick="togglePassword('password', 'eye-icon-pass')">
                            <i id="eye-icon-pass" class="fas fa-eye-slash"></i>
                        </span>
                    </div>
    
                    <div class="progress mt-2" style="height: 5px;">
                        <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                    </div>
    
                    <small id="password-feedback" class="text-muted d-block mt-1" style="font-size: 0.78rem;">
                        Mínimo 12 caracteres. Incluye mayúsculas, números y símbolos como: @, $, !, %, *, ?, &, ., ,.
                    </small>
                </div>

                <div class="input-group mb-4">
                    <input type="password" id="confirm_password" name="confirm_contraseña" required class="form-control" placeholder="Confirmar contraseña">
                    <span class="input-group-text toggle-password" onclick="togglePassword('confirm_password', 'eye-icon-confirm')">
                        <i id="eye-icon-confirm" class="fas fa-eye-slash"></i>
                    </span>
                </div>

                    <button type="submit" class="btn-ingresar w-100">Registrarse</button>

                    <p class="text-center mt-3 mb-0 small text-muted">
                        ¿Ya tienes una cuenta? <a href="../index.php" class="link-naranja">Inicia sesión aquí</a>
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
                        <a href="registro.php" class="text-decoration-none text-white">Crear Cuenta</a>
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
                        <a href="https://www.bing.com/maps/search?q=Av.+Beethoven%2C+Torre+Financiera.+Piso+9.++Colinas+de+Bello+Monte.+Caracas" target="_blank" class="text-decoration-none naranja fw-bold">
                            <i class="fas fa-location-dot me-1"></i> Cómo llegar
                        </a>
                    </li>
                    <li class="mt-4 mb-1"><i class="fas fa-warehouse me-2 naranja fs-5"></i><strong>Planta # 02:</strong></li>
                    <li class="mb-1 text-white">Zona Industrial La Chapa, La Victoria</li>
                    <li class="mb-1">
                        <a href="https://www.bing.com/maps/search?q=Planta+%23+02+La+Victoria+Aragua" target="_blank" class="text-decoration-none naranja fw-bold">
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
        
        const fields = ['nombres', 'apellidos', 'correo', 'usuario_login', 'telefono', 'id_departamento', 'password', 'confirm_password'];
        fields.forEach((fieldId, index) => {
            const currentField = document.getElementById(fieldId);
            if (currentField && index < fields.length - 1) {
                currentField.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const nextField = document.getElementById(fields[index + 1]);
                        if (nextField) nextField.focus();
                    }
                });
            }
        });

        // Función para alternar visibilidad de contraseñas
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                }
            }
        }

        // Script del medidor de fuerza
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const bar = document.getElementById('password-strength-bar');
            const feedback = document.getElementById('password-feedback');
        
            const hasMinLength = password.length >= 12;
            const hasUpper = /[A-Z]/.test(password);
            const hasNumber = /\d/.test(password);
            const hasSpecial = /[@$!%*?&.,]/.test(password);
        
            let strength = 0;
            if (hasMinLength) strength += 25;
            if (hasUpper) strength += 25;
            if (hasNumber) strength += 25;
            if (hasSpecial) strength += 25;

            bar.style.width = strength + '%';
        
            if (strength === 0) {
                bar.className = 'progress-bar';
                feedback.className = 'text-muted d-block mt-1';
                feedback.innerText = "Mínimo 12 caracteres, mayúsculas, números y símbolos.";
            } else if (strength < 50) {
                bar.className = 'progress-bar bg-danger';
                feedback.className = 'text-danger d-block mt-1 small';
                feedback.innerText = "Contraseña débil";
            } else if (strength < 100) {
                bar.className = 'progress-bar bg-warning';
                feedback.className = 'text-warning d-block mt-1 small';
                feedback.innerText = "Contraseña media";
            } else {
                bar.className = 'progress-bar bg-success';
                feedback.className = 'text-success d-block mt-1 small fw-bold';
                feedback.innerText = "Contraseña fuerte";
            }
        });
    </script>
</body>
</html>
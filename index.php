<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>HelpDesk Unicon - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600;700&display=swap" rel="stylesheet">
    
    
    <style>
    body.login-page {
        background: linear-gradient(135deg, #1a365d 0%, #0f172a 100%) !important;
        min-height: 100vh; 
        display: flex; 
        flex-direction: column; 
        justify-content: space-between;
        align-items: center; 
        position: relative;
    }

    .input-group .form-control,
    .input-group .input-group-text {
        border: 1px solid #ced4da;
        transition: all 0.2s ease-in-out;
    }

    .input-group .form-control {
        border-right: none !important;
    }

    .input-group .input-group-text {
        border-left: none !important;
        background-color: #fff !important;
    }

    .input-group:focus-within .form-control,
    .input-group:focus-within .input-group-text {
        border-color: #80bdff !important;
    }

    .input-group:focus-within {
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        border-radius: 0.375rem;
    }

    .form-control:focus {
        box-shadow: none !important;
    }
</style>

</head>
<body class="login-page">

<div class="position-absolute" style="top: 20px; right: 20px; z-index: 1000;">
    <img src="assets/img/logo_empresa.png" alt="Logo Unicon" class="img-fluid" style="max-height: 60px; width: auto;">
</div>

<div style="
    width: 100%;
    max-width: 400px;
    margin: 80px auto 20px auto;
    text-align: center;
">
    <h1 style="
        font-family: 'Poppins', sans-serif; 
        font-size: 2.2rem; 
        font-weight: 700; 
        letter-spacing: 2px;
        white-space: nowrap;
        margin-bottom: 25px;
    ">
        <span style="
            background: linear-gradient(90deg, #ffffff 0%, #E87722 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        ">HelpDesk</span>
        <span style="
            color: #adb5bd; 
            font-weight: 300;
            margin-left: 20px;
            -webkit-text-fill-color: #adb5bd;
        ">Unicon</span>
    </h1>

    <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.3);">
        <div class="card-body" style="padding: 35px;">
            <p style="color: #6c757d; margin-bottom: 25px;">Inicia sesión</p>

            <div id="mensaje-error" class="alert alert-danger d-none"></div>

            <form id="form-login" action="auth/login.php" method="post">
                <div class="input-group mb-3">
                    <input type="text" id="username" name="user_login" class="form-control" placeholder="Usuario">
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
                
                <button type="submit" class="btn btn-primary btn-block w-100" style="padding: 10px;">
                    Ingresar
                </button>
            </form>
        </div>
    </div>
</div>

<footer style="background-color: #0d1b2e; border-top: 3px solid #E87722; width: 100%; margin-top: auto;">
    <div class="container-fluid px-5 pt-3 pb-2">
        <div class="row text-white">

            <div class="col-md-3 mb-3">
                <h6 style="color: #E87722; font-weight: bold; letter-spacing: 3px; font-size: 0.8rem;">SISTEMA</h6>
                <ul class="list-unstyled" style="font-size: 0.8rem; color: #adb5bd; line-height: 1.8;">
                    <li>Inicio</li>
                    <li>Mis Tickets</li>
                    <li>Soporte</li>
                </ul>
            </div>

            <div class="col-md-3 mb-3">
                <h6 style="color: #E87722; font-weight: bold; letter-spacing: 3px; font-size: 0.8rem;">EMPRESA</h6>
                <ul class="list-unstyled" style="font-size: 0.8rem; color: #adb5bd; line-height: 1.8;">
                    <li>Industrias Unicon C.A.</li>
                    <li>ArcelorMittal</li>
                    <li>Zona Industrial La Chapa</li>
                    <li>La Victoria, Aragua</li>
                </ul>
            </div>

            <div class="col-md-3 mb-3">
                <h6 style="color: #E87722; font-weight: bold; letter-spacing: 3px; font-size: 0.8rem;">CONTACTO</h6>
                <ul class="list-unstyled" style="font-size: 0.8rem; color: #adb5bd; line-height: 1.8;">
                    <li>📧 soporte@unicon.com</li>
                    <li>📞 +58 (0243) 000-0000</li>
                    <li>🕐 Lun - Vie: 8:00am - 5:00pm</li>
                </ul>
            </div>

            <div class="col-md-3 mb-3">
                <h6 style="color: #E87722; font-weight: bold; letter-spacing: 3px; font-size: 0.8rem;">HELPDESK UNICON</h6>
                <p style="font-size: 0.8rem; color: #adb5bd; line-height: 1.6;">
                    Plataforma de gestión de incidencias<br>
                    desarrollada para el Departamento<br>
                    de Tecnología de la Información.
                </p>
            </div>

        </div>

        <hr style="border-color: #E87722; opacity: 0.4; margin: 6px 0 10px 0;">

        <div class="text-center" style="font-size: 0.75rem; color: #6c757d; padding-bottom: 5px;">
            Copyright © 2026 <span style="color: #E87722; font-weight: bold;">Unicon.</span> 
            Todos los derechos reservados.
        </div>
    </div>
</footer>

</body>
</html>
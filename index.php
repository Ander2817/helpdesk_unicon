<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>HelpDesk Unicon - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
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
                    <input type="password" name="password" class="form-control" placeholder="Contraseña">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-lock"></i></div>
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
</body>
</html>
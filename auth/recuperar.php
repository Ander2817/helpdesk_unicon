<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña — HelpDesk Unicon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body class="login-page">

    <div class="position-absolute" style="top: 20px; right: 30px; z-index: 1000;">
        <img src="../assets/img/logo_empresa.png" alt="Logo Unicon" style="max-height: 55px; width: auto;">
    </div>

    <div class="login-wrapper">
        <h1 class="login-title">
            <span class="title-help">HelpDesk</span>
            <span class="title-unicon">Unicon</span>
        </h1>

        <div class="login-card">
            <div class="login-card-accent"></div>
            <div class="login-card-body">

                <div style="text-align:center;margin-bottom:18px;">
                    <div style="width:52px;height:52px;background:rgba(232,119,34,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                        <i class="fas fa-key" style="color:#E87722;font-size:1.3rem;"></i>
                    </div>
                    <p class="login-subtitle" style="margin:0;">Recuperar Contraseña</p>
                    <p style="font-size:0.78rem;color:#64748b;margin-top:4px;">
                        Ingresa tu correo o usuario y te generaremos un enlace de recuperación.
                    </p>
                </div>

                <form id="form-recuperar">
                    <div class="input-group mb-3">
                        <input type="text" name="identificador" class="form-control"
                               placeholder="Correo o usuario de login" required>
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <div id="resultado" class="mb-3"></div>
                    <button type="submit" id="btn-enviar" class="btn-ingresar w-100">
                        <i class="fas fa-paper-plane"></i> Generar Enlace
                    </button>
                    <p class="text-center mt-3 mb-0 small text-muted">
                        <a href="../index.php" class="link-naranja">
                            <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$('#form-recuperar').on('submit', function(e) { //uso de ajax para recargar la oagina sola
    e.preventDefault();
    var btn = $('#btn-enviar');
    var resultado = $('#resultado');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
    $.ajax({
        url: '../procesos/solicitar_reset.php', // ad onve va el archivo
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            resultado.html(r);
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Generar Enlace');
        },
        error: function() { // si hay error
            resultado.html('<div class="alert alert-danger small">Error de conexión.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Generar Enlace');
        }
    });
});
</script>
</body>
</html>
<?php
// auth/resetear.php
include('../config/conexion.php');

$token = trim($_GET['token'] ?? ''); //agarra el token larguisimo y lo deja sin epacios balncos por sia caso

if (empty($token)) { //s laguien intenta entrar sin link l saca
    header("Location: recuperar.php"); exit(); 
}


// Secuencia para preparar la consulta de MySQL y hacerla más segura.
// Traemos los datos del token (tabla recuperaciones 'r') y el nombre de su dueño (tabla usuarios 'u')
// El INNER JOIN las une usando el id del usuario que tienen en común
// El signo '?' hace que busque únicamente el token exacto que le vamos a dar abajo
$stmt = $conexion->prepare("
    SELECT r.id, r.id_usuario, r.fecha_expiracion, r.usado, 
           u.nombres, u.usuario_login
    FROM recuperaciones r 
    INNER JOIN usuarios u ON r.id_usuario = u.id_usuario 
    WHERE r.token = ?
");

// el on hace que se conecte ambas tablas exactamente donde el id usuario sea igual en ambas ablas, con el fin de hacer que se asocie en lugar de mostrar por ejemplo usuario 5
$stmt->bind_param("s", $token);
$stmt->execute();
$datos = $stmt->get_result()->fetch_assoc();
$stmt->close();

$error = null;
if (!$datos) {
    $error = 'El enlace de recuperación no es válido.';
} elseif ($datos['usado']) {
    $error = 'Este enlace ya fue utilizado. Genera uno nuevo.';
} elseif (strtotime($datos['fecha_expiracion']) < time()) {
    $error = 'El enlace ha expirado. Genera uno nuevo.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña — HelpDesk Unicon</title>
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

                <?php if ($error): ?>
                    <div style="text-align:center;padding:10px 0;">
                        <div style="width:52px;height:52px;background:rgba(220,38,38,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <i class="fas fa-times-circle" style="color:#dc2626;font-size:1.4rem;"></i>
                        </div>
                        <div class="alert alert-danger small"><?= htmlspecialchars($error) ?></div>
                        <a href="recuperar.php" class="btn-ingresar d-block mt-3">
                            <i class="fas fa-redo"></i> Generar nuevo enlace
                        </a>
                    </div>

                <?php else: ?>
                    <div style="text-align:center;margin-bottom:18px;">
                        <div style="width:52px;height:52px;background:rgba(22,163,74,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                            <i class="fas fa-lock-open" style="color:#16a34a;font-size:1.3rem;"></i>
                        </div>
                        <p class="login-subtitle" style="margin:0;">Nueva Contraseña</p>
                        <p style="font-size:0.78rem;color:#64748b;margin-top:4px;">
                            Hola <strong><?= htmlspecialchars($datos['nombres']) ?></strong>, crea tu nueva contraseña.
                        </p>
                    </div>

                    <form id="form-reset">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" id="nueva_pass" name="nueva_pass"
                                       class="form-control" placeholder="Mínimo 12 caracteres" required>
                                <span class="input-group-text" style="cursor:pointer;" onclick="togglePass('nueva_pass','eye1')">
                                    <i id="eye1" class="fas fa-eye-slash"></i>
                                </span>
                            </div>
                            <div class="progress mt-2" style="height:4px;">
                                <div id="strength-bar" class="progress-bar" style="width:0%;transition:all 0.3s;"></div>
                            </div>
                            <small id="strength-msg" class="text-muted d-block mt-1" style="font-size:0.72rem;">
                                Min. 12 caracteres, mayúsculas, números y símbolos.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Confirmar Contraseña</label>
                            <div class="input-group">
                                <input type="password" id="confirm_pass" name="confirm_pass"
                                       class="form-control" placeholder="Repite la contraseña" required>
                                <span class="input-group-text" style="cursor:pointer;" onclick="togglePass('confirm_pass','eye2')">
                                    <i id="eye2" class="fas fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>

                        <div id="resultado" class="mb-3"></div>

                        <button type="submit" id="btn-reset" class="btn-ingresar w-100">
                            <i class="fas fa-save"></i> Guardar Nueva Contraseña
                        </button>

                        <p class="text-center mt-3 mb-0 small text-muted">
                            <a href="../index.php" class="link-naranja">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                        </p>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    }
}

document.getElementById('nueva_pass').addEventListener('input', function() {
    var p = this.value;
    var bar = document.getElementById('strength-bar');
    var msg = document.getElementById('strength-msg');
    var score = 0;
    if (p.length >= 12) score += 25;
    if (/[A-Z]/.test(p)) score += 25;
    if (/\d/.test(p)) score += 25;
    if (/[@$!%*?&.,;]/.test(p)) score += 25;
    bar.style.width = score + '%';
    if (score === 0) { bar.className = 'progress-bar'; msg.className = 'text-muted d-block mt-1'; msg.textContent = 'Min. 12 caracteres, mayusculas, numeros y simbolos.'; }
    else if (score < 50) { bar.className = 'progress-bar bg-danger'; msg.className = 'text-danger d-block mt-1 small'; msg.textContent = 'Debil'; }
    else if (score < 100) { bar.className = 'progress-bar bg-warning'; msg.className = 'text-warning d-block mt-1 small'; msg.textContent = 'Media'; }
    else { bar.className = 'progress-bar bg-success'; msg.className = 'text-success d-block mt-1 small fw-bold'; msg.textContent = 'Fuerte'; }
});

$('#form-reset').on('submit', function(e) {
    e.preventDefault();
    var pass = $('#nueva_pass').val();
    var confirm = $('#confirm_pass').val();
    var resultado = $('#resultado');
    var btn = $('#btn-reset');

    if (pass !== confirm) {
        resultado.html('<div class="alert alert-warning small">Las contraseñas no coinciden.</div>');
        return;
    }

    var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.,;]).{12,}$/;
    if (!regex.test(pass)) {
        resultado.html('<div class="alert alert-danger small">La contraseña no cumple los requisitos de seguridad.</div>');
        return;
    }

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: '../procesos/actualizar_clave.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            resultado.html(r);
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Nueva Contraseña');
            if (r.includes('alert-success')) {
                setTimeout(function() { window.location.href = '../index.php'; }, 2500);
            }
        },
        error: function() {
            resultado.html('<div class="alert alert-danger small">Error de conexion.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Nueva Contraseña');
        }
    });
});
</script>
</body>
</html>
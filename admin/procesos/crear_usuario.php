<?php
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) {
    header("Location: ../../index.php"); 
    exit();
}
include('../../config/conexion.php');

$query_deptos = $conexion->query("SELECT id_departamento, nombre FROM departamentos WHERE estado = 'activo'");
$query_roles  = $conexion->query("SELECT id_rol, nombre FROM roles");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario — HelpDesk Unicon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .page-wrapper { max-width: 700px; margin: 0 auto; padding: 24px 20px; }
        .hd-form-group { margin-bottom: 14px; }
        .hd-label { font-size: 0.78rem; font-weight: 600; color: var(--azul); margin-bottom: 5px; display: block; }
        .hd-input {
            width: 100%; padding: 8px 12px;
            border: 1px solid var(--borde); border-radius: 6px;
            font-family: 'Poppins', sans-serif; font-size: 0.82rem;
            color: var(--texto); background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .hd-input:focus { border-color: var(--naranja); box-shadow: 0 0 0 3px rgba(232,119,34,0.1); }
        .hd-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; }
        .hd-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        
        /* ESTILOS PARA EL CONTENEDOR DEL PASWORD Y EL OJITO */
        .password-container { position: relative; }
        .password-container .hd-input { padding-right: 40px; } /* Evita que el texto se monte sobre el ícono */
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .toggle-password:hover { color: var(--naranja); }
    </style>
</head>
<body>

<div class="page-wrapper">

    <div class="hd-page-header" style="margin-bottom:16px;">
        <div>
            <div class="hd-page-title"><i class="fas fa-user-plus" style="color:var(--naranja);margin-right:6px;"></i>Nuevo Usuario</div>
            <div class="hd-page-sub">Registrar una nueva cuenta en el sistema</div>
        </div>
        <a href="usuarios.php" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-list"></i> Datos de Registro</h5>
        </div>
        <div class="hd-card-body">
            <form id="Form" method="POST">

                <div class="hd-form-row">
                    <div class="hd-form-group">
                        <label class="hd-label">Nombres</label>
                        <input type="text" name="name" class="hd-input" placeholder="Ej. Juan" required>
                    </div>
                    <div class="hd-form-group">
                        <label class="hd-label">Apellidos</label>
                        <input type="text" name="lastname" class="hd-input" placeholder="Ej. Pérez" required>
                    </div>
                </div>

                <div class="hd-form-row">
                    <div class="hd-form-group">
                        <label class="hd-label">Correo Electrónico</label>
                        <input type="email" name="email" class="hd-input" placeholder="juan.perez@unicon.com" required>
                    </div>
                    <div class="hd-form-group">
                        <label class="hd-label">Usuario de Login</label>
                        <input type="text" name="user" class="hd-input" placeholder="Ej. jperez" required>
                    </div>
                </div>

                <div class="hd-form-row">
                    <div class="hd-form-group">
                        <label class="hd-label">Contraseña</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" class="hd-input" placeholder="Mínimo 12 caracteres" required>
                            <i class="fas fa-eye toggle-password" data-target="#password"></i>
                        </div>
                    </div>
                    <div class="hd-form-group">
                        <label class="hd-label">Confirmar Contraseña</label>
                        <div class="password-container">
                            <input type="password" id="confirm_password" name="confirm_password" class="hd-input" placeholder="Repite la contraseña" required>
                            <i class="fas fa-eye toggle-password" data-target="#confirm_password"></i>
                        </div>
                    </div>
                </div>

                <div class="hd-form-group">
                    <label class="hd-label">Teléfono <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                    <input type="text" name="phone_number" class="hd-input" placeholder="Ej. 04121234567">
                </div>

                <div class="hd-form-row">
                    <div class="hd-form-group">
                        <label class="hd-label">Departamento</label>
                        <select name="dpto" class="hd-input hd-select" required>
                            <option value="" disabled selected>Selecciona un departamento</option>
                            <?php while ($depto = $query_deptos->fetch_assoc()): ?>
                            <option value="<?= $depto['id_departamento'] ?>"><?= htmlspecialchars($depto['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="hd-form-group">
                        <label class="hd-label">Rol de Acceso</label>
                        <select name="role" class="hd-input hd-select" required>
                            <option value="" disabled selected>Selecciona un rol</option>
                            <?php while ($rol = $query_roles->fetch_assoc()): ?>
                            <option value="<?= $rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="hd-form-group">
                    <label class="hd-label">Estado Inicial</label>
                    <select name="state" class="hd-input hd-select">
                        <option value="activo" selected>Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="reposo">Reposo</option>
                        <option value="pasante">Pasante</option>
                    </select>
                </div>

                <div id="resultado" class="mt-3"></div>

                <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px;padding-top:14px;border-top:1px solid var(--borde);">
                    <a href="usuarios.php" class="hd-btn hd-btn-outline">Cancelar</a>
                    <button type="submit" id="send" class="hd-btn hd-btn-naranja">
                        <i class="fas fa-plus"></i> Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// LÓGICA PARA INTERCAMBIAR EL TIPO DE INPUT (VER / OCULTAR)
$(document).on('click', '.toggle-password', function() {
    var targetSelector = $(this).data('target');
    var inputField = $(targetSelector);
    
    if (inputField.attr('type') === 'password') {
        inputField.attr('type', 'text');
        $(this).removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        inputField.attr('type', 'password');
        $(this).removeClass('fa-eye-slash').addClass('fa-eye');
    }
});

// SUBMIT DEL FORMULARIO CON AJAX
$("#Form").on("submit", function(e) {
    e.preventDefault(); 

    var password = $("#password").val();
    var confirmPassword = $("#confirm_password").val();
    var btn = $("#send");
    var contenedorResultado = $("#resultado");

    if (password !== confirmPassword) {
        contenedorResultado.html('<div class="alert alert-warning"><i class="fas fa-exclamation-circle me-2"></i>Las contraseñas no coinciden.</div>');
        return false;
    }

    var regexSeguridad = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.;!@#$%^&*()_+\-=\[\]{}':"\\|,.<>\/?]).{12,}$/;

    if (!regexSeguridad.test(password)) {
        contenedorResultado.html('<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>La contraseña debe tener mínimo 12 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales (como puntos o puntos y comas).</div>');
        return false;
    }

    btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Registrando...');

    $.ajax({
        url : "../procesos/create_user.php", 
        type : "POST",
        data : $("#Form").serialize(), 
        success: function(resultado){
            contenedorResultado.html(resultado);
            btn.prop("disabled", false).html('<i class="fas fa-plus"></i> Crear Usuario');
        },
        error: function() {
            contenedorResultado.html('<div class="alert alert-danger">Error crítico de conexión.</div>');
            btn.prop("disabled", false).html('<i class="fas fa-plus"></i> Crear Usuario');
        }
    });
});
</script>
</body>
</html>
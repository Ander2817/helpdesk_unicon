<?php
// cambiar_clave.php — idéntico para los 3 roles. Se copia a admin/, tecnico/ y usuario/
session_start();
if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/constants.php';
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<style>
    .hd-clave-wrap { max-width: 560px; }
    .hd-label { font-size: 0.78rem; font-weight: 600; color: var(--azul); margin-bottom: 5px; display: block; }
    .hd-input {
        width: 100%; padding: 9px 40px 9px 12px; border: 1.5px solid var(--borde); border-radius: 7px;
        font-family: 'Poppins', sans-serif; font-size: 0.83rem; color: var(--texto); background: #fff;
    }
    .hd-input:focus { border-color: var(--naranja); box-shadow: 0 0 0 3px rgba(232,119,34,0.1); outline: none; }
    .hd-campo { position: relative; margin-bottom: 14px; }
    .hd-ojo {
        position: absolute; right: 12px; top: 34px; cursor: pointer; color: var(--muted); font-size: 0.85rem;
    }
    .hd-ojo:hover { color: var(--naranja); }
    .hd-requisitos { list-style: none; padding: 0; margin: 0 0 14px; font-size: 0.76rem; }
    .hd-requisitos li { padding: 3px 0; color: var(--muted); }
    .hd-requisitos li i { width: 16px; font-size: 0.7rem; }
    .hd-requisitos li.ok { color: var(--success); }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-key" style="color:var(--naranja);margin-right:6px;"></i> Cambiar contraseña</div>
            <div class="hd-page-sub">Elige una contraseña segura que no uses en otros sitios</div>
        </div>
        <a href="perfil.php" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-arrow-left"></i> Volver al perfil</a>
    </div>

    <div class="hd-card hd-clave-wrap">
        <div class="hd-card-body" style="padding:22px;">
            <form id="form-clave" autocomplete="off">

                <div class="hd-campo">
                    <label class="hd-label">Contraseña actual <span style="color:var(--danger);">*</span></label>
                    <input type="password" name="clave_actual" id="clave-actual" class="hd-input" required autocomplete="current-password">
                    <i class="fas fa-eye hd-ojo" data-target="clave-actual"></i>
                </div>

                <div class="hd-campo" style="margin-bottom:6px;">
                    <label class="hd-label">Nueva contraseña <span style="color:var(--danger);">*</span></label>
                    <input type="password" name="clave_nueva" id="clave-nueva" class="hd-input" required autocomplete="new-password">
                    <i class="fas fa-eye hd-ojo" data-target="clave-nueva"></i>
                </div>

                <ul class="hd-requisitos" id="requisitos">
                    <li data-req="largo"><i class="fas fa-circle"></i> Al menos <?= LONGITUD_MIN_PASS ?> caracteres</li>
                    <li data-req="mayus"><i class="fas fa-circle"></i> Una letra mayúscula</li>
                    <li data-req="minus"><i class="fas fa-circle"></i> Una letra minúscula</li>
                    <li data-req="numero"><i class="fas fa-circle"></i> Un número</li>
                    <li data-req="simbolo"><i class="fas fa-circle"></i> Un símbolo (por ejemplo ! @ # $ %)</li>
                </ul>

                <div class="hd-campo">
                    <label class="hd-label">Confirmar nueva contraseña <span style="color:var(--danger);">*</span></label>
                    <input type="password" name="clave_confirmar" id="clave-confirmar" class="hd-input" required autocomplete="new-password">
                    <i class="fas fa-eye hd-ojo" data-target="clave-confirmar"></i>
                </div>

                <div id="resultado-clave"></div>

                <div style="text-align:right;margin-top:6px;">
                    <button type="submit" id="btn-guardar-clave" class="hd-btn hd-btn-naranja">
                        <i class="fas fa-save"></i> Actualizar contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>

</main>

<script>
const MIN_LARGO = <?= (int)LONGITUD_MIN_PASS ?>;

// Mostrar / ocultar contraseña
$('.hd-ojo').on('click', function() {
    const input = document.getElementById(this.dataset.target);
    const visible = input.type === 'text';
    input.type = visible ? 'password' : 'text';
    $(this).toggleClass('fa-eye', visible).toggleClass('fa-eye-slash', !visible);
});

// Verificador en vivo de requisitos
$('#clave-nueva').on('input', function() {
    const v = this.value;
    const checks = {
        largo:   v.length >= MIN_LARGO,
        mayus:   /[A-Z]/.test(v),
        minus:   /[a-z]/.test(v),
        numero:  /[0-9]/.test(v),
        simbolo: /[^A-Za-z0-9]/.test(v)
    };
    $('#requisitos li').each(function() {
        const ok = checks[this.dataset.req];
        $(this).toggleClass('ok', ok);
        $(this).find('i').attr('class', ok ? 'fas fa-check-circle' : 'fas fa-circle');
    });
});

$('#form-clave').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btn-guardar-clave');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: '../procesos/cambiar_clave_propia.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            $('#resultado-clave').html(r);
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar contraseña');
            if (r.includes('alert-success')) {
                $('#form-clave')[0].reset();
                $('#requisitos li').removeClass('ok').find('i').attr('class', 'fas fa-circle');
            }
        },
        error: function() {
            $('#resultado-clave').html('<div class="alert alert-danger">Error de conexión. Intenta de nuevo.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar contraseña');
        }
    });
});
</script>

<?php include_once '../includes/footer2.php'; ?>

<?php
// perfil.php — idéntico para los 3 roles. Se copia a admin/, tecnico/ y usuario/
session_start();
if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/constants.php';
include '../config/conexion.php';

$stmt = $conexion->prepare("
    SELECT u.nombres, u.apellidos, u.correo, u.usuario_login, u.telefono, u.estado,
           d.nombre AS departamento_nombre, r.nombre AS rol_nombre
    FROM usuarios u
    JOIN departamentos d ON u.id_departamento = d.id_departamento
    JOIN roles r ON u.id_rol = r.id_rol
    WHERE u.usuario_login = ?
");
$stmt->bind_param("s", $_SESSION['usser']);
$stmt->execute();
$perfil = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$perfil) {
    header("Location: ../auth/logout.php"); exit();
}

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<style>
    .hd-perfil-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 18px; align-items: start; }
    @media (max-width: 900px) { .hd-perfil-grid { grid-template-columns: 1fr; } }
    .hd-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 600px) { .hd-row-2 { grid-template-columns: 1fr; } }
    .hd-label { font-size: 0.78rem; font-weight: 600; color: var(--azul); margin-bottom: 5px; display: block; }
    .hd-input {
        width: 100%; padding: 9px 12px; border: 1.5px solid var(--borde); border-radius: 7px;
        font-family: 'Poppins', sans-serif; font-size: 0.83rem; color: var(--texto); background: #fff;
    }
    .hd-input:focus { border-color: var(--naranja); box-shadow: 0 0 0 3px rgba(232,119,34,0.1); outline: none; }
    .hd-input:disabled { background: #f1f5f9; color: var(--muted); cursor: not-allowed; }
    .hd-avatar-grande {
        width: 72px; height: 72px; border-radius: 50%; background: var(--naranja); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem; font-weight: 700; margin: 0 auto 10px;
    }
    .hd-cuenta-item { display: flex; justify-content: space-between; font-size: 0.8rem; padding: 9px 0; border-bottom: 1px solid var(--borde); }
    .hd-cuenta-item:last-child { border-bottom: none; }
    .hd-cuenta-item span:first-child { color: var(--muted); }
    .hd-cuenta-item span:last-child { color: var(--texto); font-weight: 600; text-align: right; }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-user-edit" style="color:var(--naranja);margin-right:6px;"></i> Mi Perfil</div>
            <div class="hd-page-sub">Actualiza tus datos de contacto</div>
        </div>
        <a href="cambiar_clave.php" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-key"></i> Cambiar contraseña</a>
    </div>

    <div class="hd-perfil-grid">

        <!-- FORMULARIO -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-id-card"></i> Datos personales</h5>
            </div>
            <div class="hd-card-body" style="padding:20px 22px;">
                <form id="form-perfil">
                    <div class="hd-row-2" style="margin-bottom:14px;">
                        <div>
                            <label class="hd-label">Nombres <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="nombres" class="hd-input" maxlength="100" required value="<?= htmlspecialchars($perfil['nombres']) ?>">
                        </div>
                        <div>
                            <label class="hd-label">Apellidos <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="apellidos" class="hd-input" maxlength="100" required value="<?= htmlspecialchars($perfil['apellidos']) ?>">
                        </div>
                    </div>

                    <div class="hd-row-2" style="margin-bottom:14px;">
                        <div>
                            <label class="hd-label">Correo electrónico <span style="color:var(--danger);">*</span></label>
                            <input type="email" name="correo" class="hd-input" maxlength="150" required value="<?= htmlspecialchars($perfil['correo']) ?>">
                        </div>
                        <div>
                            <label class="hd-label">Teléfono</label>
                            <input type="text" name="telefono" class="hd-input" maxlength="20" placeholder="Opcional" value="<?= htmlspecialchars($perfil['telefono'] ?? '') ?>">
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label class="hd-label">Usuario de acceso</label>
                        <input type="text" class="hd-input" disabled value="<?= htmlspecialchars($perfil['usuario_login']) ?>">
                        <div style="font-size:0.7rem;color:var(--muted);margin-top:4px;">El usuario de acceso no se puede modificar.</div>
                    </div>

                    <div id="resultado-perfil"></div>

                    <div style="text-align:right;margin-top:12px;">
                        <button type="submit" id="btn-guardar-perfil" class="hd-btn hd-btn-naranja">
                            <i class="fas fa-save"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- INFO DE CUENTA -->
        <div class="hd-card" style="padding:20px 22px;">
            <div class="hd-avatar-grande"><?= strtoupper(substr($perfil['nombres'], 0, 1)) ?></div>
            <div style="text-align:center;margin-bottom:14px;">
                <div style="font-weight:700;color:var(--azul);"><?= htmlspecialchars($perfil['nombres'] . ' ' . $perfil['apellidos']) ?></div>
                <div style="font-size:0.75rem;color:var(--muted);">@<?= htmlspecialchars($perfil['usuario_login']) ?></div>
            </div>
            <div class="hd-cuenta-item"><span>Rol</span><span><?= htmlspecialchars($perfil['rol_nombre']) ?></span></div>
            <div class="hd-cuenta-item"><span>Departamento</span><span><?= htmlspecialchars($perfil['departamento_nombre']) ?></span></div>
            <div class="hd-cuenta-item"><span>Estado</span><span><?= htmlspecialchars(ucfirst($perfil['estado'])) ?></span></div>
            <div style="font-size:0.7rem;color:var(--muted);margin-top:12px;line-height:1.5;">
                El rol y el departamento los asigna un administrador.
            </div>
        </div>

    </div>

</main>

<script>
$('#form-perfil').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btn-guardar-perfil');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: '../procesos/actualizar_perfil.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            $('#resultado-perfil').html(r);
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar cambios');
            if (r.includes('alert-success')) setTimeout(() => location.reload(), 1200);
        },
        error: function() {
            $('#resultado-perfil').html('<div class="alert alert-danger">Error de conexión. Intenta de nuevo.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar cambios');
        }
    });
});
</script>

<?php include_once '../includes/footer2.php'; ?>

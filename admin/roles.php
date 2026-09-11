<?php
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/functions.php';
include '../config/conexion.php';

$roles = $conexion->query("SELECT id_rol, nombre, descripcion, nivel_acceso FROM roles ORDER BY nivel_acceso ASC");

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .hd-badge-rol {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600;
    }
    .hd-badge-rol .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .hd-table td.acciones { white-space: nowrap; width: 1%; }
    .hd-icon-btn {
        width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--borde);
        background: #fff; color: var(--muted); display: inline-flex; align-items: center;
        justify-content: center; cursor: pointer; font-size: 0.75rem; transition: all 0.15s;
    }
    .hd-icon-btn:hover { border-color: var(--naranja); color: var(--naranja); }
    .modal-header { border-bottom: 1px solid var(--borde); }
    .modal-title { font-size: 0.95rem; font-weight: 700; color: var(--azul); }
    .hd-label { font-size: 0.78rem; font-weight: 600; color: var(--azul); margin-bottom: 5px; display: block; }
    .hd-input {
        width: 100%; padding: 9px 12px; border: 1.5px solid var(--borde); border-radius: 7px;
        font-family: 'Poppins', sans-serif; font-size: 0.83rem; color: var(--texto); background: #fff;
    }
    .hd-input:focus { border-color: var(--naranja); box-shadow: 0 0 0 3px rgba(232,119,34,0.1); outline: none; }
    textarea.hd-input { resize: vertical; min-height: 70px; }
    .hd-input:disabled { background: #f1f5f9; color: var(--muted); }
    .hd-info-fija {
        display: flex; gap: 10px; align-items: flex-start; background: #f8fafc;
        border: 1px solid var(--borde); border-radius: 8px; padding: 12px 14px;
        font-size: 0.76rem; color: var(--muted); margin-bottom: 14px; line-height: 1.5;
    }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-shield-alt" style="color:var(--naranja);margin-right:6px;"></i> Roles</div>
            <div class="hd-page-sub">Definen los permisos de acceso al sistema</div>
        </div>
    </div>

    <?= mostrar_alerta_sistema() ?>

    <div class="hd-info-fija">
        <i class="fas fa-lock" style="color:var(--muted);margin-top:2px;"></i>
        <div>
            El sistema tiene la lógica de permisos (menús, páginas visibles, acciones permitidas) programada directamente
            para estos 3 roles y sus IDs exactos. Por eso no se pueden crear ni eliminar roles desde aquí — solo ajustar
            el nombre visible, la descripción o el nivel de acceso.
        </div>
    </div>

    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-list"></i> Listado de Roles</h5>
        </div>
        <div class="hd-card-body" style="padding:0;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Descripción</th>
                        <th>Nivel de acceso</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $colores = [1=>'#0284c7', 2=>'#d97706', 3=>'#E87722'];
                    while ($r = $roles->fetch_assoc()):
                        $color = $colores[$r['id_rol']] ?? '#94a3b8';
                    ?>
                    <tr>
                        <td>
                            <span class="hd-badge-rol" style="background:color-mix(in srgb, <?= $color ?> 12%, white); color:<?= $color ?>;">
                                <span class="dot" style="background:<?= $color ?>;"></span> <?= htmlspecialchars($r['nombre']) ?>
                            </span>
                        </td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($r['descripcion'] ?? '—') ?></td>
                        <td style="color:var(--muted);">Nivel <?= (int)$r['nivel_acceso'] ?></td>
                        <td class="acciones">
                            <button class="hd-icon-btn" title="Editar"
                                onclick='modoEditar(<?= json_encode([
                                    "id" => $r["id_rol"],
                                    "nombre" => $r["nombre"],
                                    "descripcion" => $r["descripcion"],
                                    "nivel_acceso" => $r["nivel_acceso"]
                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                <i class="fas fa-pen"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- ===== MODAL EDITAR ROL ===== -->
<div class="modal fade" id="modalRol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-rol">
                <div class="modal-body">
                    <input type="hidden" name="id_rol" id="rol-id">

                    <div class="mb-3">
                        <label class="hd-label">Nombre <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="nombre" id="rol-nombre" class="hd-input" maxlength="50" required>
                    </div>

                    <div class="mb-3">
                        <label class="hd-label">Descripción</label>
                        <textarea name="descripcion" id="rol-descripcion" class="hd-input" maxlength="255" placeholder="Opcional"></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="hd-label">Nivel de acceso <span style="color:var(--danger);">*</span></label>
                        <input type="number" name="nivel_acceso" id="rol-nivel" class="hd-input" min="1" max="9" required>
                        <div style="font-size:0.7rem;color:var(--muted);margin-top:4px;">
                            Solo informativo por ahora — el sistema usa el ID del rol, no este número, para decidir permisos.
                        </div>
                    </div>

                    <div id="resultado-rol"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="hd-btn hd-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-guardar-rol" class="hd-btn hd-btn-naranja">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function modoEditar(r) {
    $('#rol-id').val(r.id);
    $('#rol-nombre').val(r.nombre);
    $('#rol-descripcion').val(r.descripcion || '');
    $('#rol-nivel').val(r.nivel_acceso);
    $('#resultado-rol').html('');
    new bootstrap.Modal(document.getElementById('modalRol')).show();
}

$('#form-rol').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btn-guardar-rol');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: '../procesos/edit_rol.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            $('#resultado-rol').html(r);
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            if (r.includes('alert-success')) setTimeout(() => location.reload(), 1000);
        },
        error: function() {
            $('#resultado-rol').html('<div class="alert alert-danger">Error de conexión. Intenta de nuevo.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        }
    });
});
</script>

<?php include_once '../includes/footer2.php'; ?>

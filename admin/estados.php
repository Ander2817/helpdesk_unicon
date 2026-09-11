<?php
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/functions.php';
include '../config/conexion.php';

$estados = $conexion->query("SELECT id_estado_ticket, nombre, descripcion FROM estados_ticket ORDER BY id_estado_ticket ASC");

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .hd-badge-estado {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600;
    }
    .hd-badge-estado .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
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
    .hd-info-fija {
        display: flex; gap: 10px; align-items: flex-start; background: #f8fafc;
        border: 1px solid var(--borde); border-radius: 8px; padding: 12px 14px;
        font-size: 0.76rem; color: var(--muted); margin-bottom: 14px; line-height: 1.5;
    }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-toggle-on" style="color:var(--naranja);margin-right:6px;"></i> Estados de Ticket</div>
            <div class="hd-page-sub">Definen el flujo de vida de un ticket. Solo se puede editar el nombre y la descripción.</div>
        </div>
    </div>

    <?= mostrar_alerta_sistema() ?>

    <div class="hd-info-fija">
        <i class="fas fa-lock" style="color:var(--muted);margin-top:2px;"></i>
        <div>
            Estos 5 estados son parte del flujo interno del sistema (Abierto → En Proceso → Pendiente → Resuelto → Cerrado)
            y varios procesos dependen de sus IDs exactos. Por eso no se pueden crear ni eliminar desde aquí — solo ajustar
            el nombre visible o la descripción.
        </div>
    </div>

    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-list"></i> Listado de Estados</h5>
        </div>
        <div class="hd-card-body" style="padding:0;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $colores = [1=>'#0284c7', 2=>'#E87722', 3=>'#d97706', 4=>'#16a34a', 5=>'#64748b'];
                    while ($e = $estados->fetch_assoc()):
                        $color = $colores[$e['id_estado_ticket']] ?? '#94a3b8';
                    ?>
                    <tr>
                        <td style="color:var(--muted);">#<?= $e['id_estado_ticket'] ?></td>
                        <td>
                            <span class="hd-badge-estado" style="background:color-mix(in srgb, <?= $color ?> 12%, white); color:<?= $color ?>;">
                                <span class="dot" style="background:<?= $color ?>;"></span> <?= htmlspecialchars($e['nombre']) ?>
                            </span>
                        </td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($e['descripcion'] ?? '—') ?></td>
                        <td class="acciones">
                            <button class="hd-icon-btn" title="Editar"
                                onclick='modoEditar(<?= json_encode([
                                    "id" => $e["id_estado_ticket"],
                                    "nombre" => $e["nombre"],
                                    "descripcion" => $e["descripcion"]
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

<!-- ===== MODAL EDITAR ESTADO ===== -->
<div class="modal fade" id="modalEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-estado">
                <div class="modal-body">
                    <input type="hidden" name="id_estado_ticket" id="est-id">

                    <div class="mb-3">
                        <label class="hd-label">Nombre <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="nombre" id="est-nombre" class="hd-input" maxlength="50" required>
                    </div>

                    <div class="mb-2">
                        <label class="hd-label">Descripción</label>
                        <textarea name="descripcion" id="est-descripcion" class="hd-input" maxlength="255" placeholder="Opcional"></textarea>
                    </div>

                    <div id="resultado-estado"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="hd-btn hd-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-guardar-estado" class="hd-btn hd-btn-naranja">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function modoEditar(e) {
    $('#est-id').val(e.id);
    $('#est-nombre').val(e.nombre);
    $('#est-descripcion').val(e.descripcion || '');
    $('#resultado-estado').html('');
    new bootstrap.Modal(document.getElementById('modalEstado')).show();
}

$('#form-estado').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btn-guardar-estado');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: '../procesos/edit_estado.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            $('#resultado-estado').html(r);
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            if (r.includes('alert-success')) setTimeout(() => location.reload(), 1000);
        },
        error: function() {
            $('#resultado-estado').html('<div class="alert alert-danger">Error de conexión. Intenta de nuevo.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        }
    });
});
</script>

<?php include_once '../includes/footer2.php'; ?>

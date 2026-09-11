<?php
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/functions.php';
include '../config/conexion.php';

$prioridades = $conexion->query("SELECT id_prioridad, nombre, nivel, tiempo_respuesta_objetivo FROM prioridades ORDER BY nivel ASC");

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .hd-badge-nivel {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600;
    }
    .hd-badge-nivel .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
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
    .hd-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-flag" style="color:var(--naranja);margin-right:6px;"></i> Prioridades</div>
            <div class="hd-page-sub">Definen el tiempo de respuesta objetivo y se asignan automáticamente según la categoría del ticket</div>
        </div>
        <button class="hd-btn hd-btn-naranja" data-bs-toggle="modal" data-bs-target="#modalPrioridad" onclick="modoCrear()">
            <i class="fas fa-plus"></i> Nueva Prioridad
        </button>
    </div>

    <?= mostrar_alerta_sistema() ?>

    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-list"></i> Listado de Prioridades</h5>
        </div>
        <div class="hd-card-body" style="padding:0;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Nivel</th>
                        <th>Tiempo de respuesta objetivo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($prioridades->num_rows === 0): ?>
                    <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--muted);">Aún no hay prioridades registradas.</td></tr>
                    <?php endif; ?>
                    <?php
                    $niveles_color = [1=>'#0284c7', 2=>'#d97706', 3=>'#E87722', 4=>'#dc2626'];
                    while ($p = $prioridades->fetch_assoc()):
                        $color = $niveles_color[$p['nivel']] ?? '#94a3b8';
                        $h = intdiv($p['tiempo_respuesta_objetivo'], 60);
                        $m = $p['tiempo_respuesta_objetivo'] % 60;
                        $texto_tiempo = trim(($h > 0 ? "{$h}h " : '') . ($m > 0 ? "{$m}m" : ''));
                        if ($texto_tiempo === '') $texto_tiempo = '0m';
                    ?>
                    <tr>
                        <td style="font-weight:600;color:var(--azul);"><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><span class="hd-badge-nivel" style="background:color-mix(in srgb, <?= $color ?> 12%, white); color:<?= $color ?>;"><span class="dot" style="background:<?= $color ?>;"></span> Nivel <?= (int)$p['nivel'] ?></span></td>
                        <td><i class="fas fa-clock" style="color:var(--muted);margin-right:5px;font-size:0.7rem;"></i><?= $texto_tiempo ?></td>
                        <td class="acciones">
                            <button class="hd-icon-btn" title="Editar"
                                onclick='modoEditar(<?= json_encode([
                                    "id" => $p["id_prioridad"],
                                    "nombre" => $p["nombre"],
                                    "nivel" => $p["nivel"],
                                    "horas" => $h,
                                    "minutos" => $m
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

<!-- ===== MODAL CREAR / EDITAR PRIORIDAD ===== -->
<div class="modal fade" id="modalPrioridad" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPrioridadTitulo">Nueva Prioridad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-prioridad">
                <div class="modal-body">
                    <input type="hidden" name="id_prioridad" id="prio-id">

                    <div class="mb-3">
                        <label class="hd-label">Nombre <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="nombre" id="prio-nombre" class="hd-input" maxlength="50" required>
                    </div>

                    <div class="mb-3">
                        <label class="hd-label">Nivel <span style="color:var(--danger);">*</span></label>
                        <select name="nivel" id="prio-nivel" class="hd-input" required>
                            <option value="1">1 — Baja</option>
                            <option value="2">2 — Media</option>
                            <option value="3">3 — Alta</option>
                            <option value="4">4 — Crítica</option>
                        </select>
                        <div style="font-size:0.7rem;color:var(--muted);margin-top:4px;">
                            Se usa para ordenar y para el estilo visual del formulario de tickets. Manten 4 niveles para que se vea bien.
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="hd-label">Tiempo de respuesta objetivo <span style="color:var(--danger);">*</span></label>
                        <div class="hd-row-2">
                            <div>
                                <input type="number" name="horas" id="prio-horas" class="hd-input" min="0" max="999" placeholder="Horas" required>
                            </div>
                            <div>
                                <input type="number" name="minutos" id="prio-minutos" class="hd-input" min="0" max="59" placeholder="Minutos" required>
                            </div>
                        </div>
                    </div>

                    <div id="resultado-prioridad"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="hd-btn hd-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-guardar-prioridad" class="hd-btn hd-btn-naranja">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function modoCrear() {
    $('#form-prioridad')[0].reset();
    $('#prio-id').val('');
    $('#prio-horas').val(0);
    $('#prio-minutos').val(0);
    $('#modalPrioridadTitulo').text('Nueva Prioridad');
    $('#resultado-prioridad').html('');
}

function modoEditar(p) {
    $('#prio-id').val(p.id);
    $('#prio-nombre').val(p.nombre);
    $('#prio-nivel').val(p.nivel);
    $('#prio-horas').val(p.horas);
    $('#prio-minutos').val(p.minutos);
    $('#modalPrioridadTitulo').text('Editar Prioridad');
    $('#resultado-prioridad').html('');
    new bootstrap.Modal(document.getElementById('modalPrioridad')).show();
}

$('#form-prioridad').on('submit', function(e) {
    e.preventDefault();
    const esEdicion = $('#prio-id').val() !== '';
    const url = esEdicion ? '../procesos/edit_prioridad.php' : '../procesos/create_prioridad.php';
    const btn = $('#btn-guardar-prioridad');

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            $('#resultado-prioridad').html(r);
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            if (r.includes('alert-success')) {
                setTimeout(() => location.reload(), 1000);
            }
        },
        error: function() {
            $('#resultado-prioridad').html('<div class="alert alert-danger">Error de conexión. Intenta de nuevo.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        }
    });
});
</script>

<?php include_once '../includes/footer2.php'; ?>

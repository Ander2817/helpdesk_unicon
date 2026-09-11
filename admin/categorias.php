<?php
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/functions.php';
include '../config/conexion.php';

// Categorías + nombre de su prioridad por defecto
$categorias = $conexion->query("
    SELECT c.id_categoria, c.nombre, c.descripcion, c.estado,
           p.id_prioridad AS id_prioridad_default, p.nombre AS prioridad_nombre, p.nivel AS prioridad_nivel
    FROM categoria_incidencias c
    LEFT JOIN prioridades p ON c.id_prioridad_default = p.id_prioridad
    ORDER BY c.nombre ASC
");

// Prioridades para el <select> del modal
$prioridades_opts = $conexion->query("SELECT id_prioridad, nombre FROM prioridades ORDER BY nivel ASC");

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<!-- jQuery + SweetAlert2 (esta página los necesita, header2 no los carga) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .hd-badge {
        display: inline-block; padding: 3px 10px; border-radius: 20px;
        font-size: 0.68rem; font-weight: 600;
    }
    .hd-badge.activo   { background: rgba(22,163,74,0.12);  color: var(--success); }
    .hd-badge.inactivo { background: rgba(100,116,139,0.12); color: var(--muted); }
    .hd-badge-prioridad {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 0.72rem; font-weight: 500; color: var(--muted);
    }
    .hd-badge-prioridad .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .hd-badge-prioridad.sin { font-style: italic; color: #cbd5e1; }
    .hd-table td.acciones { white-space: nowrap; width: 1%; }
    .hd-icon-btn {
        width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--borde);
        background: #fff; color: var(--muted); display: inline-flex; align-items: center;
        justify-content: center; cursor: pointer; font-size: 0.75rem; transition: all 0.15s;
    }
    .hd-icon-btn:hover { border-color: var(--naranja); color: var(--naranja); }
    .hd-icon-btn.danger:hover { border-color: var(--danger); color: var(--danger); }
    .modal-header { border-bottom: 1px solid var(--borde); }
    .modal-title { font-size: 0.95rem; font-weight: 700; color: var(--azul); }
    .hd-label { font-size: 0.78rem; font-weight: 600; color: var(--azul); margin-bottom: 5px; display: block; }
    .hd-input {
        width: 100%; padding: 9px 12px; border: 1.5px solid var(--borde); border-radius: 7px;
        font-family: 'Poppins', sans-serif; font-size: 0.83rem; color: var(--texto); background: #fff;
    }
    .hd-input:focus { border-color: var(--naranja); box-shadow: 0 0 0 3px rgba(232,119,34,0.1); outline: none; }
    textarea.hd-input { resize: vertical; min-height: 70px; }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-tags" style="color:var(--naranja);margin-right:6px;"></i> Categorías de Incidencias</div>
            <div class="hd-page-sub">Cada categoría define la prioridad que se asignará automáticamente al crear un ticket</div>
        </div>
        <button class="hd-btn hd-btn-naranja" data-bs-toggle="modal" data-bs-target="#modalCategoria" onclick="modoCrear()">
            <i class="fas fa-plus"></i> Nueva Categoría
        </button>
    </div>

    <?= mostrar_alerta_sistema() ?>

    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-list"></i> Listado de Categorías</h5>
        </div>
        <div class="hd-card-body" style="padding:0;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Prioridad por defecto</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody-categorias">
                    <?php if ($categorias->num_rows === 0): ?>
                    <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--muted);">Aún no hay categorías registradas.</td></tr>
                    <?php endif; ?>
                    <?php while ($c = $categorias->fetch_assoc()):
                        $niveles_color = [1=>'#0284c7', 2=>'#d97706', 3=>'#E87722', 4=>'#dc2626'];
                        $color = $niveles_color[$c['prioridad_nivel']] ?? '#94a3b8';
                    ?>
                    <tr id="fila-cat-<?= $c['id_categoria'] ?>">
                        <td style="font-weight:600;color:var(--azul);"><?= htmlspecialchars($c['nombre']) ?></td>
                        <td style="color:var(--muted);max-width:320px;"><?= htmlspecialchars($c['descripcion'] ?? '—') ?></td>
                        <td>
                            <?php if ($c['prioridad_nombre']): ?>
                            <span class="hd-badge-prioridad"><span class="dot" style="background:<?= $color ?>;"></span> <?= htmlspecialchars($c['prioridad_nombre']) ?></span>
                            <?php else: ?>
                            <span class="hd-badge-prioridad sin">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="hd-badge <?= $c['estado'] ?>"><?= ucfirst($c['estado']) ?></span></td>
                        <td class="acciones">
                            <button class="hd-icon-btn" title="Editar"
                                onclick='modoEditar(<?= json_encode([
                                    "id" => $c["id_categoria"],
                                    "nombre" => $c["nombre"],
                                    "descripcion" => $c["descripcion"],
                                    "id_prioridad_default" => $c["id_prioridad_default"]
                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="hd-icon-btn <?= $c['estado']=='activo'?'danger':'' ?>"
                                title="<?= $c['estado']=='activo' ? 'Desactivar' : 'Activar' ?>"
                                onclick="toggleCategoria(<?= $c['id_categoria'] ?>, '<?= htmlspecialchars($c['nombre'], ENT_QUOTES) ?>', '<?= $c['estado'] ?>')">
                                <i class="fas fa-toggle-<?= $c['estado']=='activo' ? 'on' : 'off' ?>"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- ===== MODAL CREAR / EDITAR CATEGORÍA ===== -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCategoriaTitulo">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-categoria">
                <div class="modal-body">
                    <input type="hidden" name="id_categoria" id="cat-id">

                    <div class="mb-3">
                        <label class="hd-label">Nombre <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="nombre" id="cat-nombre" class="hd-input" maxlength="80" required>
                    </div>

                    <div class="mb-3">
                        <label class="hd-label">Descripción</label>
                        <textarea name="descripcion" id="cat-descripcion" class="hd-input" maxlength="500" placeholder="Opcional"></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="hd-label">Prioridad por defecto</label>
                        <select name="id_prioridad_default" id="cat-prioridad" class="hd-input">
                            <option value="">— Sin prioridad por defecto —</option>
                            <?php while ($p = $prioridades_opts->fetch_assoc()): ?>
                            <option value="<?= $p['id_prioridad'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div style="font-size:0.7rem;color:var(--muted);margin-top:4px;">
                            Se asignará automáticamente a los tickets creados con esta categoría. El técnico/admin podrá ajustarla después si es necesario.
                        </div>
                    </div>

                    <div id="resultado-categoria"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="hd-btn hd-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-guardar-categoria" class="hd-btn hd-btn-naranja">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function modoCrear() {
    $('#form-categoria')[0].reset();
    $('#cat-id').val('');
    $('#modalCategoriaTitulo').text('Nueva Categoría');
    $('#resultado-categoria').html('');
}

function modoEditar(cat) {
    $('#cat-id').val(cat.id);
    $('#cat-nombre').val(cat.nombre);
    $('#cat-descripcion').val(cat.descripcion || '');
    $('#cat-prioridad').val(cat.id_prioridad_default || '');
    $('#modalCategoriaTitulo').text('Editar Categoría');
    $('#resultado-categoria').html('');
    new bootstrap.Modal(document.getElementById('modalCategoria')).show();
}

$('#form-categoria').on('submit', function(e) {
    e.preventDefault();
    const esEdicion = $('#cat-id').val() !== '';
    const url = esEdicion ? '../procesos/edit_categoria.php' : '../procesos/create_categoria.php';
    const btn = $('#btn-guardar-categoria');

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            $('#resultado-categoria').html(r);
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            if (r.includes('alert-success')) {
                setTimeout(() => location.reload(), 1000);
            }
        },
        error: function() {
            $('#resultado-categoria').html('<div class="alert alert-danger">Error de conexión. Intenta de nuevo.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        }
    });
});

function toggleCategoria(id, nombre, estadoActual) {
    const accion = estadoActual === 'activo' ? 'desactivar' : 'activar';
    Swal.fire({
        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} "${nombre}"?`,
        text: accion === 'desactivar' ? 'No aparecerá al crear nuevos tickets.' : 'Volverá a estar disponible al crear tickets.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E87722',
        cancelButtonColor: '#64748b',
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../procesos/toggle_categoria.php', { id_categoria: id }, function() {
                location.reload();
            }).fail(function() {
                Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
            });
        }
    });
}
</script>

<?php include_once '../includes/footer2.php'; ?>

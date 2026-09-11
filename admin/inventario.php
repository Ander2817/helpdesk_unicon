<?php
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/functions.php';
include '../config/conexion.php';

$inventario = $conexion->query("
    SELECT i.id_inventario, i.codigo_equipo, i.tipo_equipo, i.marca, i.modelo, i.serial,
           i.estado, i.ubicacion, i.id_departamento, d.nombre AS departamento_nombre
    FROM inventario_tecnologico i
    LEFT JOIN departamentos d ON i.id_departamento = d.id_departamento
    ORDER BY i.codigo_equipo ASC
");

$departamentos_opts = $conexion->query("SELECT id_departamento, nombre FROM departamentos ORDER BY nombre ASC");

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .hd-badge {
        display: inline-block; padding: 3px 10px; border-radius: 20px;
        font-size: 0.68rem; font-weight: 600;
    }
    .hd-badge.activo        { background: rgba(22,163,74,0.12);  color: var(--success); }
    .hd-badge.inactivo      { background: rgba(100,116,139,0.12); color: var(--muted); }
    .hd-badge.mantenimiento { background: rgba(217,119,6,0.12);  color: #d97706; }
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
    .hd-filtros { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
    .hd-filtro-btn {
        padding: 6px 14px; border-radius: 20px; border: 1.5px solid var(--borde);
        background: #fff; font-size: 0.76rem; font-weight: 500; color: var(--muted);
        cursor: pointer; transition: all 0.15s;
    }
    .hd-filtro-btn.activo { border-color: var(--naranja); color: var(--naranja); background: rgba(232,119,34,0.06); }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-server" style="color:var(--naranja);margin-right:6px;"></i> Inventario Tecnológico</div>
            <div class="hd-page-sub">Equipos registrados por departamento — se usan como referencia opcional al crear tickets</div>
        </div>
        <button class="hd-btn hd-btn-naranja" data-bs-toggle="modal" data-bs-target="#modalInventario" onclick="modoCrear()">
            <i class="fas fa-plus"></i> Nuevo Equipo
        </button>
    </div>

    <?= mostrar_alerta_sistema() ?>

    <div class="hd-filtros">
        <button class="hd-filtro-btn activo" data-filtro="todos" onclick="filtrar('todos', this)">Todos</button>
        <button class="hd-filtro-btn" data-filtro="activo" onclick="filtrar('activo', this)">Activos</button>
        <button class="hd-filtro-btn" data-filtro="mantenimiento" onclick="filtrar('mantenimiento', this)">En Mantenimiento</button>
        <button class="hd-filtro-btn" data-filtro="inactivo" onclick="filtrar('inactivo', this)">Inactivos</button>
    </div>

    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-list"></i> Listado de Equipos</h5>
        </div>
        <div class="hd-card-body" style="padding:0;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Marca / Modelo</th>
                        <th>Serial</th>
                        <th>Departamento</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody-inventario">
                    <?php if ($inventario->num_rows === 0): ?>
                    <tr><td colspan="8" style="text-align:center;padding:20px;color:var(--muted);">Aún no hay equipos registrados.</td></tr>
                    <?php endif; ?>
                    <?php while ($eq = $inventario->fetch_assoc()): ?>
                    <tr data-estado="<?= $eq['estado'] ?>">
                        <td style="font-weight:600;color:var(--azul);"><?= htmlspecialchars($eq['codigo_equipo']) ?></td>
                        <td><?= htmlspecialchars($eq['tipo_equipo']) ?></td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($eq['marca']) ?> <?= htmlspecialchars($eq['modelo']) ?></td>
                        <td style="color:var(--muted);font-family:monospace;font-size:0.78rem;"><?= htmlspecialchars($eq['serial']) ?></td>
                        <td><?= htmlspecialchars($eq['departamento_nombre'] ?? '—') ?></td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($eq['ubicacion'] ?? '—') ?></td>
                        <td><span class="hd-badge <?= $eq['estado'] ?>"><?= ucfirst($eq['estado']) ?></span></td>
                        <td class="acciones">
                            <button class="hd-icon-btn" title="Editar"
                                onclick='modoEditar(<?= json_encode([
                                    "id" => $eq["id_inventario"],
                                    "codigo_equipo" => $eq["codigo_equipo"],
                                    "tipo_equipo" => $eq["tipo_equipo"],
                                    "marca" => $eq["marca"],
                                    "modelo" => $eq["modelo"],
                                    "serial" => $eq["serial"],
                                    "estado" => $eq["estado"],
                                    "ubicacion" => $eq["ubicacion"],
                                    "id_departamento" => $eq["id_departamento"]
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

<!-- ===== MODAL CREAR / EDITAR EQUIPO ===== -->
<div class="modal fade" id="modalInventario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInventarioTitulo">Nuevo Equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-inventario">
                <div class="modal-body">
                    <input type="hidden" name="id_inventario" id="inv-id">

                    <div class="hd-row-2 mb-3">
                        <div>
                            <label class="hd-label">Código de equipo <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="codigo_equipo" id="inv-codigo" class="hd-input" maxlength="30" placeholder="Ej. PC-CONT-004" required>
                        </div>
                        <div>
                            <label class="hd-label">Tipo de equipo <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="tipo_equipo" id="inv-tipo" class="hd-input" maxlength="80" placeholder="Ej. PC de escritorio, Impresora, Laptop" required>
                        </div>
                    </div>

                    <div class="hd-row-2 mb-3">
                        <div>
                            <label class="hd-label">Marca <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="marca" id="inv-marca" class="hd-input" maxlength="80" required>
                        </div>
                        <div>
                            <label class="hd-label">Modelo <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="modelo" id="inv-modelo" class="hd-input" maxlength="80" required>
                        </div>
                    </div>

                    <div class="hd-row-2 mb-3">
                        <div>
                            <label class="hd-label">Serial <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="serial" id="inv-serial" class="hd-input" maxlength="100" required>
                        </div>
                        <div>
                            <label class="hd-label">Departamento <span style="color:var(--danger);">*</span></label>
                            <select name="id_departamento" id="inv-departamento" class="hd-input hd-select" required>
                                <option value="" disabled selected>Selecciona un departamento</option>
                                <?php $departamentos_opts->data_seek(0); while ($d = $departamentos_opts->fetch_assoc()): ?>
                                <option value="<?= $d['id_departamento'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="hd-row-2 mb-2">
                        <div>
                            <label class="hd-label">Ubicación</label>
                            <input type="text" name="ubicacion" id="inv-ubicacion" class="hd-input" maxlength="150" placeholder="Ej. Piso 2, oficina de Contabilidad">
                        </div>
                        <div>
                            <label class="hd-label">Estado <span style="color:var(--danger);">*</span></label>
                            <select name="estado" id="inv-estado" class="hd-input hd-select" required>
                                <option value="activo">Activo</option>
                                <option value="mantenimiento">En Mantenimiento</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div id="resultado-inventario"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="hd-btn hd-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-guardar-inventario" class="hd-btn hd-btn-naranja">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function modoCrear() {
    $('#form-inventario')[0].reset();
    $('#inv-id').val('');
    $('#inv-estado').val('activo');
    $('#modalInventarioTitulo').text('Nuevo Equipo');
    $('#resultado-inventario').html('');
}

function modoEditar(eq) {
    $('#inv-id').val(eq.id);
    $('#inv-codigo').val(eq.codigo_equipo);
    $('#inv-tipo').val(eq.tipo_equipo);
    $('#inv-marca').val(eq.marca);
    $('#inv-modelo').val(eq.modelo);
    $('#inv-serial').val(eq.serial);
    $('#inv-departamento').val(eq.id_departamento);
    $('#inv-ubicacion').val(eq.ubicacion || '');
    $('#inv-estado').val(eq.estado);
    $('#modalInventarioTitulo').text('Editar Equipo');
    $('#resultado-inventario').html('');
    new bootstrap.Modal(document.getElementById('modalInventario')).show();
}

$('#form-inventario').on('submit', function(e) {
    e.preventDefault();
    const esEdicion = $('#inv-id').val() !== '';
    const url = esEdicion ? '../procesos/edit_inventario.php' : '../procesos/create_inventario.php';
    const btn = $('#btn-guardar-inventario');

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            $('#resultado-inventario').html(r);
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            if (r.includes('alert-success')) setTimeout(() => location.reload(), 1000);
        },
        error: function() {
            $('#resultado-inventario').html('<div class="alert alert-danger">Error de conexión. Intenta de nuevo.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        }
    });
});

function filtrar(estado, btn) {
    document.querySelectorAll('.hd-filtro-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.querySelectorAll('#tbody-inventario tr[data-estado]').forEach(fila => {
        fila.style.display = (estado === 'todos' || fila.dataset.estado === estado) ? '' : 'none';
    });
}
</script>

<?php include_once '../includes/footer2.php'; ?>

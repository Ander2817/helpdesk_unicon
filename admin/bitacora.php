<?php
// admin/bitacora.php — Bitácora de acciones (solo Administrador)
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/functions.php';
include '../config/conexion.php';

if (empty($_SESSION['csrf_bitacora'])) $_SESSION['csrf_bitacora'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf_bitacora'];

// Valores para los filtros
$acciones = $conexion->query("SELECT DISTINCT accion FROM bitacora ORDER BY accion")->fetch_all(MYSQLI_NUM);
$tablas   = $conexion->query("SELECT DISTINCT tabla_afectada FROM bitacora WHERE tabla_afectada IS NOT NULL ORDER BY tabla_afectada")->fetch_all(MYSQLI_NUM);

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<style>
    .hd-bit-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: end; padding: 12px 14px; border-bottom: 1px solid var(--borde); }
    .hd-bit-filtros label { font-size: 0.72rem; font-weight: 600; color: var(--azul); display: block; margin-bottom: 3px; }
    .hd-bit-filtros .form-control, .hd-bit-filtros .form-select { font-size: 0.78rem; min-width: 150px; }
    .hd-acc { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 700; letter-spacing: .3px; }
    .acc-INSERT  { background: #dcfce7; color: #15803d; }
    .acc-UPDATE  { background: #fef3c7; color: #b45309; }
    .acc-DELETE  { background: #fee2e2; color: #b91c1c; }
    .acc-LOGIN, .acc-LOGOUT { background: #e0f2fe; color: #0369a1; }
    .acc-BACKUP, .acc-EXPORT, .acc-IMPORT, .acc-RESTORE { background: #ede9fe; color: #6d28d9; }
    .acc-otro    { background: #f1f5f9; color: #475569; }
    #tablaBitacora_wrapper { padding: 12px 14px; font-size: 0.78rem; }
    #tablaBitacora td, #tablaBitacora th { font-size: 0.78rem; vertical-align: middle; }
    pre.hd-json { background: #0f172a; color: #e2e8f0; border-radius: 8px; padding: 12px; font-size: 0.72rem; max-height: 280px; overflow: auto; margin: 0; white-space: pre-wrap; word-break: break-word; }
    .hd-json-titulo { font-size: 0.74rem; font-weight: 700; color: var(--azul); margin-bottom: 4px; }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-clipboard-list" style="color:var(--naranja);margin-right:6px;"></i> Bitácora de acciones</div>
            <div class="hd-page-sub">Registro de los cambios realizados en el sistema y en la base de datos</div>
        </div>
    </div>

    <?= mostrar_alerta_sistema() ?>

    <div class="hd-card">
        <div class="hd-bit-filtros">
            <div>
                <label for="f_accion">Acción</label>
                <select id="f_accion" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($acciones as $a): ?>
                        <option value="<?= htmlspecialchars($a[0]) ?>"><?= htmlspecialchars($a[0]) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="f_tabla">Tabla</label>
                <select id="f_tabla" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($tablas as $t): ?>
                        <option value="<?= htmlspecialchars($t[0]) ?>"><?= htmlspecialchars($t[0]) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="f_desde">Desde</label>
                <input type="date" id="f_desde" class="form-control form-control-sm">
            </div>
            <div>
                <label for="f_hasta">Hasta</label>
                <input type="date" id="f_hasta" class="form-control form-control-sm">
            </div>
            <div>
                <button type="button" id="btnLimpiar" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-eraser"></i> Limpiar</button>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="#" id="btnCsv" class="hd-btn hd-btn-primary hd-btn-sm"><i class="fas fa-file-csv"></i> Exportar CSV</a>
                <button type="button" class="hd-btn hd-btn-outline hd-btn-sm" data-bs-toggle="modal" data-bs-target="#modalDepurar"><i class="fas fa-broom"></i> Depurar antiguos</button>
            </div>
        </div>

        <div class="hd-card-body" style="padding:0;">
            <table id="tablaBitacora" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Tabla</th>
                        <th>Descripción</th>
                        <th>IP</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</main>

<!-- ===== MODAL DEPURAR ===== -->
<div class="modal fade" id="modalDepurar" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="procesos/bitacora_depurar.php">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-broom" style="color:var(--naranja)"></i> Depurar bitácora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                <p style="font-size:0.82rem;">Elimina de forma <strong>permanente</strong> los registros más antiguos que el periodo elegido.
                   Las restauraciones y las propias depuraciones <strong>nunca</strong> se borran. Se recomienda <em>Exportar CSV</em> antes.</p>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;">Borrar registros con más de</label>
                    <select name="dias" class="form-select" required>
                        <option value="90">3 meses (90 días)</option>
                        <option value="180">6 meses (180 días)</option>
                        <option value="365" selected>1 año (365 días)</option>
                        <option value="730">2 años (730 días)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;">Tu contraseña de administrador</label>
                    <input type="password" name="password" class="form-control" required autocomplete="current-password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="hd-btn hd-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="hd-btn hd-btn-naranja"><i class="fas fa-broom"></i> Depurar</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL DETALLE ===== -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search" style="color:var(--naranja)"></i> Detalle del registro <span id="detId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3" style="font-size:0.8rem;">
                    <div class="col-md-6"><strong>Usuario:</strong> <span id="detUsuario"></span></div>
                    <div class="col-md-6"><strong>Fecha:</strong> <span id="detFecha"></span></div>
                    <div class="col-md-6"><strong>Acción:</strong> <span id="detAccion"></span></div>
                    <div class="col-md-6"><strong>Tabla / ID:</strong> <span id="detTabla"></span></div>
                    <div class="col-md-6"><strong>IP:</strong> <span id="detIp"></span></div>
                    <div class="col-12"><strong>Descripción:</strong> <span id="detDesc"></span></div>
                    <div class="col-12 text-muted" style="font-size:0.7rem;" id="detUA"></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6" id="boxAntes"><div class="hd-json-titulo">Datos anteriores</div><pre class="hd-json" id="detAntes"></pre></div>
                    <div class="col-md-6" id="boxDespues"><div class="hd-json-titulo">Datos nuevos</div><pre class="hd-json" id="detDespues"></pre></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    const tabla = $('#tablaBitacora').DataTable({
        processing: true,
        serverSide: true,              // paginación, búsqueda y orden en MySQL: escala a miles de registros
        searchDelay: 400,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [[1, 'desc']],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' },
        ajax: {
            url: 'procesos/bitacora_data.php',
            type: 'POST',
            data: function (d) {
                d.f_accion = $('#f_accion').val();
                d.f_tabla  = $('#f_tabla').val();
                d.f_desde  = $('#f_desde').val();
                d.f_hasta  = $('#f_hasta').val();
            },
            error: function () { alert('No se pudo cargar la bitácora. ¿Sigue activa tu sesión de administrador?'); }
        },
        columns: [
            { data: 'id' },
            { data: 'fecha' },
            { data: 'usuario' },
            { data: 'accion', render: function (a) {
                const clases = ['INSERT','UPDATE','DELETE','LOGIN','LOGOUT','BACKUP','EXPORT','IMPORT','RESTORE'];
                return '<span class="hd-acc acc-' + (clases.includes(a) ? a : 'otro') + '">' + a + '</span>';
            }},
            { data: 'tabla', render: function (t, type, row) {
                return t ? t + (row.id_registro ? ' <span class="text-muted">#' + row.id_registro + '</span>' : '') : '—';
            }},
            { data: 'descripcion' },
            { data: 'ip' },
            { data: 'id', orderable: false, searchable: false, render: function (id) {
                return '<button class="hd-icon-btn btn-ver" data-id="' + id + '" title="Ver detalle"><i class="fas fa-eye"></i></button>';
            }}
        ]
    });

    $('#f_accion, #f_tabla, #f_desde, #f_hasta').on('change', () => tabla.ajax.reload());
    // Exportar CSV con los filtros actuales
    $('#btnCsv').on('click', function (e) {
        e.preventDefault();
        const q = $.param({ f_accion: $('#f_accion').val(), f_tabla: $('#f_tabla').val(), f_desde: $('#f_desde').val(), f_hasta: $('#f_hasta').val() });
        window.location = 'procesos/bitacora_csv.php?' + q;
    });
    $('#btnLimpiar').on('click', function () {
        $('#f_accion, #f_tabla, #f_desde, #f_hasta').val('');
        tabla.search('').ajax.reload();
    });

    // Detalle: se usa .text() / textContent siempre, nunca .html(), para evitar XSS con datos guardados
    $('#tablaBitacora').on('click', '.btn-ver', function () {
        $.getJSON('procesos/bitacora_data.php', { detalle: $(this).data('id') }, function (r) {
            if (r.error) { alert(r.error); return; }
            const pretty = v => { if (!v) return null; try { return JSON.stringify(JSON.parse(v), null, 2); } catch (e) { return v; } };
            $('#detId').text('#' + r.id_bitacora);
            $('#detUsuario').text(r.usuario_login);
            $('#detFecha').text(r.fecha);
            $('#detAccion').text(r.accion);
            $('#detTabla').text((r.tabla_afectada || '—') + (r.id_registro ? ' / ' + r.id_registro : ''));
            $('#detIp').text(r.ip || '—');
            $('#detDesc').text(r.descripcion);
            $('#detUA').text(r.user_agent || '');
            const a = pretty(r.datos_anteriores), n = pretty(r.datos_nuevos);
            $('#detAntes').text(a || '');   $('#boxAntes').toggle(!!a);
            $('#detDespues').text(n || ''); $('#boxDespues').toggle(!!n);
            if (!a && !n) { $('#boxAntes').show(); $('#detAntes').text('Esta acción no guarda datos adicionales.'); }
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        });
    });
});
</script>

<?php include_once '../includes/footer2.php'; ?>

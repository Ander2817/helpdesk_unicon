<?php
// admin/respaldo.php — Respaldo, importación y restauración de la BD (solo Administrador)
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/functions.php';
require_once '../includes/respaldo_lib.php';
include '../config/conexion.php';

if (empty($_SESSION['csrf_respaldo'])) $_SESSION['csrf_respaldo'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf_respaldo'];

$respaldos = respaldo_listar();
$gz_ok = function_exists('gzopen');
$tam_bd = respaldo_tamano_bd($conexion);
$zip_ok = class_exists('ZipArchive');

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<style>
    .hd-bk-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px; margin-bottom: 14px; }
    .hd-bk-desc { font-size: 0.76rem; color: var(--muted); margin-bottom: 12px; line-height: 1.5; }
    .hd-bk-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .hd-bk-row .form-select, .hd-bk-row .form-control { font-size: 0.78rem; max-width: 190px; }
    .hd-aviso { display: flex; gap: 10px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
                border-radius: 8px; padding: 12px 14px; font-size: 0.76rem; line-height: 1.5; margin-bottom: 14px; }
    .hd-table td.acciones { white-space: nowrap; width: 1%; }
    .hd-icon-btn { width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--borde); background: #fff; color: var(--muted);
                   display: inline-flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.75rem; transition: all .15s; }
    .hd-icon-btn:hover { border-color: var(--naranja); color: var(--naranja); }
    .hd-icon-btn.peligro:hover { border-color: #dc2626; color: #dc2626; }
    .hd-tag { font-size: 0.65rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; background: #f1f5f9; color: #475569; }
    .hd-tag.pre { background: #fef3c7; color: #b45309; } .hd-tag.imp { background: #e0f2fe; color: #0369a1; }
    .modal-title { font-size: 0.95rem; font-weight: 700; color: var(--azul); }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-database" style="color:var(--naranja);margin-right:6px;"></i> Respaldo y restauración</div>
            <div class="hd-page-sub">Exporta, importa y restaura la base de datos <strong>helpdesk_unicon</strong></div>
        </div>
    </div>

    <?= mostrar_alerta_sistema() ?>

    <div class="hd-bk-grid">
        <!-- EXPORTAR -->
        <div class="hd-card">
            <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-file-export"></i> Exportar</h5></div>
            <div class="hd-card-body">
                <p class="hd-bk-desc">Genera un volcado completo (estructura y datos de todas las tablas). Puedes descargarlo ahora mismo o dejar una copia guardada en el servidor.
                   Los archivos adjuntos de los tickets viven en disco (<code>uploads/adjuntos</code>), no en la BD: descárgalos aparte con el botón «Adjuntos (.zip)» (para restaurarlos, descomprime ese .zip dentro de la carpeta <code>uploads/</code>).<br>Tamaño actual de la BD: <strong><?= respaldo_formato_bytes($tam_bd) ?></strong>.</p>
                <form method="post" action="procesos/respaldo/accion.php" class="hd-bk-row">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                    <select name="formato" class="form-select form-select-sm">
                        <option value="sql">.sql (texto plano)</option>
                        <?php if ($gz_ok): ?><option value="gz">.sql.gz (comprimido)</option><?php endif; ?>
                    </select>
                    <button type="submit" name="accion" value="exportar" class="hd-btn hd-btn-naranja"><i class="fas fa-download"></i> Descargar ahora</button>
                    <button type="submit" name="accion" value="crear" class="hd-btn hd-btn-primary"><i class="fas fa-save"></i> Guardar en servidor</button>
                    <?php if ($zip_ok): ?><button type="submit" name="accion" value="adjuntos" class="hd-btn hd-btn-outline"><i class="fas fa-paperclip"></i> Adjuntos (.zip)</button><?php endif; ?>
                </form>
            </div>
        </div>

        <!-- IMPORTAR -->
        <div class="hd-card">
            <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-file-import"></i> Importar</h5></div>
            <div class="hd-card-body">
                <p class="hd-bk-desc">Sube un archivo <code>.sql</code> o <code>.sql.gz</code> (por ejemplo, uno exportado desde phpMyAdmin). Se revisa que sea un respaldo válido y queda en la lista de abajo;
                   <strong>no se aplica</strong> hasta que pulses «Restaurar».<br>
                   Tamaño máximo: hasta <strong>200 MB</strong> 
                   Para un archivo mayor, cópialo a mano a la carpeta <code>backups/</code> del proyecto: aparecerá en la lista de abajo y se restaura igual.</p>
                <form method="post" action="procesos/respaldo/accion.php" enctype="multipart/form-data" class="hd-bk-row">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="accion" value="importar">
                    <input type="file" name="archivo" accept=".sql,.gz" class="form-control form-control-sm" required>
                    <button type="submit" class="hd-btn hd-btn-primary"><i class="fas fa-upload"></i> Importar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="hd-aviso">
        <i class="fas fa-triangle-exclamation" style="margin-top:2px;"></i>
        <div><strong>Restaurar reemplaza la base de datos actual</strong> (usuarios, tickets, comentarios…) por el contenido del respaldo elegido.
        La <strong>bitácora actual se conserva</strong> (así el historial de auditoría nunca se pierde); puedes marcar la opción contraria en la ventana de confirmación.
        Antes de hacerlo el sistema guarda automáticamente un respaldo <code>pre_restauracion_…</code>, y si algo falla a mitad del proceso vuelve solo al estado anterior.
        Se te pedirá tu contraseña.</div>
    </div>

    <!-- LISTA -->
    <div class="hd-card">
        <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-folder-open"></i> Respaldos guardados en el servidor (<?= count($respaldos) ?>)</h5></div>
        <div class="hd-card-body" style="padding:0;">
            <table class="hd-table">
                <thead><tr><th>Archivo</th><th>Fecha</th><th>Tamaño</th><th></th></tr></thead>
                <tbody>
                <?php if (!$respaldos): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px;">Aún no hay respaldos guardados.</td></tr>
                <?php endif; ?>
                <?php foreach ($respaldos as $b):
                    $n = $b['nombre'];
                    $tag = str_starts_with($n, 'pre_restauracion_') ? '<span class="hd-tag pre">previo a restauración</span>'
                         : (str_starts_with($n, 'importado_') ? '<span class="hd-tag imp">importado</span>' : '');
                ?>
                    <tr>
                        <td><i class="fas fa-file-code" style="color:var(--muted);margin-right:6px;"></i><?= htmlspecialchars($n) ?> <?= $tag ?></td>
                        <td style="color:var(--muted);"><?= date('d/m/Y H:i:s', $b['fecha']) ?></td>
                        <td style="color:var(--muted);"><?= respaldo_formato_bytes($b['tamano']) ?></td>
                        <td class="acciones">
                            <form method="post" action="procesos/respaldo/accion.php" style="display:inline;">
                                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                <input type="hidden" name="accion" value="descargar">
                                <input type="hidden" name="nombre" value="<?= htmlspecialchars($n) ?>">
                                <button class="hd-icon-btn" title="Descargar"><i class="fas fa-download"></i></button>
                            </form>
                            <button type="button" class="hd-icon-btn" title="Restaurar" onclick="abrirRestaurar(<?= htmlspecialchars(json_encode($n), ENT_QUOTES) ?>)"><i class="fas fa-rotate-left"></i></button>
                            <form method="post" action="procesos/respaldo/accion.php" style="display:inline;" onsubmit="return confirm('¿Eliminar este respaldo del servidor? No se puede deshacer.');">
                                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="nombre" value="<?= htmlspecialchars($n) ?>">
                                <button class="hd-icon-btn peligro" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- ===== MODAL RESTAURAR ===== -->
<div class="modal fade" id="modalRestaurar" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="procesos/respaldo/accion.php" id="formRestaurar">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-rotate-left" style="color:var(--naranja)"></i> Restaurar base de datos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                <input type="hidden" name="accion" value="restaurar">
                <input type="hidden" name="nombre" id="rNombre">
                <p style="font-size:0.82rem;">Vas a reemplazar <strong>toda</strong> la base de datos con el contenido de:<br><code id="rNombreTxt"></code></p>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;">Tu contraseña de administrador</label>
                    <input type="password" name="password" class="form-control" required autocomplete="current-password">
                </div>
                <div class="mb-1">
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;">Escribe <code>RESTAURAR</code> para confirmar</label>
                    <input type="text" name="confirmar" class="form-control" required autocomplete="off" pattern="RESTAURAR">
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="incluir_bitacora" value="1" id="chkBit">
                    <label class="form-check-label" for="chkBit" style="font-size:0.76rem;">Reemplazar también la bitácora por la del respaldo <span class="text-muted">(no recomendado)</span></label>
                </div>
                <small class="text-muted">El proceso puede tardar. No cierres esta ventana hasta que termine.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="hd-btn hd-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="hd-btn hd-btn-naranja" id="btnRestaurar"><i class="fas fa-rotate-left"></i> Restaurar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirRestaurar(nombre) {
    document.getElementById('rNombre').value = nombre;
    document.getElementById('rNombreTxt').textContent = nombre;
    document.getElementById('formRestaurar').reset();
    document.getElementById('rNombre').value = nombre;
    new bootstrap.Modal(document.getElementById('modalRestaurar')).show();
}
document.getElementById('formRestaurar').addEventListener('submit', function () {
    const b = document.getElementById('btnRestaurar');
    b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restaurando…';
});
</script>

<?php include_once '../includes/footer2.php'; ?>

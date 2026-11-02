<?php
// includes/kb_vista.php — Base de conocimiento: lista con buscador, lectura de un artículo y editor (admin / técnico con permiso).
// Lo incluyen admin/kb.php, tecnico/kb.php y usuario/kb.php (?id=N para leer un artículo, ?q= para buscar).
require_once '../includes/permisos.php';

$puede_editar = rol_tiene_permiso($conexion, $rol_id, 'kb.editar');
$id_art = (int)($_GET['id'] ?? 0);
$busq   = trim((string)($_GET['q'] ?? ''));
$cat_f  = (int)($_GET['cat'] ?? 0);
$falta_migracion = false;
$articulo = null; $lista = [];

$categorias = $conexion->query("SELECT id_categoria, nombre FROM categoria_incidencias WHERE estado = 'activo' ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

function kb_texto_html(string $t): string {
    $h = nl2br(htmlspecialchars($t));
    return preg_replace('~(https?://[^\s<]+)~i', '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', $h);
}

try {
    if ($id_art > 0) {
        $sql = "SELECT a.*, c.nombre AS categoria, CONCAT(u.nombres, ' ', u.apellidos) AS autor
                FROM kb_articulos a LEFT JOIN categoria_incidencias c ON a.id_categoria = c.id_categoria LEFT JOIN usuarios u ON a.id_autor = u.id_usuario
                WHERE a.id_articulo = ?" . ($puede_editar ? '' : ' AND a.publicado = 1');
        $q = $conexion->prepare($sql); $q->bind_param("i", $id_art); $q->execute();
        $articulo = $q->get_result()->fetch_assoc(); $q->close();
        if ($articulo && empty($_SESSION['kb_vistos'][$id_art])) {     // cuenta una visita por sesión
            $_SESSION['kb_vistos'][$id_art] = 1;
            $conexion->query("UPDATE kb_articulos SET vistas = vistas + 1 WHERE id_articulo = " . $id_art);
        }
    } else {
        $where = []; $tipos = ''; $par = [];
        if (!$puede_editar) $where[] = 'a.publicado = 1';
        if ($busq !== '') { $l = '%' . addcslashes($busq, '%_\\') . '%'; $where[] = '(a.titulo LIKE ? OR a.contenido LIKE ?)'; $tipos .= 'ss'; $par[] = $l; $par[] = $l; }
        if ($cat_f > 0)   { $where[] = 'a.id_categoria = ?'; $tipos .= 'i'; $par[] = $cat_f; }
        $sql = "SELECT a.id_articulo, a.titulo, a.contenido, a.publicado, a.vistas, a.actualizado, c.nombre AS categoria
                FROM kb_articulos a LEFT JOIN categoria_incidencias c ON a.id_categoria = c.id_categoria"
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY a.actualizado DESC LIMIT 100";
        $q = $conexion->prepare($sql);
        if ($tipos !== '') $q->bind_param($tipos, ...$par);
        $q->execute();
        $lista = $q->get_result()->fetch_all(MYSQLI_ASSOC); $q->close();
    }
} catch (Throwable $e) { $falta_migracion = true; }
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<main class="hd-main">
    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-book-open" style="color:var(--naranja);margin-right:6px;"></i> Base de conocimiento</div>
            <div class="hd-page-sub">Guías y soluciones a los problemas más comunes — búscalas antes de abrir un ticket</div>
        </div>
        <div style="display:flex;gap:8px;">
            <?php if ($id_art): ?><a href="kb.php" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-arrow-left"></i> Todos los artículos</a><?php endif; ?>
            <?php if ($puede_editar && !$falta_migracion && !$id_art): ?><button type="button" class="hd-btn hd-btn-naranja hd-btn-sm" onclick="kbEditar(null)"><i class="fas fa-plus"></i> Nuevo artículo</button><?php endif; ?>
        </div>
    </div>

    <?php if ($falta_migracion): ?>
        <div class="alert alert-warning">Falta crear la tabla de artículos. Ejecuta <code>DB/migracion_entrega4.sql</code> en phpMyAdmin.</div>
    <?php elseif ($id_art): ?>
        <?php if (!$articulo): ?>
            <div class="alert alert-danger">El artículo no existe o no está publicado.</div>
        <?php else: ?>
            <div class="hd-card" style="padding:22px 26px;max-width:860px;">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:start;flex-wrap:wrap;">
                    <h2 style="font-size:1.25rem;font-weight:700;color:var(--azul);margin:0;"><?= htmlspecialchars($articulo['titulo']) ?>
                        <?php if (!$articulo['publicado']): ?><span class="hd-badge" style="background:#fef3c7;color:#92400e;font-size:.65rem;vertical-align:middle;">Borrador</span><?php endif; ?></h2>
                    <?php if ($puede_editar): ?>
                    <div style="display:flex;gap:6px;">
                        <button type="button" class="hd-btn hd-btn-outline hd-btn-sm" onclick='kbEditar(<?= json_encode(["id"=>(int)$articulo["id_articulo"],"titulo"=>$articulo["titulo"],"contenido"=>$articulo["contenido"],"cat"=>(int)$articulo["id_categoria"],"pub"=>(int)$articulo["publicado"]], JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP) ?>)'><i class="fas fa-pen"></i> Editar</button>
                        <button type="button" class="hd-btn hd-btn-outline hd-btn-sm" style="color:#dc2626;" onclick="kbEliminar(<?= (int)$articulo['id_articulo'] ?>)"><i class="fas fa-trash"></i></button>
                    </div>
                    <?php endif; ?>
                </div>
                <div style="font-size:.72rem;color:var(--muted);margin:6px 0 16px;">
                    <?= $articulo['categoria'] ? htmlspecialchars($articulo['categoria']) . ' · ' : '' ?>Actualizado el <?= date('d/m/Y', strtotime($articulo['actualizado'])) ?> · <?= (int)$articulo['vistas'] ?> visita(s)
                </div>
                <div style="font-size:.9rem;line-height:1.65;color:var(--texto);"><?= kb_texto_html($articulo['contenido']) ?></div>
                <hr style="border-color:var(--borde);margin:22px 0 12px;">
                <div style="font-size:.8rem;color:var(--muted);">¿No resolvió tu problema? <a href="<?= $rol_id === ROL_USUARIO ? 'ticket_nuevo.php' : 'tickets_crear.php' ?>" style="color:var(--naranja);font-weight:600;">Crea un ticket</a>.</div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <form method="get" class="hd-card" style="padding:14px 18px;margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <input type="search" name="q" value="<?= htmlspecialchars($busq) ?>" class="hd-input" style="flex:1;min-width:200px;" placeholder="Buscar: impresora, vpn, contraseña…">
            <select name="cat" class="hd-input" style="width:auto;">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categorias as $c): ?><option value="<?= (int)$c['id_categoria'] ?>" <?= $cat_f === (int)$c['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?>
            </select>
            <button class="hd-btn hd-btn-naranja hd-btn-sm" type="submit"><i class="fas fa-search"></i> Buscar</button>
        </form>
        <?php if (!$lista): ?>
            <div class="hd-card" style="padding:26px;text-align:center;color:var(--muted);font-size:.85rem;">
                <?= ($busq !== '' || $cat_f) ? 'No hay artículos que coincidan con tu búsqueda.' : 'Aún no hay artículos publicados.' ?>
            </div>
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;">
        <?php foreach ($lista as $a):
            $ext = trim(preg_replace('/\s+/', ' ', $a['contenido'])); $ext = mb_strlen($ext) > 150 ? mb_substr($ext, 0, 149) . '…' : $ext; ?>
            <a href="kb.php?id=<?= (int)$a['id_articulo'] ?>" class="hd-card" style="padding:16px 18px;text-decoration:none;display:block;">
                <div style="font-size:.88rem;font-weight:700;color:var(--azul);"><?= htmlspecialchars($a['titulo']) ?>
                    <?php if (!$a['publicado']): ?><span class="hd-badge" style="background:#fef3c7;color:#92400e;font-size:.6rem;">Borrador</span><?php endif; ?></div>
                <div style="font-size:.78rem;color:var(--muted);margin:6px 0 8px;"><?= htmlspecialchars($ext) ?></div>
                <div style="font-size:.68rem;color:var(--muted);"><?= $a['categoria'] ? htmlspecialchars($a['categoria']) . ' · ' : '' ?><?= (int)$a['vistas'] ?> visita(s)</div>
            </a>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php if ($puede_editar && !$falta_migracion): ?>
<div class="modal fade" id="modalKb" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="kb-titulo-modal">Artículo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="form-kb">
        <div class="modal-body">
            <input type="hidden" name="id" id="kb-id" value="0">
            <label class="hd-label">Título *</label>
            <input type="text" name="titulo" id="kb-titulo" class="hd-input" maxlength="200" required>
            <label class="hd-label" style="margin-top:10px;">Categoría</label>
            <select name="id_categoria" id="kb-cat" class="hd-input"><option value="0">— Sin categoría —</option>
                <?php foreach ($categorias as $c): ?><option value="<?= (int)$c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?></select>
            <label class="hd-label" style="margin-top:10px;">Contenido * <span style="font-weight:400;color:var(--muted);">(texto simple; los enlaces http(s) se vuelven clicables)</span></label>
            <textarea name="contenido" id="kb-contenido" class="hd-input" rows="10" maxlength="20000" required placeholder="Paso 1…&#10;Paso 2…"></textarea>
            <label style="display:flex;gap:8px;align-items:center;margin-top:10px;font-size:.82rem;"><input type="checkbox" name="publicado" id="kb-pub" value="1" checked> Publicado (visible para todos los usuarios)</label>
            <div id="res-kb" style="margin-top:10px;"></div>
        </div>
        <div class="modal-footer"><button type="button" class="hd-btn hd-btn-outline" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="hd-btn hd-btn-naranja"><i class="fas fa-save"></i> Guardar</button></div>
    </form>
</div></div></div>
<script>
function kbEditar(a) {
    $('#kb-titulo-modal').text(a ? 'Editar artículo' : 'Nuevo artículo');
    $('#kb-id').val(a ? a.id : 0); $('#kb-titulo').val(a ? a.titulo : ''); $('#kb-contenido').val(a ? a.contenido : '');
    $('#kb-cat').val(a ? a.cat : 0); $('#kb-pub').prop('checked', a ? !!a.pub : true); $('#res-kb').html('');
    new bootstrap.Modal(document.getElementById('modalKb')).show();
}
function kbEliminar(id) {
    if (!confirm('¿Eliminar este artículo? No se puede deshacer.')) return;
    $.post('../procesos/kb_eliminar.php', { id: id })
        .done(function (r) { if (r.includes('alert-success')) location.href = 'kb.php'; else alert($('<div>').html(r).text()); })
        .fail(function () { alert('Error de conexión.'); });
}
$('#form-kb').on('submit', function (e) {
    e.preventDefault();
    $.post('../procesos/kb_guardar.php', $(this).serialize())
        .done(function (r) { $('#res-kb').html(r); if (r.includes('alert-success')) setTimeout(() => location.reload(), 700); })
        .fail(function (x) { $('#res-kb').html(x.responseText || '<div class="alert alert-danger">Error de conexión.</div>'); });
});
</script>
<?php endif; ?>

<?php
// admin/qr_equipos.php — Etiquetas con código QR para pegar en cada equipo. El QR abre «Nuevo ticket» con ese equipo ya elegido.
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) { header("Location: ../index.php"); exit(); }
require_once '../includes/constants.php';
require_once '../includes/functions.php';
include '../config/conexion.php';
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$pagina_actual = 'qr_equipos.php';
$dep = (int)($_GET['dep'] ?? 0); $solo = (int)($_GET['id'] ?? 0);
$deps = $conexion->query("SELECT id_departamento, nombre FROM departamentos ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
$sql = "SELECT i.id_inventario, i.codigo_equipo, i.tipo_equipo, i.marca, i.modelo, i.ubicacion, d.nombre AS depto
        FROM inventario_tecnologico i LEFT JOIN departamentos d ON d.id_departamento = i.id_departamento WHERE i.estado <> 'inactivo'";
$p = []; $t = '';
if ($solo) { $sql .= " AND i.id_inventario = ?"; $p[] = $solo; $t .= 'i'; }
elseif ($dep) { $sql .= " AND i.id_departamento = ?"; $p[] = $dep; $t .= 'i'; }
$sql .= " ORDER BY d.nombre, i.codigo_equipo LIMIT 500";
$st = $conexion->prepare($sql); if ($p) $st->bind_param($t, ...$p); $st->execute();
$eq = $st->get_result()->fetch_all(MYSQLI_ASSOC); $st->close();
// URL pública base: la configurada en constants.php (URL_BASE)
$base = rtrim(URL_BASE, '/') . '/';
include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>
<style>
 .qr-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:12px}
 .qr-et{border:1.5px dashed #94a3b8;border-radius:10px;padding:12px;text-align:center;background:#fff;color:#0f172a;break-inside:avoid}
 .qr-et .qr{display:flex;justify-content:center;margin:6px 0}
 .qr-et .cod{font-weight:800;font-size:1.05rem;letter-spacing:.03em}
 .qr-et .sub{font-size:.75rem;color:#475569}.qr-et .tit{font-size:.7rem;text-transform:uppercase;color:#E87722;font-weight:700}
 @media print{ .hd-sidebar,.sidebar,header,nav,.no-print,.hd-page-header{display:none!important} .hd-main{margin:0!important;padding:0!important} .qr-grid{grid-template-columns:repeat(3,1fr)} body{background:#fff!important} }
</style>
<main class="hd-main">
  <div class="hd-page-header no-print"><div><h1 class="hd-page-title"><i class="fa-solid fa-qrcode me-2"></i>Etiquetas QR de equipos</h1>
    <p class="hd-page-sub">Imprime, recorta y pega cada etiqueta en su equipo. Quien la escanee y tenga sesión abre «Nuevo ticket» con ese equipo ya seleccionado.</p></div></div>
  <div class="hd-card mb-3 no-print"><div class="hd-card-body d-flex gap-2 flex-wrap align-items-center">
    <form method="get" class="d-flex gap-2 align-items-center">
      <select name="dep" class="hd-input" onchange="this.form.submit()"><option value="0">Todos los departamentos</option>
        <?php foreach ($deps as $d): ?><option value="<?= (int)$d['id_departamento'] ?>" <?= $dep === (int)$d['id_departamento'] ? 'selected' : '' ?>><?= $h($d['nombre']) ?></option><?php endforeach; ?></select>
    </form>
    <span class="text-muted small"><?= count($eq) ?> etiqueta(s)</span>
    <button class="hd-btn hd-btn-primary ms-auto" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Imprimir</button>
  </div>
  <?php if (strpos($base, 'localhost') !== false || strpos($base, '127.0.0.1') !== false): ?>
    <div class="px-3 pb-3 small text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>URL_BASE apunta a <b>localhost</b>: los QR solo funcionarán en este PC o los del proyecto. En el dominio debemos cambiarlo Angel y en unicon.</div>
  <?php endif; ?></div>
  <div class="qr-grid">
  <?php foreach ($eq as $e): ?>
    <div class="qr-et"><div class="tit">¿Falla? Escanea y repórtalo</div>
      <div class="qr" data-url="<?= $h($base . 'reportar_equipo.php?e=' . (int)$e['id_inventario']) ?>"></div>
      <div class="cod"><?= $h($e['codigo_equipo']) ?></div>
      <div class="sub"><?= $h($e['tipo_equipo']) ?> · <?= $h(trim($e['marca'] . ' ' . $e['modelo'])) ?></div>
      <div class="sub"><?= $h($e['depto'] ?? '') ?><?= $e['ubicacion'] ? ' · ' . $h($e['ubicacion']) : '' ?></div></div>
  <?php endforeach; if (!$eq): ?><p class="text-muted">No hay equipos para mostrar.</p><?php endif; ?>
  </div>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.querySelectorAll('.qr').forEach(el => {
    if (window.QRCode) new QRCode(el, {text: el.dataset.url, width: 130, height: 130, correctLevel: QRCode.CorrectLevel.M});
    else el.textContent = 'No se pudo cargar el generador de QR (revisa la conexión a internet).';
});
</script>
<?php include_once '../includes/footer2.php'; ?>

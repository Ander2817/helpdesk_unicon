<?php
// admin/reportes_gestion.php — Reporte de gestión completo (pantalla). El PDF sale de admin/reporte_gestion_pdf.php.
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) { header("Location: ../index.php"); exit(); }
require_once '../includes/constants.php';
require_once '../includes/functions.php';
require_once '../includes/reporte_datos.php';
require_once '../includes/reporte_gestion.php';
include '../config/conexion.php';
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$pagina_actual = 'reportes_gestion.php';
$rango = reporte_rango($_GET['desde'] ?? null, $_GET['hasta'] ?? null);
$d = rg_obtener($conexion, $rango['desde'], $rango['hasta']);
$a = $d['actual']; $p = $d['previo'];
$q = 'desde=' . $rango['desde'] . '&hasta=' . $rango['hasta'];
$fm = fn($v, $dec = 1) => $v === null ? '—' : number_format((float)$v, $dec, ',', '.');
/** Flecha de variación; $mejor_sube = true si subir es bueno. */
function rg_flecha(?float $dl, bool $mejor_sube): string {
    if ($dl === null) return '<span class="text-muted">—</span>';
    if (abs($dl) < 0.05) return '<span class="text-muted">= igual</span>';
    $bien = ($dl > 0) === $mejor_sube;
    return '<span style="color:' . ($bien ? '#16a34a' : '#dc2626') . ';font-weight:600"><i class="fa-solid fa-arrow-' . ($dl > 0 ? 'up' : 'down') . '"></i> ' . number_format(abs($dl), 1, ',', '.') . '%</span>';
}
$max_mes = max(1, ...array_map(fn($m) => max($m['creados'], $m['cerrados']), $d['mensual'] ?: [['creados' => 1, 'cerrados' => 1]]));
include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>
<main class="hd-main">
  <div class="hd-page-header"><div><h1 class="hd-page-title"><i class="fa-solid fa-chart-line me-2"></i>Reporte de gestión</h1>
    <p class="hd-page-sub"><?= $h(date('d/m/Y', strtotime($d['desde']))) ?> – <?= $h(date('d/m/Y', strtotime($d['hasta']))) ?> (<?= $d['dias'] ?> días) · comparado con <?= $h(date('d/m/Y', strtotime($d['prev_desde']))) ?> – <?= $h(date('d/m/Y', strtotime($d['prev_hasta']))) ?></p></div>
    <a class="hd-btn hd-btn-primary" href="reporte_gestion_pdf.php?<?= $q ?>"><i class="fa-solid fa-file-pdf me-1"></i>Descargar PDF</a></div>
  <form class="d-flex gap-2 flex-wrap mb-3" method="get">
    <input type="date" name="desde" class="hd-input" value="<?= $h($rango['desde']) ?>" max="<?= date('Y-m-d') ?>">
    <input type="date" name="hasta" class="hd-input" value="<?= $h($rango['hasta']) ?>" max="<?= date('Y-m-d') ?>">
    <button class="hd-btn hd-btn-outline">Aplicar</button>
    <?php foreach ([30 => '30 días', 90 => '90 días', 180 => '6 meses', 365 => '1 año'] as $n => $t): ?>
      <a class="hd-btn hd-btn-outline" href="?desde=<?= date('Y-m-d', strtotime("-" . ($n - 1) . " days")) ?>&hasta=<?= date('Y-m-d') ?>"><?= $t ?></a><?php endforeach; ?>
  </form>
  <?php if ($rango['ajustado']): ?><div class="alert alert-warning small">El rango se recortó al máximo permitido (<?= REPORTE_MAX_DIAS ?> días).</div><?php endif; ?>

  <div class="hd-card mb-3"><div class="hd-card-header"><span class="hd-card-title">Comparación con el período anterior</span></div>
    <div class="hd-card-body" style="overflow:auto"><table class="hd-table"><thead><tr><th>Indicador</th><th>Este período</th><th>Anterior</th><th>Variación</th></tr></thead><tbody>
      <tr><td>Tickets creados</td><td><b><?= $a['creados'] ?></b></td><td><?= $p['creados'] ?></td><td><?= rg_flecha(rg_delta($a['creados'], $p['creados']), false) ?></td></tr>
      <tr><td>Tickets cerrados</td><td><b><?= $a['cerrados'] ?></b></td><td><?= $p['cerrados'] ?></td><td><?= rg_flecha(rg_delta($a['cerrados'], $p['cerrados']), true) ?></td></tr>
      <tr><td>Tiempo medio de resolución (h, sin tiempo «Pendiente»)</td><td><b><?= $fm($a['prom_horas']) ?></b></td><td><?= $fm($p['prom_horas']) ?></td><td><?= rg_flecha(rg_delta($a['prom_horas'], $p['prom_horas']), false) ?></td></tr>
      <tr><td>Cumplimiento de SLA</td><td><b><?= $a['sla_pct'] === null ? '—' : $a['sla_pct'] . '%' ?></b></td><td><?= $p['sla_pct'] === null ? '—' : $p['sla_pct'] . '%' ?></td><td><?= rg_flecha(rg_delta($a['sla_pct'], $p['sla_pct']), true) ?></td></tr>
      <tr><td>Satisfacción (1–5)</td><td><b><?= $fm($a['sat_prom'], 2) ?></b> <small class="text-muted">(<?= $a['sat_n'] ?>)</small></td><td><?= $fm($p['sat_prom'], 2) ?> <small class="text-muted">(<?= $p['sat_n'] ?>)</small></td><td><?= rg_flecha(rg_delta($a['sat_prom'], $p['sat_prom']), true) ?></td></tr>
    </tbody></table></div></div>

  <div class="hd-card mb-3"><div class="hd-card-header"><span class="hd-card-title">Tendencia mensual</span>
      <span class="small"><span style="color:#E87722">■</span> creados &nbsp; <span style="color:#1A2A4A">■</span> cerrados</span></div>
    <div class="hd-card-body">
    <?php foreach ($d['mensual'] as $m): ?>
      <div class="d-flex align-items-center gap-2 mb-1"><span style="width:70px;font-size:.8rem"><?= $h($m['mes']) ?></span>
        <div style="flex:1"><div style="height:9px;background:#E87722;border-radius:4px;width:<?= round(100 * $m['creados'] / $max_mes) ?>%;min-width:2px"></div>
          <div style="height:9px;background:#1A2A4A;border-radius:4px;margin-top:2px;width:<?= round(100 * $m['cerrados'] / $max_mes) ?>%;min-width:2px"></div></div>
        <span style="width:80px;font-size:.8rem;text-align:right"><?= $m['creados'] ?> / <?= $m['cerrados'] ?></span></div>
    <?php endforeach; ?></div></div>

  <div class="row g-3 mb-3">
    <div class="col-lg-6"><div class="hd-card h-100"><div class="hd-card-header"><span class="hd-card-title">SLA por prioridad</span></div><div class="hd-card-body" style="overflow:auto">
      <table class="hd-table"><thead><tr><th>Prioridad</th><th>Objetivo</th><th>Cerrados</th><th>Cumplidos</th><th>Tiempo medio</th></tr></thead><tbody>
      <?php foreach ($d['sla'] as $s): $col = $s['pct'] === null ? '#64748b' : ($s['pct'] >= 80 ? '#16a34a' : ($s['pct'] >= 50 ? '#d97706' : '#dc2626')); ?>
        <tr><td><?= $h($s['nombre']) ?></td><td><?= $h(sla_formato((int)$s['obj'])) ?></td><td><?= $s['n'] ?></td><td style="color:<?= $col ?>;font-weight:700"><?= $s['pct'] === null ? '—' : $s['pct'] . '%' ?></td><td><?= $h(sla_formato($s['prom'])) ?></td></tr>
      <?php endforeach; if (!$d['sla']) echo '<tr><td colspan="5" class="text-muted text-center">Sin tickets cerrados en el período.</td></tr>'; ?></tbody></table>
      <p class="small text-muted mb-0">El tiempo en «Pendiente» (esperando al usuario) no cuenta contra el SLA.</p></div></div></div>
    <div class="col-lg-6"><div class="hd-card h-100"><div class="hd-card-header"><span class="hd-card-title">Equipos con fallas repetidas</span></div><div class="hd-card-body" style="overflow:auto">
      <table class="hd-table"><thead><tr><th>Equipo</th><th>Departamento</th><th>Tickets</th></tr></thead><tbody>
      <?php foreach ($d['equipos'] as $e): ?><tr><td><b><?= $h($e['codigo_equipo']) ?></b> <small class="text-muted"><?= $h($e['tipo_equipo']) ?></small></td><td><?= $h($e['depto']) ?></td><td><span class="badge bg-danger"><?= (int)$e['n'] ?></span></td></tr>
      <?php endforeach; if (!$d['equipos']) echo '<tr><td colspan="3" class="text-muted text-center">Ningún equipo con 2 o más tickets.</td></tr>'; ?></tbody></table>
      <p class="small text-muted mb-0">Candidatos a revisión profunda o reemplazo.</p></div></div></div>
  </div>

  <div class="hd-card"><div class="hd-card-header"><span class="hd-card-title">Satisfacción</span><a class="small" href="satisfaccion.php">Ver detalle</a></div><div class="hd-card-body">
    <div class="row g-3"><div class="col-md-5">
      <?php $tot = max(1, array_sum($d['sat_dist'])); for ($i = 5; $i >= 1; $i--): ?>
        <div class="d-flex align-items-center gap-2 mb-1"><span style="width:28px"><?= $i ?> ★</span><div style="flex:1;background:#e2e8f0;border-radius:4px;height:10px"><div style="height:10px;border-radius:4px;background:#E87722;width:<?= round(100 * $d['sat_dist'][$i] / $tot) ?>%"></div></div><span style="width:30px;text-align:right"><?= $d['sat_dist'][$i] ?></span></div>
      <?php endfor; ?></div>
    <div class="col-md-7"><table class="hd-table"><thead><tr><th>Técnico</th><th>Encuestas</th><th>Promedio</th></tr></thead><tbody>
      <?php foreach ($d['sat_tec'] as $t): ?><tr><td><?= $h($t['nombre']) ?></td><td><?= (int)$t['n'] ?></td><td><b><?= $fm($t['prom'], 2) ?></b></td></tr>
      <?php endforeach; if (!$d['sat_tec']) echo '<tr><td colspan="3" class="text-muted text-center">Sin encuestas en el período.</td></tr>'; ?></tbody></table></div></div>
  </div></div>
</main>
<?php include_once '../includes/footer2.php'; ?>

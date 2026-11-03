<?php
// admin/satisfaccion.php — Reporte de satisfacción: promedio por técnico y por categoría, distribución y comentarios (tabla encuestas_ticket).
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) { header("Location: ../index.php"); exit(); }
require_once '../includes/constants.php';
require_once '../includes/functions.php';
include '../config/conexion.php';
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$dias = (int)($_GET['dias'] ?? 90);
if (!in_array($dias, [30, 90, 365, 0], true)) $dias = 90;
$filtro = $dias > 0 ? "AND e.fecha > (NOW() - INTERVAL $dias DAY)" : '';
function sa_q(mysqli $c, string $sql): ?array { try { return $c->query($sql)->fetch_all(MYSQLI_ASSOC); } catch (Throwable $e) { return null; } }

$base = "FROM encuestas_ticket e JOIN tickets t ON t.id_tickets = e.id_ticket WHERE 1=1 $filtro";
$res   = sa_q($conexion, "SELECT COUNT(*) n, ROUND(AVG(e.puntuacion), 2) prom, SUM(e.puntuacion >= 4) buenas $base");
$dist  = sa_q($conexion, "SELECT e.puntuacion p, COUNT(*) n $base GROUP BY e.puntuacion");
$porTec = sa_q($conexion, "SELECT CONCAT(u.nombres, ' ', u.apellidos) AS nombre, COUNT(*) n, ROUND(AVG(e.puntuacion), 2) prom
                        FROM encuestas_ticket e JOIN tickets t ON t.id_tickets = e.id_ticket JOIN usuarios u ON u.id_usuario = t.id_usuario_asignado
                        WHERE 1=1 $filtro GROUP BY u.id_usuario, u.nombres, u.apellidos ORDER BY prom DESC, n DESC");
$porCat = sa_q($conexion, "SELECT c.nombre, COUNT(*) n, ROUND(AVG(e.puntuacion), 2) prom
                        FROM encuestas_ticket e JOIN tickets t ON t.id_tickets = e.id_ticket JOIN categoria_incidencias c ON c.id_categoria = t.id_categoria
                        WHERE 1=1 $filtro GROUP BY c.id_categoria, c.nombre ORDER BY prom DESC, n DESC");
$coment = sa_q($conexion, "SELECT e.puntuacion, e.comentario, e.fecha, t.id_tickets, t.codigo_ticket
                        $base AND e.comentario IS NOT NULL AND e.comentario <> '' ORDER BY e.fecha DESC LIMIT 10");
$n = (int)($res[0]['n'] ?? 0); $prom = $res[0]['prom'] ?? null; $buenas = (int)($res[0]['buenas'] ?? 0);
$d = array_fill(1, 5, 0); foreach ($dist ?? [] as $r) $d[(int)$r['p']] = (int)$r['n']; $max = max($d) ?: 1;
$estrellas = fn($p) => str_repeat('★', (int)round((float)$p)) . str_repeat('☆', 5 - (int)round((float)$p));
$color = fn($p) => $p >= 4 ? '#16a34a' : ($p >= 3 ? '#d97706' : '#dc2626');

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>
<style>
    .sa-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:16px; margin-bottom:16px; }
    .sa-kpi { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
    .sa-kpi > div { background:#fff; border:1px solid var(--borde); border-radius:10px; padding:12px 18px; min-width:140px; }
    .sa-kpi b { display:block; font-size:1.6rem; } .sa-kpi span { font-size:.74rem; color:var(--muted); }
    .sa-bar { height:10px; border-radius:5px; background:#e5e7eb; overflow:hidden; flex:1; } .sa-bar i { display:block; height:100%; background:#f59e0b; }
    .sa-fila { display:flex; align-items:center; gap:10px; margin-bottom:7px; font-size:.8rem; }
    .sa-tabs a { padding:5px 12px; border:1px solid var(--borde); border-radius:16px; font-size:.78rem; color:var(--muted); text-decoration:none; background:#fff; }
    .sa-tabs a.on { background:var(--naranja); color:#fff; border-color:var(--naranja); }
    @media print { .hd-sidebar, .hd-topbar, .no-print { display:none !important; } .hd-main { margin:0 !important; } }
</style>
<main class="hd-main">
    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-star" style="color:#f59e0b;margin-right:6px;"></i> Satisfacción de usuarios</div>
            <div class="hd-page-sub">Calificaciones que dan los usuarios al cerrar sus tickets (1 a 5 estrellas).</div>
        </div>
        <div class="no-print" style="display:flex;gap:8px;flex-wrap:wrap;">
            <a class="hd-btn hd-btn-outline" href="../procesos/exportar_csv.php?tipo=satisfaccion"><i class="fas fa-file-csv"></i> CSV</a>
            <button class="hd-btn hd-btn-outline" type="button" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / PDF</button>
        </div>
    </div>
    <div class="sa-tabs no-print" style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;">
        <?php foreach ([30 => '30 días', 90 => '90 días', 365 => '1 año', 0 => 'Todo'] as $k => $t): ?><a href="?dias=<?= $k ?>" class="<?= $dias === $k ? 'on' : '' ?>"><?= $t ?></a><?php endforeach; ?>
    </div>
<?php if ($res === null): ?>
    <div class="hd-card"><div class="hd-card-body">La tabla <code>encuestas_ticket</code> no existe todavía (módulo 02).</div></div>
<?php elseif ($n === 0): ?>
    <div class="hd-card"><div class="hd-card-body" style="color:var(--muted);">Todavía no hay calificaciones en este período. Aparecen cuando un usuario califica un ticket resuelto o cerrado.</div></div>
<?php else: ?>
    <div class="sa-kpi">
        <div><b style="color:<?= $color($prom) ?>;"><?= number_format((float)$prom, 2, ',', '') ?> <small style="font-size:.9rem;">/ 5</small></b><span><?= $estrellas($prom) ?> promedio general</span></div>
        <div><b><?= $n ?></b><span>calificaciones</span></div>
        <div><b><?= round($buenas * 100 / $n) ?>%</b><span>4 o 5 estrellas</span></div>
    </div>
    <div class="sa-grid">
        <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-chart-bar"></i> Distribución</h5></div><div class="hd-card-body">
            <?php for ($i = 5; $i >= 1; $i--): ?><div class="sa-fila"><span style="width:34px;"><?= $i ?> ★</span><div class="sa-bar"><i style="width:<?= round($d[$i] * 100 / $max) ?>%"></i></div><span style="width:30px;text-align:right;"><?= $d[$i] ?></span></div><?php endfor; ?>
        </div></div>
        <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-user-gear"></i> Por técnico</h5></div><div class="hd-card-body" style="padding:0;">
            <table class="hd-table"><thead><tr><th>Técnico</th><th>Calif.</th><th>Promedio</th></tr></thead><tbody>
            <?php foreach ($porTec ?? [] as $r): ?><tr><td><?= $h($r['nombre']) ?></td><td><?= (int)$r['n'] ?></td><td style="color:<?= $color($r['prom']) ?>;font-weight:600;"><?= number_format((float)$r['prom'], 2, ',', '') ?> <span style="color:#f59e0b;"><?= $estrellas($r['prom']) ?></span></td></tr>
            <?php endforeach; if (!$porTec) echo '<tr><td colspan="3" style="color:var(--muted);">Sin datos.</td></tr>'; ?></tbody></table></div></div>
        <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-tags"></i> Por categoría</h5></div><div class="hd-card-body" style="padding:0;">
            <table class="hd-table"><thead><tr><th>Categoría</th><th>Calif.</th><th>Promedio</th></tr></thead><tbody>
            <?php foreach ($porCat ?? [] as $r): ?><tr><td><?= $h($r['nombre']) ?></td><td><?= (int)$r['n'] ?></td><td style="color:<?= $color($r['prom']) ?>;font-weight:600;"><?= number_format((float)$r['prom'], 2, ',', '') ?></td></tr>
            <?php endforeach; if (!$porCat) echo '<tr><td colspan="3" style="color:var(--muted);">Sin datos.</td></tr>'; ?></tbody></table></div></div>
    </div>
    <div class="hd-card"><div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-comments"></i> Últimos comentarios</h5></div><div class="hd-card-body">
        <?php foreach ($coment ?? [] as $r): ?>
            <div style="padding:8px 0;border-bottom:1px solid var(--borde);font-size:.82rem;">
                <span style="color:#f59e0b;"><?= $estrellas($r['puntuacion']) ?></span>
                <a href="ver_ticket.php?id=<?= (int)$r['id_tickets'] ?>" style="margin-left:6px;"><?= $h($r['codigo_ticket']) ?></a>
                <span style="color:var(--muted);font-size:.72rem;margin-left:6px;"><?= $h(date('d/m/Y', strtotime($r['fecha']))) ?></span>
                <div>«<?= $h($r['comentario']) ?>»</div></div>
        <?php endforeach; if (!$coment) echo '<div style="color:var(--muted);">Nadie dejó comentarios en este período.</div>'; ?>
    </div></div>
<?php endif; ?>
</main>
<?php include_once '../includes/footer2.php'; ?>

<?php
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/constants.php';
include '../config/conexion.php';

// =====================================================================
// 1. VOLUMEN DE TICKETS (últimos 6 meses, creados vs resueltos)
// =====================================================================
$meses_labels = [];
$meses_creados = [];
$meses_resueltos = [];
$mapa_creados = [];
$mapa_resueltos = [];

$r1 = $conexion->query("
    SELECT DATE_FORMAT(fecha_creacion, '%Y-%m') AS mes, COUNT(*) AS total
    FROM tickets
    WHERE fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes
");
while ($row = $r1->fetch_assoc()) $mapa_creados[$row['mes']] = (int)$row['total'];

$r2 = $conexion->query("
    SELECT DATE_FORMAT(fecha_cierre, '%Y-%m') AS mes, COUNT(*) AS total
    FROM tickets
    WHERE fecha_cierre IS NOT NULL AND fecha_cierre >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes
");
while ($row = $r2->fetch_assoc()) $mapa_resueltos[$row['mes']] = (int)$row['total'];

$meses_es = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("-$i months");
    $key = date('Y-m', $ts);
    $meses_labels[] = $meses_es[(int)date('n', $ts)] . ' ' . date('y', $ts);
    $meses_creados[] = $mapa_creados[$key] ?? 0;
    $meses_resueltos[] = $mapa_resueltos[$key] ?? 0;
}

// =====================================================================
// 2. RENDIMIENTO POR TÉCNICO
// =====================================================================
$tecnicos_perf = $conexion->query("
    SELECT u.id_usuario, u.nombres, u.apellidos,
           COUNT(CASE WHEN t.id_estado IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ") THEN 1 END) AS resueltos,
           COUNT(CASE WHEN t.id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ") AND t.id_tickets IS NOT NULL THEN 1 END) AS activos,
           ROUND(AVG(CASE WHEN t.fecha_cierre IS NOT NULL THEN TIMESTAMPDIFF(HOUR, t.fecha_creacion, t.fecha_cierre) END), 1) AS horas_promedio
    FROM usuarios u
    LEFT JOIN tickets t ON t.id_usuario_asignado = u.id_usuario
    WHERE u.id_rol = " . ROL_TECNICO . " AND u.estado = 'activo'
    GROUP BY u.id_usuario
    ORDER BY resueltos DESC
");

// =====================================================================
// 3. CUMPLIMIENTO DE SLA POR PRIORIDAD
// =====================================================================
$sla_labels = [];
$sla_porcentajes = [];
$sla_detalle = [];

$r3 = $conexion->query("
    SELECT p.id_prioridad, p.nombre, p.nivel, p.tiempo_respuesta_objetivo,
           COUNT(t.id_tickets) AS total_resueltos,
           SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, t.fecha_creacion, t.fecha_cierre) <= p.tiempo_respuesta_objetivo THEN 1 ELSE 0 END) AS dentro_sla
    FROM prioridades p
    LEFT JOIN tickets t ON t.id_prioridad = p.id_prioridad AND t.fecha_cierre IS NOT NULL
    GROUP BY p.id_prioridad
    ORDER BY p.nivel ASC
");
while ($row = $r3->fetch_assoc()) {
    $total = (int)$row['total_resueltos'];
    $dentro = (int)$row['dentro_sla'];
    $pct = $total > 0 ? round(($dentro / $total) * 100) : null;
    $sla_labels[] = $row['nombre'];
    $sla_porcentajes[] = $pct ?? 0;
    $sla_detalle[] = ['nombre' => $row['nombre'], 'total' => $total, 'dentro' => $dentro, 'pct' => $pct];
}

// =====================================================================
// 4. TICKETS POR CATEGORÍA Y POR DEPARTAMENTO
// =====================================================================
$cat_labels = [];
$cat_valores = [];
$r4 = $conexion->query("
    SELECT c.nombre, COUNT(t.id_tickets) AS total
    FROM categoria_incidencias c
    LEFT JOIN tickets t ON t.id_categoria = c.id_categoria
    GROUP BY c.id_categoria
    ORDER BY total DESC
");
while ($row = $r4->fetch_assoc()) { $cat_labels[] = $row['nombre']; $cat_valores[] = (int)$row['total']; }

$dep_labels = [];
$dep_valores = [];
$r5 = $conexion->query("
    SELECT d.nombre, COUNT(t.id_tickets) AS total
    FROM departamentos d
    LEFT JOIN tickets t ON t.id_departamento = d.id_departamento
    GROUP BY d.id_departamento
    ORDER BY total DESC
");
while ($row = $r5->fetch_assoc()) { $dep_labels[] = $row['nombre']; $dep_valores[] = (int)$row['total']; }

// =====================================================================
// STATS RÁPIDAS DE ARRIBA
// =====================================================================
$total_tickets = $conexion->query("SELECT COUNT(*) t FROM tickets")->fetch_assoc()['t'];
$total_resueltos = $conexion->query("SELECT COUNT(*) t FROM tickets WHERE id_estado IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")")->fetch_assoc()['t'];
$total_abiertos = $conexion->query("SELECT COUNT(*) t FROM tickets WHERE id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")")->fetch_assoc()['t'];
$sla_global_row = $conexion->query("
    SELECT COUNT(*) total, SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, t.fecha_creacion, t.fecha_cierre) <= p.tiempo_respuesta_objetivo THEN 1 ELSE 0 END) dentro
    FROM tickets t JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    WHERE t.fecha_cierre IS NOT NULL
")->fetch_assoc();
$sla_global = ($sla_global_row['total'] > 0) ? round(($sla_global_row['dentro'] / $sla_global_row['total']) * 100) : null;

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

<style>
    .hd-reportes-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 18px; }
    @media (max-width: 1000px) { .hd-reportes-grid { grid-template-columns: 1fr; } }
    .hd-chart-card { padding: 18px 20px; }
    .hd-chart-card h5 { margin-bottom: 4px; }
    .hd-chart-sub { font-size: 0.76rem; color: var(--muted); margin-bottom: 14px; }
    .hd-chart-wrap { position: relative; height: 260px; }
    .hd-tabla-mini { width: 100%; font-size: 0.78rem; margin-top: 10px; }
    .hd-tabla-mini th { text-align: left; color: var(--muted); font-weight: 600; padding: 6px 8px; border-bottom: 1px solid var(--borde); }
    .hd-tabla-mini td { padding: 7px 8px; border-bottom: 1px solid var(--borde); }
    .hd-tabla-mini tr:last-child td { border-bottom: none; }
    .hd-pill-sla { padding: 2px 9px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-chart-bar" style="color:var(--naranja);margin-right:6px;"></i> Reportes</div>
            <div class="hd-page-sub">Vista general del desempeño del sistema de tickets</div>
        </div>
    </div>

    <!-- STATS RÁPIDAS -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:6px;">
        <div class="hd-card" style="padding:16px 18px;">
            <div style="font-size:0.72rem;color:var(--muted);font-weight:600;">TOTAL TICKETS</div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--azul);margin-top:4px;"><?= $total_tickets ?></div>
        </div>
        <div class="hd-card" style="padding:16px 18px;">
            <div style="font-size:0.72rem;color:var(--muted);font-weight:600;">RESUELTOS</div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--success);margin-top:4px;"><?= $total_resueltos ?></div>
        </div>
        <div class="hd-card" style="padding:16px 18px;">
            <div style="font-size:0.72rem;color:var(--muted);font-weight:600;">ACTIVOS</div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--naranja);margin-top:4px;"><?= $total_abiertos ?></div>
        </div>
        <div class="hd-card" style="padding:16px 18px;">
            <div style="font-size:0.72rem;color:var(--muted);font-weight:600;">SLA GLOBAL CUMPLIDO</div>
            <div style="font-size:1.6rem;font-weight:700;color:<?= $sla_global !== null && $sla_global < 80 ? 'var(--danger)' : 'var(--success)' ?>;margin-top:4px;">
                <?= $sla_global !== null ? $sla_global . '%' : '—' ?>
            </div>
        </div>
    </div>

    <div class="hd-reportes-grid">

        <!-- 1. VOLUMEN DE TICKETS -->
        <div class="hd-card hd-chart-card">
            <h5>Volumen de Tickets</h5>
            <div class="hd-chart-sub">Creados vs. resueltos por mes (últimos 6 meses)</div>
            <div class="hd-chart-wrap"><canvas id="chartVolumen"></canvas></div>
        </div>

        <!-- 2. RENDIMIENTO POR TÉCNICO -->
        <div class="hd-card hd-chart-card">
            <h5>Rendimiento por Técnico</h5>
            <div class="hd-chart-sub">Tickets resueltos y tiempo promedio de resolución</div>
            <div class="hd-chart-wrap" style="height:auto;">
                <table class="hd-tabla-mini">
                    <thead>
                        <tr><th>Técnico</th><th>Resueltos</th><th>Activos</th><th>Tiempo prom.</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($tecnicos_perf->num_rows === 0): ?>
                        <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:14px;">No hay técnicos activos.</td></tr>
                        <?php endif; ?>
                        <?php while ($tp = $tecnicos_perf->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight:600;color:var(--azul);"><?= htmlspecialchars($tp['nombres'] . ' ' . $tp['apellidos']) ?></td>
                            <td><?= $tp['resueltos'] ?></td>
                            <td><?= $tp['activos'] ?></td>
                            <td><?= $tp['horas_promedio'] !== null ? $tp['horas_promedio'] . 'h' : '—' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. SLA POR PRIORIDAD -->
        <div class="hd-card hd-chart-card">
            <h5>Cumplimiento de SLA por Prioridad</h5>
            <div class="hd-chart-sub">% de tickets resueltos dentro del tiempo objetivo</div>
            <div class="hd-chart-wrap"><canvas id="chartSla"></canvas></div>
            <table class="hd-tabla-mini">
                <thead><tr><th>Prioridad</th><th>Resueltos</th><th>Dentro de SLA</th><th>%</th></tr></thead>
                <tbody>
                    <?php foreach ($sla_detalle as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['nombre']) ?></td>
                        <td><?= $s['total'] ?></td>
                        <td><?= $s['dentro'] ?></td>
                        <td>
                            <?php if ($s['pct'] === null): ?>
                            <span style="color:var(--muted);">—</span>
                            <?php else: ?>
                            <span class="hd-pill-sla" style="background:<?= $s['pct'] >= 80 ? 'rgba(22,163,74,0.12)' : 'rgba(220,38,38,0.12)' ?>;color:<?= $s['pct'] >= 80 ? 'var(--success)' : 'var(--danger)' ?>;"><?= $s['pct'] ?>%</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 4. CATEGORÍA / DEPARTAMENTO -->
        <div class="hd-card hd-chart-card">
            <h5>Tickets por Categoría y Departamento</h5>
            <div class="hd-chart-sub">Qué falla más y en dónde</div>
            <div class="hd-chart-wrap"><canvas id="chartCategoria"></canvas></div>
            <div class="hd-chart-wrap" style="margin-top:16px;"><canvas id="chartDepartamento"></canvas></div>
        </div>

    </div>

</main>

<script>
const coloresLista = ['#E87722', '#1a2a4a', '#0284c7', '#16a34a', '#d97706', '#dc2626', '#64748b', '#a855f7'];

new Chart(document.getElementById('chartVolumen'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($meses_labels) ?>,
        datasets: [
            { label: 'Creados', data: <?= json_encode($meses_creados) ?>, backgroundColor: '#1a2a4a', borderRadius: 4 },
            { label: 'Resueltos', data: <?= json_encode($meses_resueltos) ?>, backgroundColor: '#E87722', borderRadius: 4 }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

new Chart(document.getElementById('chartSla'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($sla_labels) ?>,
        datasets: [{
            label: '% dentro de SLA',
            data: <?= json_encode($sla_porcentajes) ?>,
            backgroundColor: <?= json_encode($sla_porcentajes) ?>.map(v => v >= 80 ? '#16a34a' : (v >= 50 ? '#d97706' : '#dc2626')),
            borderRadius: 4
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } }
    }
});

new Chart(document.getElementById('chartCategoria'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($cat_labels) ?>,
        datasets: [{ label: 'Tickets', data: <?= json_encode($cat_valores) ?>, backgroundColor: coloresLista, borderRadius: 4 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false }, title: { display: true, text: 'Por categoría', font: { size: 11 }, color: '#64748b' } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

new Chart(document.getElementById('chartDepartamento'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($dep_labels) ?>,
        datasets: [{ label: 'Tickets', data: <?= json_encode($dep_valores) ?>, backgroundColor: coloresLista, borderRadius: 4 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false }, title: { display: true, text: 'Por departamento', font: { size: 11 }, color: '#64748b' } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
</script>

<?php include_once '../includes/footer2.php'; ?>

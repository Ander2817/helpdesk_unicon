<?php
// admin/calendario_mantenimientos.php — Calendario mensual de mantenimientos preventivos (fecha «proximo» del último mantenimiento de cada equipo).
// Lo usan administradores y técnicos con permiso 'inventario.ver' (el técnico entra por tecnico/calendario_mantenimientos.php).
session_start();
if (!isset($_SESSION['usser'])) { header("Location: ../index.php"); exit(); }
$rol = (int)$_SESSION['id_rol'];
require_once '../includes/constants.php';
require_once '../includes/functions.php';
include '../config/conexion.php';
if ($rol !== 3) {
    require_once '../includes/permisos.php';
    if ($rol !== 2 || !rol_tiene_permiso($conexion, $rol, 'inventario.ver')) { header("Location: ../index.php"); exit(); }
}
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$pagina_actual = 'calendario_mantenimientos.php';
$carpeta = $rol === 3 ? 'admin' : 'tecnico';

$mes = $_GET['mes'] ?? date('Y-m');
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) $mes = date('Y-m');
$ini = $mes . '-01'; $fin = date('Y-m-t', strtotime($ini));

// Último mantenimiento de cada equipo (no inactivo) con fecha próxima definida
$base = "SELECT m.id_mantenimiento, m.id_inventario, m.tipo, m.fecha AS ultimo, m.proximo, i.codigo_equipo, i.tipo_equipo, i.marca, i.modelo, i.ubicacion, d.nombre AS depto
         FROM mantenimientos_equipo m
         JOIN inventario_tecnologico i ON i.id_inventario = m.id_inventario
         LEFT JOIN departamentos d ON d.id_departamento = i.id_departamento
         WHERE m.proximo IS NOT NULL AND i.estado <> 'inactivo'
           AND m.id_mantenimiento = (SELECT m2.id_mantenimiento FROM mantenimientos_equipo m2 WHERE m2.id_inventario = m.id_inventario ORDER BY m2.fecha DESC, m2.id_mantenimiento DESC LIMIT 1)";

if (isset($_GET['ics'])) {      // exportar a Google Calendar / Outlook
    $r = $conexion->query($base . " ORDER BY m.proximo LIMIT 1000")->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: text/calendar; charset=utf-8'); header('Content-Disposition: attachment; filename="mantenimientos.ics"');
    echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//HelpDesk Unicon//ES\r\nCALSCALE:GREGORIAN\r\n";
    foreach ($r as $m) {
        $d = date('Ymd', strtotime($m['proximo'])); $d2 = date('Ymd', strtotime($m['proximo'] . ' +1 day'));
        $t = fn($s) => str_replace([",", ";", "\n", "\r"], ["\\,", "\;", " ", ""], $s);
        echo "BEGIN:VEVENT\r\nUID:mant{$m['id_mantenimiento']}@helpdesk-unicon\r\nDTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\nDTSTART;VALUE=DATE:$d\r\nDTEND;VALUE=DATE:$d2\r\n"
           . "SUMMARY:" . $t('Mantenimiento ' . $m['codigo_equipo'] . ' (' . $m['tipo_equipo'] . ')') . "\r\nLOCATION:" . $t(($m['depto'] ?? '') . ' ' . ($m['ubicacion'] ?? '')) . "\r\nEND:VEVENT\r\n";
    }
    echo "END:VCALENDAR\r\n"; exit();
}

$st = $conexion->prepare($base . " AND m.proximo BETWEEN ? AND ? ORDER BY m.proximo, i.codigo_equipo");
$st->bind_param("ss", $ini, $fin); $st->execute(); $del_mes = $st->get_result()->fetch_all(MYSQLI_ASSOC); $st->close();
$por_dia = []; foreach ($del_mes as $m) $por_dia[(int)date('j', strtotime($m['proximo']))][] = $m;
$vencidos = $conexion->query($base . " AND m.proximo < CURDATE() ORDER BY m.proximo LIMIT 100")->fetch_all(MYSQLI_ASSOC);

$meses = [1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$ts = strtotime($ini); $dias_mes = (int)date('t', $ts); $offset = (int)date('N', $ts) - 1;   // lunes = 0
$prev = date('Y-m', strtotime("$ini -1 month")); $next = date('Y-m', strtotime("$ini +1 month"));
$hoy = date('Y-m-d');
include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>
<style>
 .cal{display:grid;grid-template-columns:repeat(7,1fr);gap:4px}
 .cal .cab{font-size:.75rem;font-weight:700;text-align:center;color:#64748b;padding:4px}
 .cal .dia{min-height:92px;border:1px solid #e2e8f0;border-radius:8px;padding:4px;background:#fff;font-size:.75rem;overflow:hidden}
 .cal .dia.hoy{border:2px solid #E87722}.cal .dia.vacio{background:transparent;border:0}
 .cal .num{font-weight:700;color:#475569}
 .cal .ev{display:block;margin-top:2px;padding:1px 5px;border-radius:5px;background:#E8772222;color:#9a4a0b;text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
 .cal .ev.venc{background:#dc262622;color:#b91c1c}
 @media (prefers-color-scheme:dark){.cal .dia{background:#1e293b;border-color:#334155}.cal .num{color:#cbd5e1}.cal .ev{color:#fdba74}.cal .ev.venc{color:#fca5a5}}
 @media (max-width:700px){.cal .dia{min-height:60px}.cal .ev{font-size:.65rem}}
</style>
<main class="hd-main">
  <div class="hd-page-header"><div><h1 class="hd-page-title"><i class="fa-solid fa-calendar-days me-2"></i>Calendario de mantenimientos</h1>
    <p class="hd-page-sub">Próximo mantenimiento preventivo de cada equipo, según el último registrado.</p></div>
    <a class="hd-btn hd-btn-outline" href="?ics=1"><i class="fa-solid fa-download me-1"></i>Exportar .ics</a></div>

  <?php if ($vencidos): ?>
  <div class="hd-card mb-3" style="border-left:4px solid #dc2626"><div class="hd-card-header"><span class="hd-card-title" style="color:#dc2626"><i class="fa-solid fa-triangle-exclamation me-1"></i>Mantenimientos vencidos (<?= count($vencidos) ?>)</span></div>
    <div class="hd-card-body" style="overflow:auto"><table class="hd-table"><thead><tr><th>Equipo</th><th>Departamento</th><th>Debía hacerse</th><th>Atraso</th></tr></thead><tbody>
    <?php foreach ($vencidos as $m): $dias = (int)floor((strtotime($hoy) - strtotime($m['proximo'])) / 86400); ?>
      <tr><td><a href="../<?= $carpeta ?>/inventario.php"><?= $h($m['codigo_equipo']) ?></a> · <?= $h($m['tipo_equipo']) ?></td><td><?= $h($m['depto']) ?></td><td><?= $h(date('d/m/Y', strtotime($m['proximo']))) ?></td><td><?= $dias ?> día<?= $dias === 1 ? '' : 's' ?></td></tr>
    <?php endforeach; ?></tbody></table></div></div>
  <?php endif; ?>

  <div class="hd-card"><div class="hd-card-header">
      <a class="hd-btn hd-btn-outline" href="?mes=<?= $prev ?>"><i class="fa-solid fa-chevron-left"></i></a>
      <span class="hd-card-title"><?= $meses[(int)date('n', $ts)] . ' ' . date('Y', $ts) ?> · <?= count($del_mes) ?> programado(s)</span>
      <span><a class="hd-btn hd-btn-outline" href="?mes=<?= date('Y-m') ?>">Hoy</a> <a class="hd-btn hd-btn-outline" href="?mes=<?= $next ?>"><i class="fa-solid fa-chevron-right"></i></a></span></div>
    <div class="hd-card-body"><div class="cal">
      <?php foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $n): ?><div class="cab"><?= $n ?></div><?php endforeach; ?>
      <?php for ($i = 0; $i < $offset; $i++): ?><div class="dia vacio"></div><?php endfor; ?>
      <?php for ($d = 1; $d <= $dias_mes; $d++): $f = sprintf('%s-%02d', $mes, $d); ?>
        <div class="dia <?= $f === $hoy ? 'hoy' : '' ?>"><span class="num"><?= $d ?></span>
          <?php foreach (($por_dia[$d] ?? []) as $m): ?>
            <a class="ev <?= $f < $hoy ? 'venc' : '' ?>" href="../<?= $carpeta ?>/inventario.php" title="<?= $h($m['codigo_equipo'] . ' · ' . $m['tipo_equipo'] . ' · ' . $m['depto']) ?>"><?= $h($m['codigo_equipo']) ?></a>
          <?php endforeach; ?></div>
      <?php endfor; ?>
    </div></div></div>
</main>
<?php include_once '../includes/footer2.php'; ?>

<?php
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] !== 3) {
    header("Location: ../index.php"); exit();
}
include '../config/conexion.php';

// Filtro opcional por código de ticket (?buscar=TK-2026-0001)
$buscar = trim($_GET['buscar'] ?? '');

$sql = "
    SELECT h.id_historial, h.campo_modificado, h.valor_anterior, h.valor_nuevo, h.fecha_cambio,
           t.id_tickets, t.codigo_ticket, t.asunto,
           u.nombres, u.apellidos, u.id_rol
    FROM historial_ticket h
    JOIN tickets t ON h.id_ticket = t.id_tickets
    JOIN usuarios u ON h.id_usuario = u.id_usuario
";

if ($buscar !== '') {
    $stmt = $conexion->prepare($sql . " WHERE t.codigo_ticket LIKE ? ORDER BY h.fecha_cambio DESC LIMIT 300");
    $like = '%' . $buscar . '%';
    $stmt->bind_param("s", $like);
} else {
    $stmt = $conexion->prepare($sql . " ORDER BY h.fecha_cambio DESC LIMIT 300");
}
$stmt->execute();
$historial = $stmt->get_result();

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<style>
    .hd-historial-fila { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--borde); }
    .hd-historial-fila:last-child { border-bottom: none; }
    .hd-historial-avatar {
        width: 30px; height: 30px; border-radius: 50%; background: var(--azul); color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
    }
    .hd-historial-avatar.rol-2 { background: #d97706; }
    .hd-historial-avatar.rol-3 { background: var(--naranja); }
    .hd-historial-ticket { font-size: 0.72rem; font-weight: 600; color: var(--naranja); text-decoration: none; }
    .hd-historial-ticket:hover { text-decoration: underline; }
    .hd-historial-texto { font-size: 0.82rem; color: var(--texto); margin-top: 2px; }
    .hd-historial-fecha { font-size: 0.68rem; color: var(--muted); margin-top: 2px; }
    .hd-buscador { max-width: 340px; margin-bottom: 16px; }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-history" style="color:var(--naranja);margin-right:6px;"></i> Historial de Tickets</div>
            <div class="hd-page-sub">Registro de auditoría de todos los cambios en el sistema (últimos 300)</div>
        </div>
    </div>

    <form method="get" class="hd-buscador">
        <div style="display:flex;gap:8px;">
            <input type="text" name="buscar" class="hd-input" placeholder="Buscar por código de ticket (ej. TK-2026-0001)" value="<?= htmlspecialchars($buscar) ?>">
            <button type="submit" class="hd-btn hd-btn-outline"><i class="fas fa-search"></i></button>
            <?php if ($buscar !== ''): ?>
            <a href="tickets_historial.php" class="hd-btn hd-btn-outline"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </div>
    </form>

    <div class="hd-card" style="padding:18px 22px;">
        <?php if ($historial->num_rows === 0): ?>
        <div style="text-align:center;color:var(--muted);padding:24px;">No hay registros que coincidan.</div>
        <?php endif; ?>
        <?php while ($h = $historial->fetch_assoc()): ?>
        <div class="hd-historial-fila">
            <div class="hd-historial-avatar rol-<?= $h['id_rol'] ?>"><?= strtoupper(substr($h['nombres'], 0, 1)) ?></div>
            <div style="flex:1;">
                <a href="ver_ticket.php?id=<?= $h['id_tickets'] ?>" class="hd-historial-ticket">#<?= htmlspecialchars($h['codigo_ticket']) ?></a>
                <span style="color:var(--muted);font-size:0.72rem;"> — <?= htmlspecialchars($h['asunto']) ?></span>
                <div class="hd-historial-texto">
                    <strong><?= htmlspecialchars($h['nombres'] . ' ' . $h['apellidos']) ?></strong>
                    cambió <strong><?= htmlspecialchars($h['campo_modificado']) ?></strong>
                    <?php if ($h['valor_anterior']): ?> de "<?= htmlspecialchars($h['valor_anterior']) ?>"<?php endif; ?>
                    a "<?= htmlspecialchars($h['valor_nuevo']) ?>"
                </div>
                <div class="hd-historial-fecha"><?= date('d/m/Y H:i', strtotime($h['fecha_cambio'])) ?></div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

</main>

<?php include_once '../includes/footer2.php'; ?>

<?php
if (!defined('TICKET_ABIERTO')) require_once __DIR__ . '/../config/constants.php';

// ===== CONTADORES REALES DEL SIDEBAR =====
// Se calculan con una sola consulta por rol. Si un contador vale 0 no se dibuja la burbuja.
if (!function_exists('sb_badge')) {
    function sb_badge(int $n, string $clase = ''): string {
        if ($n <= 0) return '';
        return '<span class="hd-sidebar-count' . ($clase !== '' ? ' ' . $clase : '') . '">' . $n . '</span>';
    }
}

$sb = [];

// ID numérico del usuario en sesión (la sesión solo guarda el login)
$stmt_sb = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
$stmt_sb->bind_param("s", $usuario_activo);
$stmt_sb->execute();
$fila_sb = $stmt_sb->get_result()->fetch_assoc();
$stmt_sb->close();
$sb_id_usuario = (int)($fila_sb['id_usuario'] ?? 0);

// Tickets que esta persona creó y siguen activos (para "Mis Solicitudes" de admin y técnico)
$stmt_sb = $conexion->prepare("SELECT COUNT(*) AS c FROM tickets WHERE id_usuario_solicitante = ? AND id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")");
$stmt_sb->bind_param("i", $sb_id_usuario);
$stmt_sb->execute();
$sb['mis_sol'] = (int)$stmt_sb->get_result()->fetch_assoc()['c'];
$stmt_sb->close();

if ($rol_id == ROL_ADMIN) {
    $r = $conexion->query("
        SELECT COALESCE(SUM(id_estado = " . TICKET_ABIERTO . "), 0)   AS abiertos,
               COALESCE(SUM(id_estado = " . TICKET_PROCESO . "), 0)   AS proceso,
               COALESCE(SUM(id_estado = " . TICKET_PENDIENTE . "), 0) AS pendientes
        FROM tickets
    ")->fetch_assoc();
    $sb['abiertos']   = (int)$r['abiertos'];
    $sb['proceso']    = (int)$r['proceso'];
    $sb['pendientes'] = (int)$r['pendientes'];
    $sb['activos']    = $sb['abiertos'] + $sb['proceso'] + $sb['pendientes'];
    // Tickets activos que todavía esperan técnico: es lo que el administrador debe atender primero
    $sb['sin_asignar'] = (int)$conexion->query("SELECT COUNT(*) c FROM tickets WHERE id_usuario_asignado IS NULL AND id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")")->fetch_assoc()['c'];
    $sb['usuarios']   = (int)$conexion->query("SELECT COUNT(*) c FROM usuarios")->fetch_assoc()['c'];
    $sb['inventario'] = (int)$conexion->query("SELECT COUNT(*) c FROM inventario_tecnologico")->fetch_assoc()['c'];

} elseif ($rol_id == ROL_TECNICO) {
    $stmt_sb = $conexion->prepare("
        SELECT COALESCE(SUM(id_usuario_asignado = ? AND id_estado IN (" . TICKET_PROCESO . "," . TICKET_PENDIENTE . ")), 0) AS asignados,
               COALESCE(SUM(id_usuario_asignado = ? AND id_estado = " . TICKET_PENDIENTE . "), 0)                                AS pendientes
        FROM tickets
    ");
    $stmt_sb->bind_param("ii", $sb_id_usuario, $sb_id_usuario);
    $stmt_sb->execute();
    $r = $stmt_sb->get_result()->fetch_assoc();
    $stmt_sb->close();
    $sb['asignados']   = (int)$r['asignados'];
    $sb['pendientes']  = (int)$r['pendientes'];

} else {
    $stmt_sb = $conexion->prepare("
        SELECT COUNT(*) AS total,
               COALESCE(SUM(id_estado = " . TICKET_ABIERTO . "), 0) AS abiertos,
               COALESCE(SUM(id_estado = " . TICKET_PROCESO . "), 0) AS proceso,
               COALESCE(SUM(id_estado IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . ")), 0) AS resueltos
        FROM tickets WHERE id_usuario_solicitante = ?
    ");
    $stmt_sb->bind_param("i", $sb_id_usuario);
    $stmt_sb->execute();
    $r = $stmt_sb->get_result()->fetch_assoc();
    $stmt_sb->close();
    $sb['total']    = (int)$r['total'];
    $sb['abiertos'] = (int)$r['abiertos'];
    $sb['proceso']  = (int)$r['proceso'];
    $sb['resueltos'] = (int)$r['resueltos'];
}
?>
<!-- ===== SIDEBAR ===== -->
<aside class="hd-sidebar" id="sidebar">

    <?php if ($rol_id == 3): ?>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Principal</div>
        <a href="dashboard.php" class="hd-sidebar-link <?= $pagina_actual=='dashboard.php'?'active':'' ?>">
            <i class="fas fa-chart-line"></i> Panel General
        </a>
    </div>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Tickets</div>
        <a href="tickets.php" class="hd-sidebar-link <?= ($pagina_actual=='tickets.php' && ($_GET['estado'] ?? '') !== 'sin_asignar') ? 'active' : '' ?>">
            <i class="fas fa-inbox"></i> Todos los Tickets
            <?= sb_badge($sb['activos']) ?>
        </a>
        <a href="tickets.php?estado=sin_asignar" class="hd-sidebar-link <?= ($pagina_actual=='tickets.php' && ($_GET['estado'] ?? '') === 'sin_asignar') ? 'active' : '' ?>">
            <i class="fas fa-user-clock"></i> Sin Asignar
            <?= sb_badge($sb['sin_asignar'], 'naranja') ?>
        </a>
        <button class="hd-sidebar-link" onclick="toggleSub('sub-tickets')">
            <i class="fas fa-filter"></i> Por Estado
            <i class="fas fa-chevron-right hd-sidebar-chevron"></i>
        </button>
        <div class="hd-submenu" id="sub-tickets">
            <a href="tickets.php?estado=abierto" class="hd-sidebar-link"><i class="fas fa-circle" style="color:#0284c7;font-size:0.5rem;"></i> Abiertos <?= sb_badge($sb['abiertos']) ?></a>
            <a href="tickets.php?estado=proceso" class="hd-sidebar-link"><i class="fas fa-circle" style="color:var(--naranja);font-size:0.5rem;"></i> En Proceso <?= sb_badge($sb['proceso']) ?></a>
            <a href="tickets.php?estado=pendiente" class="hd-sidebar-link"><i class="fas fa-circle" style="color:var(--warning);font-size:0.5rem;"></i> Pendientes <?= sb_badge($sb['pendientes']) ?></a>
            <a href="tickets.php?estado=resuelto" class="hd-sidebar-link"><i class="fas fa-circle" style="color:var(--success);font-size:0.5rem;"></i> Resueltos</a>
        </div>
        <a href="tickets_historial.php" class="hd-sidebar-link <?= $pagina_actual=='tickets_historial.php'?'active':'' ?>">
            <i class="fas fa-history"></i> Historial
        </a>
    </div>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Mis Solicitudes</div>
        <a href="tickets_crear.php" class="hd-sidebar-link <?= $pagina_actual=='tickets_crear.php'?'active':'' ?>">
            <i class="fas fa-plus-circle"></i> Crear Ticket
        </a>
        <a href="mis_tickets.php" class="hd-sidebar-link <?= $pagina_actual=='mis_tickets.php'?'active':'' ?>">
            <i class="fas fa-ticket-alt"></i> Mis Solicitudes
            <?= sb_badge($sb['mis_sol']) ?>
        </a>
    </div>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Administración</div>
        <a href="procesos/usuarios.php" class="hd-sidebar-link <?= $pagina_actual=='usuarios.php'?'active':'' ?>">
            <i class="fas fa-users"></i> Usuarios
            <?= sb_badge($sb['usuarios']) ?>
        </a>
        <a href="procesos/departamentos.php" class="hd-sidebar-link <?= $pagina_actual=='departamentos.php'?'active':'' ?>">
            <i class="fas fa-building"></i> Departamentos
        </a>
        <a href="roles.php" class="hd-sidebar-link <?= $pagina_actual=='roles.php'?'active':'' ?>">
            <i class="fas fa-shield-alt"></i> Roles
        </a>
        <button class="hd-sidebar-link" onclick="toggleSub('sub-config')">
            <i class="fas fa-cogs"></i> Configuración
            <i class="fas fa-chevron-right hd-sidebar-chevron"></i>
        </button>
        <div class="hd-submenu" id="sub-config">
            <a href="categorias.php" class="hd-sidebar-link"><i class="fas fa-tags"></i> Categorías</a>
            <a href="prioridades.php" class="hd-sidebar-link"><i class="fas fa-flag"></i> Prioridades</a>
            <a href="estados.php" class="hd-sidebar-link"><i class="fas fa-toggle-on"></i> Estados</a>
        </div>
    </div>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Recursos</div>
        <a href="inventario.php" class="hd-sidebar-link <?= $pagina_actual=='inventario.php'?'active':'' ?>">
            <i class="fas fa-server"></i> Inventario TI
            <?= sb_badge($sb['inventario']) ?>
        </a>
        <a href="reportes.php" class="hd-sidebar-link <?= $pagina_actual=='reportes.php'?'active':'' ?>">
            <i class="fas fa-chart-bar"></i> Reportes
        </a>
    </div>

    <?php elseif ($rol_id == 2): ?>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Principal</div>
        <a href="dashboard.php" class="hd-sidebar-link <?= $pagina_actual=='dashboard.php'?'active':'' ?>">
            <i class="fas fa-home"></i> Mi Panel
        </a>
    </div>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Mis Tickets</div>
        <a href="tickets_asignados.php" class="hd-sidebar-link <?= $pagina_actual=='tickets_asignados.php'?'active':'' ?>">
            <i class="fas fa-user-check"></i> Asignados a Mí
            <?= sb_badge($sb['asignados'], 'naranja') ?>
        </a>
        <a href="tickets_pendientes.php" class="hd-sidebar-link <?= $pagina_actual=='tickets_pendientes.php'?'active':'' ?>">
            <i class="fas fa-clock"></i> Pendientes
            <?= sb_badge($sb['pendientes']) ?>
        </a>
        <a href="tickets_resueltos.php" class="hd-sidebar-link <?= $pagina_actual=='tickets_resueltos.php'?'active':'' ?>">
            <i class="fas fa-check-circle"></i> Resueltos
        </a>
        <a href="historial.php" class="hd-sidebar-link <?= $pagina_actual=='historial.php'?'active':'' ?>">
            <i class="fas fa-history"></i> Mi Historial
        </a>
    </div>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Mis Solicitudes</div>
        <a href="tickets_crear.php" class="hd-sidebar-link <?= $pagina_actual=='tickets_crear.php'?'active':'' ?>">
            <i class="fas fa-plus-circle"></i> Crear Ticket
        </a>
        <a href="mis_tickets.php" class="hd-sidebar-link <?= $pagina_actual=='mis_tickets.php'?'active':'' ?>">
            <i class="fas fa-ticket-alt"></i> Mis Solicitudes
            <?= sb_badge($sb['mis_sol']) ?>
        </a>
    </div>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Recursos</div>
        <a href="inventario.php" class="hd-sidebar-link">
            <i class="fas fa-server"></i> Inventario TI
        </a>
    </div>

    <?php else: ?>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Principal</div>
        <a href="dashboard.php" class="hd-sidebar-link <?= $pagina_actual=='dashboard.php'?'active':'' ?>">
            <i class="fas fa-home"></i> Inicio
        </a>
    </div>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Mis Tickets</div>
        <a href="ticket_nuevo.php" class="hd-sidebar-link <?= $pagina_actual=='ticket_nuevo.php'?'active':'' ?>">
            <i class="fas fa-plus-circle"></i> Crear Ticket
        </a>
        <a href="mis_tickets.php" class="hd-sidebar-link <?= $pagina_actual=='mis_tickets.php'?'active':'' ?>">
            <i class="fas fa-ticket-alt"></i> Mis Tickets
            <?= sb_badge($sb['total']) ?>
        </a>
        <button class="hd-sidebar-link" onclick="toggleSub('sub-mis')">
            <i class="fas fa-filter"></i> Por Estado
            <i class="fas fa-chevron-right hd-sidebar-chevron"></i>
        </button>
        <div class="hd-submenu" id="sub-mis">
            <a href="mis_tickets.php?estado=abierto" class="hd-sidebar-link"><i class="fas fa-circle" style="color:#0284c7;font-size:0.5rem;"></i> Abiertos <?= sb_badge($sb['abiertos']) ?></a>
            <a href="mis_tickets.php?estado=proceso" class="hd-sidebar-link"><i class="fas fa-circle" style="color:var(--naranja);font-size:0.5rem;"></i> En Proceso <?= sb_badge($sb['proceso']) ?></a>
            <a href="mis_tickets.php?estado=resuelto" class="hd-sidebar-link"><i class="fas fa-circle" style="color:var(--success);font-size:0.5rem;"></i> Resueltos <?= sb_badge($sb['resueltos']) ?></a>
        </div>
    </div>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Mi Cuenta</div>
        <a href="perfil.php" class="hd-sidebar-link <?= $pagina_actual=='perfil.php'?'active':'' ?>">
            <i class="fas fa-user-edit"></i> Mi Perfil
        </a>
    </div>

    <?php endif; ?>

    <div class="hd-sidebar-footer">
        <a href="perfil.php" class="hd-sidebar-link <?= $pagina_actual=='perfil.php'?'active':'' ?>" style="margin-bottom:4px;">
            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($usuario_activo) ?>
        </a>
        <a href="../auth/logout.php" class="hd-sidebar-logout">
            <i class="fas fa-power-off"></i> Cerrar Sesión
        </a>
    </div>

</aside>

<!-- INICIO DEL CONTENIDO PRINCIPAL -->
<div class="hd-content">

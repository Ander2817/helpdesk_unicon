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
        <a href="tickets.php" class="hd-sidebar-link <?= $pagina_actual=='tickets.php'?'active':'' ?>">
            <i class="fas fa-inbox"></i> Todos los Tickets
            <span class="hd-sidebar-count naranja">4</span>
        </a>
        <a href="tickets_crear.php" class="hd-sidebar-link">
            <i class="fas fa-plus-circle"></i> Crear Ticket
        </a>
        <button class="hd-sidebar-link" onclick="toggleSub('sub-tickets')">
            <i class="fas fa-filter"></i> Por Estado
            <i class="fas fa-chevron-right hd-sidebar-chevron"></i>
        </button>
        <div class="hd-submenu" id="sub-tickets">
            <a href="tickets.php?estado=abierto" class="hd-sidebar-link"><i class="fas fa-circle" style="color:#0284c7;font-size:0.5rem;"></i> Abiertos <span class="hd-sidebar-count">6</span></a>
            <a href="tickets.php?estado=proceso" class="hd-sidebar-link"><i class="fas fa-circle" style="color:var(--naranja);font-size:0.5rem;"></i> En Proceso <span class="hd-sidebar-count">4</span></a>
            <a href="tickets.php?estado=pendiente" class="hd-sidebar-link"><i class="fas fa-circle" style="color:var(--warning);font-size:0.5rem;"></i> Pendientes <span class="hd-sidebar-count">2</span></a>
            <a href="tickets.php?estado=resuelto" class="hd-sidebar-link"><i class="fas fa-circle" style="color:var(--success);font-size:0.5rem;"></i> Resueltos</a>
        </div>
        <a href="tickets_historial.php" class="hd-sidebar-link">
            <i class="fas fa-history"></i> Historial
        </a>
    </div>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Administración</div>
        <a href="procesos/usuarios.php" class="hd-sidebar-link <?= $pagina_actual=='usuarios.php'?'active':'' ?>">
            <i class="fas fa-users"></i> Usuarios
            <span class="hd-sidebar-count">16</span>
        </a>
        <a href="procesos/departamentos.php" class="hd-sidebar-link">
            <i class="fas fa-building"></i> Departamentos
        </a>
        <a href="roles.php" class="hd-sidebar-link">
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
            <span class="hd-sidebar-count">47</span>
        </a>
        <a href="reportes.php" class="hd-sidebar-link">
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
            <i class="fas fa-inbox"></i> Asignados a Mí
            <span class="hd-sidebar-count naranja">7</span>
        </a>
        <a href="tickets_pendientes.php" class="hd-sidebar-link">
            <i class="fas fa-clock"></i> Pendientes
            <span class="hd-sidebar-count">2</span>
        </a>
        <a href="tickets_resueltos.php" class="hd-sidebar-link">
            <i class="fas fa-check-circle"></i> Resueltos
        </a>
        <a href="historial.php" class="hd-sidebar-link">
            <i class="fas fa-history"></i> Mi Historial
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
        <a href="ticket_nuevo.php" class="hd-sidebar-link">
            <i class="fas fa-plus-circle"></i> Crear Ticket
        </a>
        <a href="mis_tickets.php" class="hd-sidebar-link <?= $pagina_actual=='mis_tickets.php'?'active':'' ?>">
            <i class="fas fa-ticket-alt"></i> Mis Tickets
            <span class="hd-sidebar-count">5</span>
        </a>
        <button class="hd-sidebar-link" onclick="toggleSub('sub-mis')">
            <i class="fas fa-filter"></i> Por Estado
            <i class="fas fa-chevron-right hd-sidebar-chevron"></i>
        </button>
        <div class="hd-submenu" id="sub-mis">
            <a href="mis_tickets.php?estado=abierto" class="hd-sidebar-link"><i class="fas fa-circle" style="color:#0284c7;font-size:0.5rem;"></i> Abiertos <span class="hd-sidebar-count">1</span></a>
            <a href="mis_tickets.php?estado=proceso" class="hd-sidebar-link"><i class="fas fa-circle" style="color:var(--naranja);font-size:0.5rem;"></i> En Proceso <span class="hd-sidebar-count">2</span></a>
            <a href="mis_tickets.php?estado=resuelto" class="hd-sidebar-link"><i class="fas fa-circle" style="color:var(--success);font-size:0.5rem;"></i> Resueltos <span class="hd-sidebar-count">2</span></a>
        </div>
    </div>

    <div class="hd-sidebar-section">
        <div class="hd-sidebar-label">Mi Cuenta</div>
        <a href="perfil.php" class="hd-sidebar-link">
            <i class="fas fa-user-edit"></i> Mi Perfil
        </a>
    </div>

    <?php endif; ?>

    <div class="hd-sidebar-footer">
        <a href="perfil.php" class="hd-sidebar-link" style="margin-bottom:4px;">
            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($usuario_activo) ?>
        </a>
        <a href="../auth/logout.php" class="hd-sidebar-logout">
            <i class="fas fa-power-off"></i> Cerrar Sesión
        </a>
    </div>

</aside>

<!-- INICIO DEL CONTENIDO PRINCIPAL -->
<div class="hd-content">
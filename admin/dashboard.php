<?php include_once '../includes/header.php'; ?>

<main class="hd-main">

    <!-- ENCABEZADO DE PÁGINA -->
    <div class="hd-page-header">
        <div>
            <h2><i class="fas fa-chart-line" style="color:var(--naranja);margin-right:8px;"></i>Panel Administrativo</h2>
            <p>Bienvenido, <strong><?= htmlspecialchars($usuario_activo) ?></strong> — Vista general del sistema HelpDesk</p>
        </div>
        <span class="hd-rol-badge"><i class="fas fa-shield-alt"></i> Administrador</span>
    </div>

    <!-- ESTADÍSTICAS PRINCIPALES -->
    <div class="hd-stats hd-stats-4">
        <div class="hd-stat">
            <div class="hd-stat-icon rojo"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="hd-stat-num">04</div>
                <div class="hd-stat-label">Tickets Críticos</div>
            </div>
        </div>
        <div class="hd-stat">
            <div class="hd-stat-icon naranja"><i class="fas fa-spinner"></i></div>
            <div>
                <div class="hd-stat-num">12</div>
                <div class="hd-stat-label">En Proceso</div>
            </div>
        </div>
        <div class="hd-stat">
            <div class="hd-stat-icon verde"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="hd-stat-num">38</div>
                <div class="hd-stat-label">Resueltos este mes</div>
            </div>
        </div>
        <div class="hd-stat">
            <div class="hd-stat-icon azul"><i class="fas fa-users"></i></div>
            <div>
                <div class="hd-stat-num">16</div>
                <div class="hd-stat-label">Usuarios Activos</div>
            </div>
        </div>
    </div>

    <!-- SEGUNDA FILA DE STATS -->
    <div class="hd-stats hd-stats-4" style="margin-top:-8px;">
        <div class="hd-stat">
            <div class="hd-stat-icon celeste"><i class="fas fa-ticket-alt"></i></div>
            <div>
                <div class="hd-stat-num">54</div>
                <div class="hd-stat-label">Total Tickets</div>
            </div>
        </div>
        <div class="hd-stat">
            <div class="hd-stat-icon amarillo"><i class="fas fa-clock"></i></div>
            <div>
                <div class="hd-stat-num">2.4h</div>
                <div class="hd-stat-label">Tiempo Prom. Respuesta</div>
            </div>
        </div>
        <div class="hd-stat">
            <div class="hd-stat-icon verde"><i class="fas fa-server"></i></div>
            <div>
                <div class="hd-stat-num">47</div>
                <div class="hd-stat-label">Equipos en Inventario</div>
            </div>
        </div>
        <div class="hd-stat">
            <div class="hd-stat-icon naranja"><i class="fas fa-user-cog"></i></div>
            <div>
                <div class="hd-stat-num">03</div>
                <div class="hd-stat-label">Técnicos Activos</div>
            </div>
        </div>
    </div>

    <!-- FILA PRINCIPAL: Tabla + Accesos -->
    <div class="hd-grid-8-4" style="margin-bottom:20px;">

        <!-- TABLA DE TICKETS RECIENTES -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-list-ul"></i> Tickets Recientes</h5>
                <a href="tickets.php" class="hd-btn hd-btn-outline hd-btn-sm">Ver todos</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="hd-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asunto</th>
                            <th>Categoría</th>
                            <th>Departamento</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span style="font-family:monospace;font-size:0.78rem;color:var(--muted);">#TK-0001</span></td>
                            <td>
                                <div style="font-weight:500;">Falla de Red LAN</div>
                                <div style="font-size:0.72rem;color:var(--muted);">Báscula de Pesaje — Planta</div>
                            </td>
                            <td>Red e Internet</td>
                            <td>Manufactura</td>
                            <td><span class="hd-tag hd-tag-rojo"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Crítica</span></td>
                            <td><span class="hd-tag hd-tag-naranja">En Proceso</span></td>
                            <td>
                                <a href="ticket_ver.php?id=1" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td><span style="font-family:monospace;font-size:0.78rem;color:var(--muted);">#TK-0002</span></td>
                            <td>
                                <div style="font-weight:500;">PC no enciende</div>
                                <div style="font-size:0.72rem;color:var(--muted);">Área de Contabilidad</div>
                            </td>
                            <td>Hardware</td>
                            <td>Contabilidad</td>
                            <td><span class="hd-tag hd-tag-naranja"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Alta</span></td>
                            <td><span class="hd-tag hd-tag-azul">Abierto</span></td>
                            <td>
                                <a href="ticket_ver.php?id=2" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td><span style="font-family:monospace;font-size:0.78rem;color:var(--muted);">#TK-0003</span></td>
                            <td>
                                <div style="font-weight:500;">Impresora sin papel</div>
                                <div style="font-size:0.72rem;color:var(--muted);">Recursos Humanos</div>
                            </td>
                            <td>Impresoras</td>
                            <td>RRHH</td>
                            <td><span class="hd-tag hd-tag-gris">Baja</span></td>
                            <td><span class="hd-tag hd-tag-verde">Resuelto</span></td>
                            <td>
                                <a href="ticket_ver.php?id=3" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td><span style="font-family:monospace;font-size:0.78rem;color:var(--muted);">#TK-0004</span></td>
                            <td>
                                <div style="font-weight:500;">Acceso a sistema SAP</div>
                                <div style="font-size:0.72rem;color:var(--muted);">Abastecimiento</div>
                            </td>
                            <td>Accesos y Permisos</td>
                            <td>Abastecimiento</td>
                            <td><span class="hd-tag hd-tag-amarillo">Media</span></td>
                            <td><span class="hd-tag hd-tag-gris">Pendiente</span></td>
                            <td>
                                <a href="ticket_ver.php?id=4" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Accesos directos -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h5 class="hd-card-title"><i class="fas fa-bolt"></i> Accesos Directos</h5>
                </div>
                <div class="hd-card-body">
                    <div class="hd-quick-grid">
                        <a href="tickets_crear.php" class="hd-quick-btn"><i class="fas fa-plus-circle"></i> Crear Ticket</a>
                        <a href="../admin/procesos/usuarios.php" class="hd-quick-btn"><i class="fas fa-users"></i> Usuarios</a>
                        <a href="inventario.php" class="hd-quick-btn"><i class="fas fa-server"></i> Inventario</a>
                        <a href="reportes.php" class="hd-quick-btn"><i class="fas fa-chart-bar"></i> Reportes</a>
                        <a href="departamentos.php" class="hd-quick-btn"><i class="fas fa-building"></i> Deptos.</a>
                        <a href="categorias.php" class="hd-quick-btn"><i class="fas fa-tags"></i> Categorías</a>
                    </div>
                </div>
            </div>

            <!-- Actividad reciente -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h5 class="hd-card-title"><i class="fas fa-history"></i> Actividad Reciente</h5>
                </div>
                <div class="hd-card-body">
                    <div class="hd-activity-item">
                        <div class="hd-activity-dot" style="background:#fef2f2;color:var(--danger);"><i class="fas fa-exclamation"></i></div>
                        <div>
                            <div class="hd-activity-text">Ticket <strong>#TK-0001</strong> marcado como crítico</div>
                            <div class="hd-activity-time">hace 15 min</div>
                        </div>
                    </div>
                    <div class="hd-activity-item">
                        <div class="hd-activity-dot" style="background:#f0fdf4;color:var(--success);"><i class="fas fa-user-plus"></i></div>
                        <div>
                            <div class="hd-activity-text">Nuevo usuario <strong>jperez</strong> registrado</div>
                            <div class="hd-activity-time">hace 1 hora</div>
                        </div>
                    </div>
                    <div class="hd-activity-item">
                        <div class="hd-activity-dot" style="background:#eff6ff;color:var(--info);"><i class="fas fa-check"></i></div>
                        <div>
                            <div class="hd-activity-text">Ticket <strong>#TK-0003</strong> resuelto por cmendez</div>
                            <div class="hd-activity-time">hace 3 horas</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- FILA INFERIOR -->
    <div class="hd-grid-3">
        <!-- Por categoría -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-tags"></i> Por Categoría</h5>
            </div>
            <div class="hd-card-body">
                <?php
                $categorias = [
                    ['Hardware', 18, 'naranja'],
                    ['Software', 14, 'azul'],
                    ['Red e Internet', 10, 'rojo'],
                    ['Impresoras', 7, 'amarillo'],
                    ['Accesos', 5, 'verde'],
                ];
                foreach($categorias as $c): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="font-size:0.83rem;"><?= $c[0] ?></span>
                    <span class="hd-tag hd-tag-<?= $c[2] ?>"><?= $c[1] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Por departamento -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-building"></i> Por Departamento</h5>
            </div>
            <div class="hd-card-body">
                <?php
                $deptos = [
                    ['Manufactura', 16],
                    ['Contabilidad', 12],
                    ['RRHH', 9],
                    ['Abastecimiento', 8],
                    ['Logística', 5],
                ];
                foreach($deptos as $d): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="font-size:0.83rem;"><?= $d[0] ?></span>
                    <div style="background:#e2e8f0;border-radius:4px;height:6px;width:100px;margin:0 10px;flex:1;">
                        <div style="background:var(--naranja);height:100%;border-radius:4px;width:<?= ($d[1]/16)*100 ?>%;"></div>
                    </div>
                    <span style="font-size:0.78rem;color:var(--muted);min-width:20px;text-align:right;"><?= $d[1] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Técnicos -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-user-cog"></i> Técnicos</h5>
            </div>
            <div class="hd-card-body">
                <?php
                $tecnicos = [
                    ['cmendez', 'Carlos Méndez', 8, 'verde'],
                    ['jrodriguez', 'Juan Rodríguez', 5, 'naranja'],
                    ['aperez', 'Ana Pérez', 3, 'azul'],
                ];
                foreach($tecnicos as $t): ?>
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                    <div class="hd-avatar" style="width:34px;height:34px;font-size:0.8rem;"><?= strtoupper(substr($t[0],0,1)) ?></div>
                    <div style="flex:1;">
                        <div style="font-size:0.83rem;font-weight:500;"><?= $t[1] ?></div>
                        <div style="font-size:0.72rem;color:var(--muted);"><?= $t[2] ?> tickets asignados</div>
                    </div>
                    <span class="hd-tag hd-tag-<?= $t[3] ?>"><?= $t[2] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</main>

<?php include_once '../includes/footer.php'; ?>

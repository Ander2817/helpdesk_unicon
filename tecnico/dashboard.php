<?php include_once '../includes/header.php'; ?>

<main class="hd-main">

    <!-- ENCABEZADO -->
    <div class="hd-page-header">
        <div>
            <h2><i class="fas fa-tools" style="color:var(--naranja);margin-right:8px;"></i>Panel Técnico</h2>
            <p>Bienvenido, <strong><?= htmlspecialchars($usuario_activo) ?></strong> — Gestión de tickets asignados</p>
        </div>
        <span class="hd-rol-badge"><i class="fas fa-wrench"></i> Técnico TI</span>
    </div>

    <!-- STATS TÉCNICO -->
    <div class="hd-stats hd-stats-3">
        <div class="hd-stat">
            <div class="hd-stat-icon rojo"><i class="fas fa-fire"></i></div>
            <div>
                <div class="hd-stat-num">02</div>
                <div class="hd-stat-label">Críticos Asignados</div>
            </div>
        </div>
        <div class="hd-stat">
            <div class="hd-stat-icon naranja"><i class="fas fa-inbox"></i></div>
            <div>
                <div class="hd-stat-num">07</div>
                <div class="hd-stat-label">Pendientes por Atender</div>
            </div>
        </div>
        <div class="hd-stat">
            <div class="hd-stat-icon verde"><i class="fas fa-check-double"></i></div>
            <div>
                <div class="hd-stat-num">21</div>
                <div class="hd-stat-label">Resueltos este mes</div>
            </div>
        </div>
    </div>

    <div class="hd-grid-8-4" style="margin-bottom:20px;">

        <!-- MIS TICKETS ASIGNADOS -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-inbox"></i> Mis Tickets Asignados</h5>
                <a href="tickets_asignados.php" class="hd-btn hd-btn-outline hd-btn-sm">Ver todos</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="hd-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asunto</th>
                            <th>Solicitante</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span style="font-family:monospace;font-size:0.78rem;color:var(--muted);">#TK-0001</span></td>
                            <td>
                                <div style="font-weight:500;">Falla de Red LAN</div>
                                <div style="font-size:0.72rem;color:var(--muted);">Báscula de Pesaje</div>
                            </td>
                            <td>jperez</td>
                            <td><span class="hd-tag hd-tag-rojo">Crítica</span></td>
                            <td><span class="hd-tag hd-tag-naranja">En Proceso</span></td>
                            <td style="font-size:0.78rem;color:var(--muted);">19/06/2026</td>
                            <td>
                                <a href="ticket_atender.php?id=1" class="hd-btn hd-btn-primary hd-btn-sm"><i class="fas fa-wrench"></i> Atender</a>
                            </td>
                        </tr>
                        <tr>
                            <td><span style="font-family:monospace;font-size:0.78rem;color:var(--muted);">#TK-0002</span></td>
                            <td>
                                <div style="font-weight:500;">PC no enciende</div>
                                <div style="font-size:0.72rem;color:var(--muted);">Contabilidad</div>
                            </td>
                            <td>mrodriguez</td>
                            <td><span class="hd-tag hd-tag-naranja">Alta</span></td>
                            <td><span class="hd-tag hd-tag-azul">Abierto</span></td>
                            <td style="font-size:0.78rem;color:var(--muted);">20/06/2026</td>
                            <td>
                                <a href="ticket_atender.php?id=2" class="hd-btn hd-btn-primary hd-btn-sm"><i class="fas fa-wrench"></i> Atender</a>
                            </td>
                        </tr>
                        <tr>
                            <td><span style="font-family:monospace;font-size:0.78rem;color:var(--muted);">#TK-0005</span></td>
                            <td>
                                <div style="font-weight:500;">Error en sistema SAP</div>
                                <div style="font-size:0.72rem;color:var(--muted);">Abastecimiento</div>
                            </td>
                            <td>lgomez</td>
                            <td><span class="hd-tag hd-tag-amarillo">Media</span></td>
                            <td><span class="hd-tag hd-tag-gris">Pendiente</span></td>
                            <td style="font-size:0.78rem;color:var(--muted);">21/06/2026</td>
                            <td>
                                <a href="ticket_atender.php?id=5" class="hd-btn hd-btn-primary hd-btn-sm"><i class="fas fa-wrench"></i> Atender</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Acciones rápidas -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h5 class="hd-card-title"><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
                </div>
                <div class="hd-card-body">
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <a href="tickets_asignados.php" class="hd-quick-btn"><i class="fas fa-inbox"></i> Tickets Asignados</a>
                        <a href="tickets_pendientes.php" class="hd-quick-btn"><i class="fas fa-clock"></i> Pendientes</a>
                        <a href="tickets_resueltos.php" class="hd-quick-btn"><i class="fas fa-check-circle"></i> Resueltos</a>
                        <a href="inventario.php" class="hd-quick-btn"><i class="fas fa-server"></i> Inventario</a>
                        <a href="historial.php" class="hd-quick-btn"><i class="fas fa-history"></i> Mi Historial</a>
                    </div>
                </div>
            </div>

            <!-- Mi rendimiento -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h5 class="hd-card-title"><i class="fas fa-chart-pie"></i> Mi Rendimiento</h5>
                </div>
                <div class="hd-card-body">
                    <?php
                    $metricas = [
                        ['Resueltos hoy', '3', 'verde'],
                        ['Tiempo prom.', '1.8h', 'azul'],
                        ['SLA cumplido', '94%', 'verde'],
                        ['En espera resp.', '2', 'amarillo'],
                    ];
                    foreach($metricas as $m): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                        <span style="font-size:0.82rem;color:var(--muted);"><?= $m[0] ?></span>
                        <span class="hd-tag hd-tag-<?= $m[2] ?>" style="font-weight:700;"><?= $m[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- HISTORIAL RECIENTE -->
    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-history"></i> Últimas Actualizaciones que Realicé</h5>
        </div>
        <div style="overflow-x:auto;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Acción Realizada</th>
                        <th>Campo</th>
                        <th>Valor Anterior</th>
                        <th>Valor Nuevo</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span style="font-family:monospace;font-size:0.78rem;">#TK-0003</span></td>
                        <td>Cambio de estado</td>
                        <td>Estado</td>
                        <td><span class="hd-tag hd-tag-naranja">En Proceso</span></td>
                        <td><span class="hd-tag hd-tag-verde">Resuelto</span></td>
                        <td style="font-size:0.78rem;color:var(--muted);">21/06/2026 10:32</td>
                    </tr>
                    <tr>
                        <td><span style="font-family:monospace;font-size:0.78rem;">#TK-0001</span></td>
                        <td>Comentario agregado</td>
                        <td>Comentario</td>
                        <td>—</td>
                        <td style="font-size:0.82rem;">Se revisó el switch del área</td>
                        <td style="font-size:0.78rem;color:var(--muted);">21/06/2026 09:15</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php include_once '../includes/footer.php'; ?>

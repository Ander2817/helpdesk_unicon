<?php include_once '../includes/header.php'; ?>

<main class="hd-main">

    <!-- ENCABEZADO -->
    <div class="hd-page-header">
        <div>
            <h2><i class="fas fa-home" style="color:var(--naranja);margin-right:8px;"></i>Mi Panel</h2>
            <p>Bienvenido, <strong><?= htmlspecialchars($usuario_activo) ?></strong> — Gestiona tus solicitudes de soporte</p>
        </div>
        <a href="ticket_nuevo.php" class="hd-btn hd-btn-naranja">
            <i class="fas fa-plus-circle"></i> Nuevo Ticket
        </a>
    </div>

    <!-- STATS USUARIO -->
    <div class="hd-stats hd-stats-3">
        <div class="hd-stat">
            <div class="hd-stat-icon azul"><i class="fas fa-ticket-alt"></i></div>
            <div>
                <div class="hd-stat-num">05</div>
                <div class="hd-stat-label">Mis Tickets Totales</div>
            </div>
        </div>
        <div class="hd-stat">
            <div class="hd-stat-icon naranja"><i class="fas fa-spinner"></i></div>
            <div>
                <div class="hd-stat-num">02</div>
                <div class="hd-stat-label">En Proceso</div>
            </div>
        </div>
        <div class="hd-stat">
            <div class="hd-stat-icon verde"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="hd-stat-num">03</div>
                <div class="hd-stat-label">Resueltos</div>
            </div>
        </div>
    </div>

    <div class="hd-grid-8-4" style="margin-bottom:20px;">

        <!-- MIS TICKETS -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-ticket-alt"></i> Mis Tickets</h5>
                <a href="mis_tickets.php" class="hd-btn hd-btn-outline hd-btn-sm">Ver todos</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="hd-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asunto</th>
                            <th>Categoría</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span style="font-family:monospace;font-size:0.78rem;color:var(--muted);">#TK-0001</span></td>
                            <td>
                                <div style="font-weight:500;">Falla de Red LAN</div>
                                <div style="font-size:0.72rem;color:var(--muted);">Báscula de Pesaje</div>
                            </td>
                            <td>Red e Internet</td>
                            <td><span class="hd-tag hd-tag-rojo">Crítica</span></td>
                            <td><span class="hd-tag hd-tag-naranja">En Proceso</span></td>
                            <td style="font-size:0.78rem;color:var(--muted);">19/06/2026</td>
                            <td><a href="ver_ticket.php?id=1" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <tr>
                            <td><span style="font-family:monospace;font-size:0.78rem;color:var(--muted);">#TK-0003</span></td>
                            <td>
                                <div style="font-weight:500;">Impresora sin tóner</div>
                                <div style="font-size:0.72rem;color:var(--muted);">Oficina RRHH</div>
                            </td>
                            <td>Impresoras</td>
                            <td><span class="hd-tag hd-tag-gris">Baja</span></td>
                            <td><span class="hd-tag hd-tag-verde">Resuelto</span></td>
                            <td style="font-size:0.78rem;color:var(--muted);">15/06/2026</td>
                            <td><a href="ver_ticket.php?id=3" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <tr>
                            <td><span style="font-family:monospace;font-size:0.78rem;color:var(--muted);">#TK-0005</span></td>
                            <td>
                                <div style="font-weight:500;">No puedo acceder al correo</div>
                                <div style="font-size:0.72rem;color:var(--muted);">Mi equipo</div>
                            </td>
                            <td>Correo Electrónico</td>
                            <td><span class="hd-tag hd-tag-amarillo">Media</span></td>
                            <td><span class="hd-tag hd-tag-azul">Abierto</span></td>
                            <td style="font-size:0.78rem;color:var(--muted);">21/06/2026</td>
                            <td><a href="ver_ticket.php?id=5" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Crear ticket rápido -->
            <div class="hd-card" style="border-top:3px solid var(--naranja);">
                <div class="hd-card-header">
                    <h5 class="hd-card-title"><i class="fas fa-plus-circle"></i> Reportar Problema</h5>
                </div>
                <div class="hd-card-body">
                    <p style="font-size:0.82rem;color:var(--muted);margin-bottom:14px;">¿Tienes un problema técnico? Crea un ticket y el equipo de TI lo atenderá.</p>
                    <a href="ticket_nuevo.php" class="hd-btn hd-btn-naranja w-100" style="justify-content:center;">
                        <i class="fas fa-plus"></i> Crear Nuevo Ticket
                    </a>
                    <div style="margin-top:10px;padding:10px;background:#f8fafc;border-radius:8px;">
                        <div style="font-size:0.75rem;color:var(--muted);text-align:center;">
                            <i class="fas fa-clock" style="color:var(--naranja);"></i>
                            Tiempo prom. de respuesta: <strong>2.4 horas</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado de mis tickets -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h5 class="hd-card-title"><i class="fas fa-chart-donut"></i> Estado General</h5>
                </div>
                <div class="hd-card-body">
                    <?php
                    $estados = [
                        ['Abiertos', 1, 'azul'],
                        ['En Proceso', 2, 'naranja'],
                        ['Pendientes', 0, 'gris'],
                        ['Resueltos', 2, 'verde'],
                        ['Cerrados', 0, 'gris'],
                    ];
                    $total = 5;
                    foreach($estados as $e): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #f1f5f9;">
                        <span style="font-size:0.82rem;"><?= $e[0] ?></span>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="background:#e2e8f0;border-radius:4px;height:5px;width:70px;">
                                <div style="background:var(--naranja);height:100%;border-radius:4px;width:<?= $total > 0 ? ($e[1]/$total)*100 : 0 ?>%;"></div>
                            </div>
                            <span class="hd-tag hd-tag-<?= $e[2] ?>"><?= $e[1] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- GUÍA RÁPIDA -->
    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-question-circle"></i> ¿Cómo funciona el HelpDesk?</h5>
        </div>
        <div class="hd-card-body">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;">
                <?php
                $pasos = [
                    ['fas fa-plus-circle', 'naranja', '1. Crea tu ticket', 'Describe el problema técnico que estás experimentando con el mayor detalle posible.'],
                    ['fas fa-search', 'azul', '2. Es revisado', 'El equipo de TI recibe tu solicitud y la asigna al técnico disponible según la prioridad.'],
                    ['fas fa-wrench', 'amarillo', '3. Se trabaja en ello', 'El técnico asignado atiende tu caso. Puedes ver el progreso en tiempo real.'],
                    ['fas fa-check-circle', 'verde', '4. Solución confirmada', 'Recibes notificación cuando tu ticket es resuelto y puedes confirmar la solución.'],
                ];
                foreach($pasos as $p): ?>
                <div style="text-align:center;padding:10px;">
                    <div class="hd-stat-icon <?= $p[1] ?>" style="margin:0 auto 12px;width:48px;height:48px;">
                        <i class="<?= $p[0] ?>"></i>
                    </div>
                    <div style="font-weight:600;font-size:0.85rem;color:var(--azul);margin-bottom:6px;"><?= $p[2] ?></div>
                    <div style="font-size:0.78rem;color:var(--muted);line-height:1.5;"><?= $p[3] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</main>

<?php include_once '../includes/footer.php'; ?>

<?php include_once '../includes/header2.php'; ?>
<?php include_once '../includes/sidebar.php'; ?>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title">Bienvenido, <?= htmlspecialchars($usuario_activo) ?></div>
            <div class="hd-page-sub">Centro de soporte técnico · <?= date('d/m/Y') ?></div>
        </div>
        <a href="ticket_nuevo.php" class="hd-btn hd-btn-naranja hd-btn-sm"><i class="fas fa-plus"></i> Nuevo Ticket</a>
    </div>

    <!-- STATS -->
    <div class="hd-stats hd-stats-3">
        <div class="hd-stat azul">
            <div class="hd-stat-icon azul"><i class="fas fa-ticket-alt"></i></div>
            <div><div class="hd-stat-num">05</div><div class="hd-stat-label">Mis Tickets</div></div>
        </div>
        <div class="hd-stat naranja">
            <div class="hd-stat-icon naranja"><i class="fas fa-spinner"></i></div>
            <div><div class="hd-stat-num">02</div><div class="hd-stat-label">En Proceso</div></div>
        </div>
        <div class="hd-stat verde">
            <div class="hd-stat-icon verde"><i class="fas fa-check-circle"></i></div>
            <div><div class="hd-stat-num">03</div><div class="hd-stat-label">Resueltos</div></div>
        </div>
    </div>

    <div class="hd-grid-8-4" style="margin-bottom:12px;">

        <!-- MIS TICKETS -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-ticket-alt"></i> Mis Tickets</h5>
                <a href="mis_tickets.php" class="hd-btn hd-btn-ghost hd-btn-sm">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>
            <div style="overflow-x:auto;">
                <table class="hd-table">
                    <thead>
                        <tr><th>ID</th><th>Asunto</th><th>Categoría</th><th>Técnico</th><th>Prioridad</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $tickets = [
                            ['TK-0001','Falla de Red LAN','Red e Internet','cmendez','Crítica','rojo','En Proceso','naranja'],
                            ['TK-0003','Impresora sin tóner','Impresoras','aperez','Baja','gris','Resuelto','verde'],
                            ['TK-0005','No accedo al correo','Correo','jrodriguez','Media','amarillo','Abierto','azul'],
                        ];
                        foreach($tickets as $t): ?>
                        <tr>
                            <td><span style="font-family:monospace;color:var(--muted);font-size:0.7rem;">#<?= $t[0] ?></span></td>
                            <td>
                                <div style="font-weight:500;font-size:0.78rem;"><?= $t[1] ?></div>
                                <div style="font-size:0.68rem;color:var(--muted);"><?= $t[2] ?></div>
                            </td>
                            <td style="color:var(--muted);"><?= $t[2] ?></td>
                            <td>
                                <?php if($t[3]): ?>
                                <div style="display:flex;align-items:center;gap:5px;">
                                    <div class="hd-avatar" style="width:20px;height:20px;font-size:0.6rem;"><?= strtoupper(substr($t[3],0,1)) ?></div>
                                    <span><?= $t[3] ?></span>
                                </div>
                                <?php else: ?><span style="color:var(--muted);">Sin asignar</span><?php endif; ?>
                            </td>
                            <td><span class="hd-tag hd-tag-<?= $t[5] ?>"><?= $t[4] ?></span></td>
                            <td><span class="hd-tag hd-tag-<?= $t[7] ?>"><?= $t[6] ?></span></td>
                            <td><a href="ver_ticket.php" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DERECHA -->
        <div style="display:flex;flex-direction:column;gap:10px;">

            <!-- Crear ticket -->
            <div class="hd-card" style="border-top:3px solid var(--naranja);">
                <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-plus-circle"></i> Reportar Problema</h5></div>
                <div class="hd-card-body">
                    <p style="font-size:0.75rem;color:var(--muted);margin-bottom:10px;">¿Tienes un problema técnico? El equipo de TI lo atenderá.</p>
                    <a href="procesos/ticket_nuevo.php" class="hd-btn hd-btn-naranja" style="width:100%;justify-content:center;"><i class="fas fa-plus"></i> Crear Ticket</a>
                    <div style="margin-top:8px;padding:8px;background:#f8fafc;border-radius:6px;text-align:center;font-size:0.7rem;color:var(--muted);">
                        <i class="fas fa-clock" style="color:var(--naranja);"></i> Respuesta prom.: <strong>2.4h</strong>
                    </div>
                </div>
            </div>

            <!-- Estado -->
            <div class="hd-card">
                <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-chart-pie"></i> Estado General</h5></div>
                <div class="hd-card-body" style="padding:8px 12px;">
                    <?php
                    $estados = [['Abiertos',1,20,'azul'],['En Proceso',2,40,'naranja'],['Pendientes',0,0,'gris'],['Resueltos',2,40,'verde'],['Cerrados',0,0,'gris']];
                    foreach($estados as $e): ?>
                    <div class="hd-list-item">
                        <span style="flex:1;"><?= $e[0] ?></span>
                        <div class="hd-progress"><div class="hd-progress-fill" style="width:<?= $e[2] ?>%;"></div></div>
                        <span class="hd-tag hd-tag-<?= $e[3] ?>"><?= $e[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- GUÍA -->
    <div class="hd-card">
        <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-question-circle"></i> ¿Cómo funciona?</h5></div>
        <div class="hd-card-body">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;text-align:center;">
                <?php
                $pasos = [
                    ['fas fa-plus-circle','naranja','1. Crea el ticket','Describe el problema con el mayor detalle posible.'],
                    ['fas fa-search','azul','2. Es revisado','TI asigna al técnico disponible.'],
                    ['fas fa-wrench','amarillo','3. Se trabaja','El técnico atiende y actualiza el estado.'],
                    ['fas fa-check-circle','verde','4. Resuelto','Confirmas la solución y se cierra el ticket.'],
                ];
                foreach($pasos as $p): ?>
                <div style="padding:8px;">
                    <div class="hd-stat-icon <?= $p[1] ?>" style="margin:0 auto 8px;width:36px;height:36px;"><i class="<?= $p[0] ?>"></i></div>
                    <div style="font-weight:600;font-size:0.78rem;color:var(--azul);margin-bottom:4px;"><?= $p[2] ?></div>
                    <div style="font-size:0.72rem;color:var(--muted);line-height:1.4;"><?= $p[3] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</main>

<?php include_once '../includes/footer2.php'; ?>

<?php include_once '../includes/header2.php'; ?>
<?php include_once '../includes/sidebar.php'; ?>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title">Mi Panel — Técnico TI</div>
            <div class="hd-page-sub">Tickets asignados y actividad · <?= date('d/m/Y') ?></div>
        </div>
        <a href="tickets_asignados.php" class="hd-btn hd-btn-naranja hd-btn-sm"><i class="fas fa-inbox"></i> Ver mis tickets</a>
    </div>

    <!-- STATS -->
    <div class="hd-stats hd-stats-4">
        <div class="hd-stat rojo">
            <div class="hd-stat-icon rojo"><i class="fas fa-fire"></i></div>
            <div><div class="hd-stat-num">02</div><div class="hd-stat-label">Críticos</div></div>
        </div>
        <div class="hd-stat naranja">
            <div class="hd-stat-icon naranja"><i class="fas fa-inbox"></i></div>
            <div><div class="hd-stat-num">07</div><div class="hd-stat-label">Pendientes</div></div>
        </div>
        <div class="hd-stat verde">
            <div class="hd-stat-icon verde"><i class="fas fa-check-double"></i></div>
            <div><div class="hd-stat-num">21</div><div class="hd-stat-label">Resueltos</div></div>
        </div>
        <div class="hd-stat amarillo">
            <div class="hd-stat-icon amarillo"><i class="fas fa-clock"></i></div>
            <div><div class="hd-stat-num">1.8h</div><div class="hd-stat-label">Tiempo prom.</div></div>
        </div>
    </div>

    <div class="hd-grid-8-4" style="margin-bottom:12px;">

        <!-- MIS TICKETS -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-inbox"></i> Asignados a Mí</h5>
                <a href="tickets_asignados.php" class="hd-btn hd-btn-ghost hd-btn-sm">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>
            <div style="overflow-x:auto;">
                <table class="hd-table">
                    <thead>
                        <tr><th>ID</th><th>Asunto</th><th>Solicitante</th><th>Depto.</th><th>Prioridad</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $tickets = [
                            ['TK-0001','Falla de Red LAN','jperez','Manufactura','Crítica','rojo','En Proceso','naranja'],
                            ['TK-0002','PC no enciende','mrodriguez','Contabilidad','Alta','naranja','Abierto','azul'],
                            ['TK-0005','Error SAP','lgomez','Abastecimiento','Media','amarillo','Pendiente','gris'],
                            ['TK-0007','Monitor sin señal','aperez','RRHH','Baja','gris','Abierto','azul'],
                        ];
                        foreach($tickets as $t): ?>
                        <tr>
                            <td><span style="font-family:monospace;color:var(--muted);font-size:0.7rem;">#<?= $t[0] ?></span></td>
                            <td>
                                <div style="font-weight:500;font-size:0.78rem;"><?= $t[1] ?></div>
                                <div style="font-size:0.68rem;color:var(--muted);"><?= $t[3] ?></div>
                            </td>
                            <td><?= $t[2] ?></td>
                            <td style="color:var(--muted);"><?= $t[3] ?></td>
                            <td><span class="hd-tag hd-tag-<?= $t[5] ?>"><?= $t[4] ?></span></td>
                            <td><span class="hd-tag hd-tag-<?= $t[7] ?>"><?= $t[6] ?></span></td>
                            <td><a href="ticket_atender.php" class="hd-btn hd-btn-primary hd-btn-sm"><i class="fas fa-wrench"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DERECHA -->
        <div style="display:flex;flex-direction:column;gap:10px;">

            <div class="hd-card">
                <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-chart-line"></i> Mi Rendimiento</h5></div>
                <div class="hd-card-body" style="padding:8px 12px;">
                    <?php
                    $metricas = [['Resueltos hoy','3','verde'],['SLA cumplido','94%','verde'],['En espera resp.','2','amarillo'],['Promedio resolución','1.8h','azul']];
                    foreach($metricas as $m): ?>
                    <div class="hd-list-item">
                        <span style="color:var(--muted);flex:1;"><?= $m[0] ?></span>
                        <span class="hd-tag hd-tag-<?= $m[2] ?>" style="font-weight:700;"><?= $m[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hd-card">
                <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-history"></i> Reciente</h5></div>
                <div class="hd-card-body" style="padding:8px 12px;">
                    <?php
                    $act = [
                        ['fas fa-check','f0fdf4','success','#TK-0003 resuelto','hace 2h'],
                        ['fas fa-comment','eff6ff','info','Comentario en #TK-0001','hace 4h'],
                        ['fas fa-exchange-alt','fff7ed','warning','Estado cambiado #TK-0005','ayer'],
                    ];
                    foreach($act as $a): ?>
                    <div class="hd-activity">
                        <div class="hd-activity-dot" style="background:#<?= $a[1] ?>;color:var(--<?= $a[2] ?>);"><i class="<?= $a[0] ?>"></i></div>
                        <div><div><?= $a[3] ?></div><div class="hd-activity-time"><?= $a[4] ?></div></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- HISTORIAL -->
    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-history"></i> Mis Últimas Acciones</h5>
            <a href="historial.php" class="hd-btn hd-btn-ghost hd-btn-sm">Ver historial completo</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="hd-table">
                <thead><tr><th>Ticket</th><th>Acción</th><th>Antes</th><th>Después</th><th>Fecha</th></tr></thead>
                <tbody>
                    <tr>
                        <td><span style="font-family:monospace;font-size:0.7rem;">#TK-0003</span></td>
                        <td>Cambio de estado</td>
                        <td><span class="hd-tag hd-tag-naranja">En Proceso</span></td>
                        <td><span class="hd-tag hd-tag-verde">Resuelto</span></td>
                        <td style="color:var(--muted);font-size:0.72rem;">21/06/2026 10:32</td>
                    </tr>
                    <tr>
                        <td><span style="font-family:monospace;font-size:0.7rem;">#TK-0001</span></td>
                        <td>Comentario agregado</td>
                        <td>—</td>
                        <td style="font-size:0.78rem;">Revisé switch del área</td>
                        <td style="color:var(--muted);font-size:0.72rem;">21/06/2026 09:15</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php include_once '../includes/footer2.php'; ?>

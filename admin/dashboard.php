<?php include_once '../includes/header2.php'; ?>

<main class="hd-main">

    <!-- HEADER -->
    <div class="hd-page-header">
        <div>
            <div class="hd-page-title">Panel General — Administrador</div>
            <div class="hd-page-sub">Vista completa del sistema · <?= date('d/m/Y') ?></div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="reportes.php" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-download"></i> Exportar</a>
            <a href="tickets_crear.php" class="hd-btn hd-btn-naranja hd-btn-sm"><i class="fas fa-plus"></i> Crear Ticket</a>
        </div>
    </div>

    <!-- STATS -->
    <div class="hd-stats hd-stats-4">
        <div class="hd-stat rojo">
            <div class="hd-stat-icon rojo"><i class="fas fa-fire"></i></div>
            <div><div class="hd-stat-num">04</div><div class="hd-stat-label">Críticos Abiertos</div></div>
        </div>
        <div class="hd-stat naranja">
            <div class="hd-stat-icon naranja"><i class="fas fa-spinner"></i></div>
            <div><div class="hd-stat-num">12</div><div class="hd-stat-label">En Proceso</div></div>
        </div>
        <div class="hd-stat verde">
            <div class="hd-stat-icon verde"><i class="fas fa-check-circle"></i></div>
            <div><div class="hd-stat-num">38</div><div class="hd-stat-label">Resueltos mes</div></div>
        </div>
        <div class="hd-stat azul">
            <div class="hd-stat-icon azul"><i class="fas fa-users"></i></div>
            <div><div class="hd-stat-num">16</div><div class="hd-stat-label">Usuarios Activos</div></div>
        </div>
    </div>

    <!-- FILA PRINCIPAL -->
    <div class="hd-grid-8-4" style="margin-bottom:12px;">

        <!-- TICKETS RECIENTES -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-list"></i> Tickets Recientes</h5>
                <a href="tickets.php" class="hd-btn hd-btn-ghost hd-btn-sm">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>
            <div style="overflow-x:auto;">
                <table class="hd-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asunto</th>
                            <th>Área</th>
                            <th>Técnico</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tickets = [
                            ['TK-0001','Falla de Red LAN','Manufactura','cmendez','Crítica','rojo','En Proceso','naranja'],
                            ['TK-0002','PC no enciende','Contabilidad','jrodriguez','Alta','naranja','Abierto','azul'],
                            ['TK-0003','Impresora sin tóner','RRHH','aperez','Baja','gris','Resuelto','verde'],
                            ['TK-0004','Acceso SAP','Abastecimiento','cmendez','Media','amarillo','Pendiente','gris'],
                            ['TK-0005','Error en correo','Logística','jrodriguez','Alta','naranja','En Proceso','naranja'],
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
                                <div style="display:flex;align-items:center;gap:5px;">
                                    <div class="hd-avatar" style="width:20px;height:20px;font-size:0.6rem;"><?= strtoupper(substr($t[3],0,1)) ?></div>
                                    <span><?= $t[3] ?></span>
                                </div>
                            </td>
                            <td><span class="hd-tag hd-tag-<?= $t[5] ?>"><?= $t[4] ?></span></td>
                            <td><span class="hd-tag hd-tag-<?= $t[7] ?>"><?= $t[6] ?></span></td>
                            <td><a href="ticket_ver.php?id=1" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div style="display:flex;flex-direction:column;gap:10px;">

            <!-- Resumen -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h5 class="hd-card-title"><i class="fas fa-chart-pie"></i> Resumen</h5>
                </div>
                <div class="hd-card-body" style="padding:8px 12px;">
                    <?php
                    $resumen = [
                        ['Total Tickets','54','azul'],
                        ['Tiempo prom.','2.4h','naranja'],
                        ['SLA cumplido','91%','verde'],
                        ['Técnicos activos','3','gris'],
                        ['Equipos inv.','47','gris'],
                    ];
                    foreach($resumen as $r): ?>
                    <div class="hd-list-item">
                        <span style="color:var(--muted);flex:1;"><?= $r[0] ?></span>
                        <span class="hd-tag hd-tag-<?= $r[2] ?>" style="font-weight:700;"><?= $r[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Actividad -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h5 class="hd-card-title"><i class="fas fa-history"></i> Actividad</h5>
                </div>
                <div class="hd-card-body" style="padding:8px 12px;">
                    <?php
                    $actividad = [
                        ['fas fa-exclamation','fef2f2','danger','#TK-0001 marcado crítico','15 min'],
                        ['fas fa-user-plus','f0fdf4','success','Usuario jperez creado','1 hora'],
                        ['fas fa-check','eff6ff','info','#TK-0003 resuelto','3 horas'],
                        ['fas fa-wrench','fff7ed','warning','#TK-0002 asignado','5 horas'],
                    ];
                    foreach($actividad as $a): ?>
                    <div class="hd-activity">
                        <div class="hd-activity-dot" style="background:#<?= $a[1] ?>;color:var(--<?= $a[2] ?>);"><i class="<?= $a[0] ?>"></i></div>
                        <div>
                            <div><?= $a[3] ?></div>
                            <div class="hd-activity-time"><?= $a[4] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- FILA INFERIOR -->
    <div class="hd-grid-3">

        <!-- Por categoría -->
        <div class="hd-card">
            <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-tags"></i> Por Categoría</h5></div>
            <div class="hd-card-body" style="padding:8px 12px;">
                <?php
                $cats = [['Hardware',18,56],['Software',14,44],['Red',10,31],['Impresoras',7,22],['Accesos',5,16]];
                foreach($cats as $c): ?>
                <div class="hd-list-item">
                    <span style="flex:1;"><?= $c[0] ?></span>
                    <div class="hd-progress"><div class="hd-progress-fill" style="width:<?= $c[2] ?>%;"></div></div>
                    <span style="color:var(--muted);min-width:20px;text-align:right;"><?= $c[1] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Por departamento -->
        <div class="hd-card">
            <div class="hd-card-header"><h5 class="hd-card-title"><i class="fas fa-building"></i> Por Departamento</h5></div>
            <div class="hd-card-body" style="padding:8px 12px;">
                <?php
                $deptos = [['Manufactura',16,100],['Contabilidad',12,75],['RRHH',9,56],['Abastecimiento',8,50],['Logística',5,31]];
                foreach($deptos as $d): ?>
                <div class="hd-list-item">
                    <span style="flex:1;"><?= $d[0] ?></span>
                    <div class="hd-progress"><div class="hd-progress-fill" style="width:<?= $d[2] ?>%;"></div></div>
                    <span style="color:var(--muted);min-width:20px;text-align:right;"><?= $d[1] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Técnicos -->
        <div class="hd-card">
            <div class="hd-card-header">
                <h5 class="hd-card-title"><i class="fas fa-user-cog"></i> Técnicos</h5>
                <a href="procesos/usuarios.php" class="hd-btn hd-btn-ghost hd-btn-sm">Gestionar</a>
            </div>
            <div class="hd-card-body" style="padding:8px 12px;">
                <?php
                $tecs = [['cmendez','Carlos Méndez',8,'verde'],['jrodriguez','Juan Rodríguez',5,'naranja'],['aperez','Ana Pérez',3,'azul']];
                foreach($tecs as $t): ?>
                <div class="hd-list-item">
                    <div class="hd-avatar" style="width:24px;height:24px;font-size:0.65rem;"><?= strtoupper(substr($t[0],0,1)) ?></div>
                    <div style="flex:1;">
                        <div style="font-weight:500;"><?= $t[1] ?></div>
                        <div style="font-size:0.68rem;color:var(--muted);"><?= $t[2] ?> tickets</div>
                    </div>
                    <span class="hd-tag hd-tag-<?= $t[3] ?>"><?= $t[2] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</main>

<?php include_once '../includes/footer2.php'; ?>

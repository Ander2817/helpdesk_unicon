<?php
// 1. IMPORTANTE: Salimos de la carpeta admin/ para jalar el validador de sesión y tus variables
include_once '../includes/header.php';
?>

<div class="content-wrapper py-4" style="background-color: #f8f9fa;">
    <div class="content">
        <div class="container">
            
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-outline shadow-sm border-0" style="border-top: 3px solid var(--azul-unicon);">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <h3 class="font-weight-bold text-dark mb-1">Panel Operativo TI</h3>
                                <p class="text-muted mb-0 small">Conectado como: <strong><?php echo htmlspecialchars($usuario_activo); ?></strong> &mdash; Sede Administrativa e Industrial</p>
                            </div>
                            <div>
                                <span class="badge px-3 py-2 text-uppercase font-monospace text-white" style="background-color: var(--azul-unicon); font-size: 0.75rem;">
                                    ID Rol Activo: <?php echo htmlspecialchars($rol_id); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box bg-white shadow-sm border-0" style="border-radius: 6px;">
                        <span class="info-box-icon bg-danger elevation-1" style="border-radius: 6px;"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted text-uppercase small font-weight-bold">Críticos / Abiertos</span>
                            <span class="info-box-number h4 font-weight-bold text-dark mb-0">04</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box bg-white shadow-sm border-0" style="border-radius: 6px;">
                        <span class="info-box-icon bg-warning elevation-1 text-white" style="border-radius: 6px;"><i class="fas fa-wrench"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted text-uppercase small font-weight-bold">En Reparación</span>
                            <span class="info-box-number h4 font-weight-bold text-dark mb-0">02</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box bg-white shadow-sm border-0" style="border-radius: 8px;">
                        <span class="info-box-icon bg-success elevation-1" style="border-radius: 6px;"><i class="fas fa-check-double"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted text-uppercase small font-weight-bold">Cerrados Planta</span>
                            <span class="info-box-number h4 font-weight-bold text-dark mb-0">18</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm border-0" style="border-radius: 6px;">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <h5 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-list-ul me-2 text-secondary"></i>Monitoreo de Faltas Recientes</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-valign-middle mb-0" style="font-size: 0.9rem;">
                                    <thead class="table-light text-secondary">
                                        <tr>
                                            <th class="py-3 ps-4">Incidencia</th>
                                            <th class="py-3">Área de Origen</th>
                                            <th class="py-3">Estatus</th>
                                            <th class="py-3 pe-4 text-end">Gestión</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-4 py-3">
                                                <span class="d-block font-weight-bold text-dark">Falla de Red LAN</span>
                                                <small class="text-muted">ID Caso: #4052</small>
                                            </td>
                                            <td class="py-3">Planta — Báscula de Pesaje</td>
                                            <td class="py-3"><span class="badge bg-danger p-2">Alta Prioridad</span></td>
                                            <td class="pe-4 py-3 text-end">
                                                <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm border-0" style="border-radius: 6px;">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <h5 class="card-title font-weight-bold text-dark mb-0">Accesos Directos</h5>
                        </div>
                        <div class="card-body px-4 pb-4 pt-2 d-flex flex-column gap-2">
                            <a href="#" class="btn text-white text-left p-3 d-flex align-items-center justify-content-between" style="background-color: var(--azul-unicon); border-radius: 6px;">
                                <span><i class="fas fa-plus-circle me-2"></i> Crear Nuevo Reporte</span>
                                <i class="fas fa-arrow-right small opacity-50"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
// Salimos de admin/ para cerrar la estructura HTML con el footer
include_once '../includes/footer.php';
?>
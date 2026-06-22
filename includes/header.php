<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar sesión y rol
if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/conexion.php';

$usuario_activo = $_SESSION['usser'];
$rol_id         = $_SESSION['id_rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpDesk Unicon — Panel</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        :root {
            --azul-unicon: #1a2a4a;
            --naranja-unicon: #E87722;
        }
        .main-header-hd {
            background-color: var(--azul-unicon);
            border-bottom: 3px solid var(--naranja-unicon);
        }
        .nav-link-hd {
            color: rgba(255, 255, 255, 0.85) !important;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            padding: 0.5rem 1rem !important;
            border-radius: 4px;
        }
        .nav-link-hd:hover, .nav-link-hd.active {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .avatar-letra {
            width: 34px;
            height: 34px;
            background-color: var(--naranja-unicon);
            color: white;
            font-weight: bold;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="hold-transition layout-top-nav bg-light">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand-md navbar-dark main-header-hd border-0 py-2">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand d-flex align-items-center gap-2">
                <i class="fas fa-headset text-white" style="font-size: 1.4rem;"></i>
                <span class="brand-text font-weight-bold text-white mb-0" style="letter-spacing: 0.5px;">
                    Help<span style="color: var(--naranja-unicon);">Desk</span> Unicon
                </span>
            </a>

            <button class="navbar-toggler order-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse order-3" id="navbarCollapse">
                <ul class="navbar-nav ms-4 me-auto">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link-hd active"><i class="fas fa-home me-1"></i> Panel</a>
                    </li>
                    <li class="nav-item">
                        <a href="tickets.php" class="nav-link-hd"><i class="fas fa-ticket-alt me-1"></i> Reportes</a>
                    </li>
                    <?php if ($rol_id == 3): ?>
                    <li class="nav-item">
                        <a href="usuarios.php" class="nav-link-hd"><i class="fas fa-users-cog me-1"></i> Personal</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center gap-2 pr-0" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                        <div class="avatar-letra"><?= strtoupper(substr($usuario_activo, 0, 1)) ?></div>
                        <span class="text-white d-none d-sm-inline font-weight-light"><?= htmlspecialchars($usuario_activo) ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right mt-2 shadow border-0" style="right: 0; left: auto;">
                        <span class="dropdown-header text-uppercase tracking-wider font-weight-bold text-muted">Configuración</span>
                        <div class="dropdown-divider"></div>
                        <a href="perfil.php" class="dropdown-item py-2">
                            <i class="fas fa-user-edit mr-2 text-secondary"></i> Mi Cuenta
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="../auth/logout.php" class="dropdown-item py-2 text-danger">
                            <i class="fas fa-power-off mr-2"></i> Salir del Sistema
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
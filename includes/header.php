<?php

if (!isset($conexion)) {
    require_once __DIR__ . '/conexion.php';
}

// Proteger páginas: si no hay sesión activa, redirigir al login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Datos del usuario en sesión
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_rol    = $_SESSION['usuario_rol']    ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpDesk Unicon</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css">

    <style>
        .navbar-helpdesk {
            background-color: #1a2a4a;
            border-bottom: 3px solid #E87722;
            padding: 0 1.25rem;
            min-height: 60px;
        }

        .navbar-brand-hd {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .navbar-brand-hd .brand-icon {
            font-size: 1.5rem;
            color: #E87722;
        }
        .navbar-brand-hd .brand-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
        }
        .navbar-brand-hd .brand-text span {
            color: #E87722;
        }


        .nav-link-hd {
            color: rgba(255, 255, 255, 0.80) !important;
            font-size: 0.875rem;
            padding: 0.9rem 0.85rem !important;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: color 0.2s, border-bottom 0.2s;
            border-bottom: 3px solid transparent;
        }
        .nav-link-hd:hover,
        .nav-link-hd.active {
            color: #ffffff !important;
            border-bottom: 3px solid #E87722;
        }
        .nav-link-hd i {
            font-size: 0.9rem;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.85) !important;
            font-size: 0.875rem;
            cursor: pointer;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: background 0.2s;
            text-decoration: none;
        }
        .user-dropdown-toggle:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #fff !important;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #E87722;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
        .dropdown-menu-dark-hd {
            background-color: #1a2a4a;
            border: 1px solid rgba(232, 119, 34, 0.35);
            border-radius: 8px;
            min-width: 200px;
            padding: 0.5rem 0;
            margin-top: 6px !important;
        }
        .dropdown-menu-dark-hd .dropdown-item {
            color: rgba(255, 255, 255, 0.80);
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dropdown-menu-dark-hd .dropdown-item:hover {
            background-color: rgba(232, 119, 34, 0.15);
            color: #fff;
        }
        .dropdown-menu-dark-hd .dropdown-divider {
            border-color: rgba(255, 255, 255, 0.12);
        }
        .dropdown-menu-dark-hd .dropdown-header {
            color: #E87722;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .navbar-toggler-hd {
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 0.3rem 0.55rem;
        }
        .navbar-toggler-hd .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255,255,255,0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
    </style>
</head>
<body>


<nav class="navbar navbar-expand-lg navbar-helpdesk sticky-top">
    <div class="container-fluid">

        <a class="navbar-brand-hd" href="dashboard.php">
            <i class="fas fa-headset brand-icon"></i>
            <span class="brand-text">Help<span>Desk</span> Unicon</span>
        </a>

        <button class="navbar-toggler navbar-toggler-hd ms-auto me-2" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link nav-link-hd <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>"
                       href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-hd <?= basename($_SERVER['PHP_SELF']) === 'tickets.php' ? 'active' : '' ?>"
                       href="tickets.php">
                        <i class="fas fa-ticket-alt"></i> Tickets
                    </a>
                </li>

                <?php if ($usuario_rol === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link nav-link-hd <?= basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'active' : '' ?>"
                       href="usuarios.php">
                        <i class="fas fa-users"></i> Usuarios
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link nav-link-hd <?= basename($_SERVER['PHP_SELF']) === 'reportes.php' ? 'active' : '' ?>"
                       href="reportes.php">
                        <i class="fas fa-chart-bar"></i> Reportes
                    </a>
                </li>

            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="user-dropdown-toggle dropdown-toggle"
                       href="#" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            <?= strtoupper(mb_substr($usuario_nombre, 0, 1)) ?>
                        </div>
                        <span class="d-none d-md-inline"><?= htmlspecialchars($usuario_nombre) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-hd">
                        <li><h6 class="dropdown-header"><i class="fas fa-user-circle me-1"></i> Mi cuenta</h6></li>
                        <li>
                            <a class="dropdown-item" href="perfil.php">
                                <i class="fas fa-id-card"></i> Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger-emphasis" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
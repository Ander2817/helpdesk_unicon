<?php
// CAMBIO 1: Usamos __DIR__ para que busque constants.php justo al lado de este archivo, sin importar desde dónde se llame
require_once __DIR__ . '/constants.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    // CAMBIO 2: Usamos una ruta absoluta del servidor para que la redirección funcione desde cualquier subcarpeta
    header("Location: /helpdesk_unicon/index.php");
    exit();
}

if (!isset($conexion)) {
    // CAMBIO 3: Usamos __DIR__ y subimos un nivel para entrar de forma segura a la carpeta config/
    require_once __DIR__ . '/../config/conexion.php';
}

$usuario_activo = $_SESSION['usser'];
$rol_id         = (int)$_SESSION['id_rol'];

// Nombre legible del rol
$rol_nombre = match($rol_id) {
    3 => 'Administrador',
    2 => 'Técnico TI',
    1 => 'Usuario',
    default => 'Desconocido'
};

// Inicial para avatar
$inicial = strtoupper(substr($usuario_activo, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpDesk Unicon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="hd-navbar navbar navbar-expand-lg">
    <div class="container-fluid px-4">

        <!-- LOGO -->
        <a class="navbar-brand hd-brand" href="dashboard.php">
            <i class="fas fa-headset hd-brand-icon"></i>
            <span>Help<span class="hd-brand-accent">Desk</span> <span class="hd-brand-light">Unicon</span></span>
        </a>

        <!-- TOGGLER MOBILE -->
        <button class="navbar-toggler hd-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hdNav">
            <i class="fas fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="hdNav">

            <!-- MENÚ SEGÚN ROL -->
            <ul class="navbar-nav ms-4 me-auto hd-nav-links">

                <?php if ($rol_id == ROL_ADMIN): // ADMIN ?>
                <li class="nav-item"><a href="dashboard.php" class="hd-nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Panel</a></li>
                <li class="nav-item dropdown">
                    <a class="hd-nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-ticket-alt"></i> Tickets</a>
                    <ul class="dropdown-menu hd-dropdown">
                        <li><a class="dropdown-item" href="tickets.php"><i class="fas fa-list"></i> Todos los Tickets</a></li>
                        <li><a class="dropdown-item" href="tickets_crear.php"><i class="fas fa-plus"></i> Crear Ticket</a></li>
                        <li><a class="dropdown-item" href="tickets_historial.php"><i class="fas fa-history"></i> Historial</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="hd-nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-users-cog"></i> Usuarios</a>
                    <ul class="dropdown-menu hd-dropdown">
                        <li><a class="dropdown-item" href="../admin/procesos/usuarios.php"><i class="fas fa-users"></i> Gestionar Usuarios</a></li>
                        <li><a class="dropdown-item" href="roles.php"><i class="fas fa-shield-alt"></i> Roles y Permisos</a></li>
                        <li><a class="dropdown-item" href="departamentos.php"><i class="fas fa-building"></i> Departamentos</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="hd-nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-cogs"></i> Gestión</a>
                    <ul class="dropdown-menu hd-dropdown">
                        <li><a class="dropdown-item" href="categorias.php"><i class="fas fa-tags"></i> Categorías</a></li>
                        <li><a class="dropdown-item" href="prioridades.php"><i class="fas fa-flag"></i> Prioridades</a></li>
                        <li><a class="dropdown-item" href="estados.php"><i class="fas fa-toggle-on"></i> Estados</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="inventario.php" class="hd-nav-link"><i class="fas fa-server"></i> Inventario</a></li>
                <li class="nav-item"><a href="reportes.php" class="hd-nav-link"><i class="fas fa-chart-bar"></i> Reportes</a></li>

                <?php elseif ($rol_id == ROL_TECNICO): // TÉCNICO ?>
                <li class="nav-item"><a href="dashboard.php" class="hd-nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Panel</a></li>
                <li class="nav-item dropdown">
                    <a class="hd-nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-ticket-alt"></i> Mis Tickets</a>
                    <ul class="dropdown-menu hd-dropdown">
                        <li><a class="dropdown-item" href="tickets_asignados.php"><i class="fas fa-inbox"></i> Asignados a Mí</a></li>
                        <li><a class="dropdown-item" href="tickets_pendientes.php"><i class="fas fa-clock"></i> Pendientes</a></li>
                        <li><a class="dropdown-item" href="tickets_resueltos.php"><i class="fas fa-check-circle"></i> Resueltos</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="inventario.php" class="hd-nav-link"><i class="fas fa-server"></i> Inventario</a></li>
                <li class="nav-item"><a href="historial.php" class="hd-nav-link"><i class="fas fa-history"></i> Historial</a></li>

                <?php else: // USUARIO ?>
                <li class="nav-item"><a href="dashboard.php" class="hd-nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="nav-item"><a href="ticket_nuevo.php" class="hd-nav-link"><i class="fas fa-plus-circle"></i> Nuevo Ticket</a></li>
                <li class="nav-item"><a href="mis_tickets.php" class="hd-nav-link"><i class="fas fa-ticket-alt"></i> Mis Tickets</a></li>
                <?php endif; ?>

            </ul>

            <!-- LADO DERECHO: Notificaciones + Usuario -->
            <ul class="navbar-nav ms-auto align-items-center gap-2">

                <!-- Notificaciones -->
                <li class="nav-item">
                    <a href="#" class="hd-nav-icon position-relative">
                        <i class="fas fa-bell"></i>
                        <span class="hd-badge">3</span>
                    </a>
                </li>

                <!-- Dropdown usuario -->
                <li class="nav-item dropdown">
                    <a class="hd-user-btn dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <div class="hd-avatar"><?= $inicial ?></div>
                        <span class="hd-user-name"><?= htmlspecialchars($usuario_activo) ?></span>
                        <small class="hd-user-role"><?= $rol_nombre ?></small>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end hd-dropdown mt-2">
                        <li><span class="dropdown-header">Mi Cuenta</span></li>
                        <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user-edit"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="cambiar_clave.php"><i class="fas fa-key"></i> Cambiar Contraseña</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fas fa-power-off"></i> Cerrar Sesión</a></li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</nav>
<!-- FIN NAVBAR -->

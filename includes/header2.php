<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    header("Location: ../index.php"); exit();
}
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';

$usuario_activo = $_SESSION['usser'];
$rol_id         = (int)$_SESSION['id_rol'];
$inicial        = strtoupper(substr($usuario_activo, 0, 1));
$rol_nombre     = match($rol_id) { 3=>'Administrador', 2=>'Técnico TI', 1=>'Usuario', default=>'—' };
$pagina_actual  = basename($_SERVER['PHP_SELF']);

require_once __DIR__ . '/../includes/constants.php';

// ---- GUARDA DE ROL POR CARPETA ----
// Cada carpeta del sistema pertenece a un rol. Si alguien entra a una carpeta que no es
// la suya (por ejemplo un técnico escribiendo admin/dashboard.php en la URL), se le
// devuelve a su propio panel. Cubre todas las páginas que incluyen este header.
$rol_de_carpeta   = ['admin' => ROL_ADMIN, 'tecnico' => ROL_TECNICO, 'usuario' => ROL_USUARIO];
$carpeta_de_rol   = [ROL_ADMIN => 'admin', ROL_TECNICO => 'tecnico', ROL_USUARIO => 'usuario'];
$ruta_actual      = str_replace('\\', '/', $_SERVER['PHP_SELF']);

foreach ($rol_de_carpeta as $carpeta => $rol_requerido) {
    if (strpos($ruta_actual, '/' . $carpeta . '/') !== false && $rol_id !== $rol_requerido) {
        $destino = isset($carpeta_de_rol[$rol_id]) ? $carpeta_de_rol[$rol_id] . '/dashboard.php' : 'index.php';
        header('Location: ' . URL_BASE . $destino);
        exit();
    }
}
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
    <link rel="stylesheet" href="../assets/css/dashboard2.css">
</head>
<body>

<!-- ===== TOPBAR ===== -->
<header class="hd-topbar">
    <a href="dashboard.php" class="hd-topbar-brand">
        <i class="fas fa-headset"></i>
        Help<span>Desk</span> <small>Unicon</small>
    </a>

    <div class="hd-topbar-search">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Buscar tickets, usuarios...">
    </div>

    <div class="hd-topbar-right">

        <div style="position:relative;">
            <a href="#" class="hd-topbar-icon" title="Notificaciones">
                <i class="fas fa-bell"></i>
                <span class="hd-notif-dot"></span>
            </a>
        </div>

        <?php if ($rol_id >= 2): ?>
        <a href="<?= $rol_id === ROL_ADMIN ? 'tickets.php' : 'tickets_asignados.php' ?>" class="hd-topbar-icon" title="Tickets">
            <i class="fas fa-ticket-alt"></i>
        </a>
        <?php endif; ?>

        <?php if ($rol_id == 3): ?>
        <a href="procesos/usuarios.php" class="hd-topbar-icon" title="Usuarios">
            <i class="fas fa-users"></i>
        </a>
        <?php endif; ?>

        <div class="hd-topbar-sep"></div>

        <div style="position:relative;">
            <button class="hd-topbar-user" onclick="toggleUserMenu()" id="userBtn">
                <div class="hd-avatar"><?= $inicial ?></div>
                <div class="hd-topbar-user-info">
                    <span class="hd-topbar-user-name"><?= htmlspecialchars($usuario_activo) ?></span>
                    <span class="hd-topbar-user-role"><?= $rol_nombre ?></span>
                </div>
                <i class="fas fa-chevron-down" style="color:rgba(255,255,255,0.4);font-size:0.6rem;margin-left:4px;"></i>
            </button>
            <div class="hd-dropdown-menu" id="userMenu">
                <a href="perfil.php" class="hd-dropdown-item"><i class="fas fa-user-edit" style="color:var(--muted);width:14px;"></i> Mi Perfil</a>
                <a href="cambiar_clave.php" class="hd-dropdown-item"><i class="fas fa-key" style="color:var(--muted);width:14px;"></i> Contraseña</a>
                <div class="hd-dropdown-sep"></div>
                <a href="../auth/logout.php" class="hd-dropdown-item danger"><i class="fas fa-power-off" style="width:14px;"></i> Cerrar Sesión</a>
            </div>
        </div>
    </div>
</header>
<!-- FIN TOPBAR -->
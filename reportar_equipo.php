<?php
// reportar_equipo.php?e=ID — Destino del código QR pegado en cada equipo.
// Si ya hay sesión de usuario → abre "Nuevo ticket" con el equipo preseleccionado.
// Si no → recuerda el equipo, pide iniciar sesión y, tras entrar, el usuario lo reabre desde el mismo QR.
session_start();
$id = (int)($_GET['e'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit(); }
if (!isset($_SESSION['usser'])) {
    $_SESSION['equipo_qr'] = $id;
    header("Location: index.php?msg=" . urlencode("Inicia sesión y vuelve a escanear el código para reportar el equipo."));
    exit();
}
$rol = (int)($_SESSION['id_rol'] ?? 0);
if ($rol === 1) { header("Location: usuario/ticket_nuevo.php?equipo=" . $id); exit(); }
// técnicos y administradores ven la ficha del inventario
header("Location: " . ($rol === 3 ? "admin/inventario.php" : "tecnico/inventario.php") . "?buscar=" . $id);
exit();

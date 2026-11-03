<?php
// error.php — Página de error amigable para 403 / 404 / 500 (se usa desde .htaccess). No muestra detalles técnicos.
$c = (int)($_GET['c'] ?? 404);
$info = [
    403 => ['Acceso denegado', 'No tienes permiso para ver esta página.', 'fa-lock'],
    404 => ['Página no encontrada', 'La dirección que buscas no existe o fue movida.', 'fa-compass'],
    500 => ['Algo salió mal', 'Ocurrió un error en el servidor. Ya puedes volver a intentarlo; si se repite, avisa al administrador.', 'fa-triangle-exclamation'],
][$c] ?? ['Error', 'No se pudo completar la solicitud.', 'fa-circle-exclamation'];
if (!in_array($c, [403, 404, 500], true)) $c = 404;
http_response_code($c);
// Destino del botón según el rol de la sesión (si existe)
if (session_status() === PHP_SESSION_NONE) @session_start();
$destino = 'index.php';
if (isset($_SESSION['usser'])) $destino = [3 => 'admin/', 2 => 'tecnico/', 1 => 'usuario/'][(int)($_SESSION['id_rol'] ?? 0)] ?? 'index.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
?><!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $c ?> · HelpDesk Unicon</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f1f5f9;font-family:system-ui,Segoe UI,Roboto,sans-serif;color:#0f172a}
  .box{background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,.08);padding:2.5rem 2rem;max-width:440px;text-align:center;margin:1rem}
  .code{font-size:4.5rem;font-weight:800;color:#E87722;line-height:1}
  .ic{font-size:1.6rem;color:#64748b;margin:.5rem 0}
  h1{font-size:1.3rem;margin:.3rem 0}p{color:#475569}
  a{display:inline-block;margin-top:1rem;background:#E87722;color:#fff;text-decoration:none;padding:.6rem 1.4rem;border-radius:8px;font-weight:600}
  @media (prefers-color-scheme:dark){body{background:#0f172a;color:#e2e8f0}.box{background:#1e293b}p{color:#94a3b8}}
</style></head><body><div class="box">
  <div class="code"><?= $c ?></div><div class="ic"><i class="fa-solid <?= $info[2] ?>"></i></div>
  <h1><?= htmlspecialchars($info[0]) ?></h1><p><?= htmlspecialchars($info[1]) ?></p>
  <a href="<?= htmlspecialchars($base . $destino) ?>"><i class="fa-solid fa-house me-1"></i> Volver al inicio</a>
</div></body></html>

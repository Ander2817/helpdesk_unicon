<?php
// procesos/exportar_csv.php?tipo=tickets|inventario|bitacora|satisfaccion — descarga CSV (abre bien en Excel, con tildes).
// Admin: todo. Técnico: tickets (los suyos) e inventario si tiene el permiso inventario.ver. Usuario: nada.
// Filtros opcionales por la URL: estado, prioridad, categoria (ids), desde, hasta (YYYY-MM-DD), q (texto).
session_start();
if (!isset($_SESSION['usser'], $_SESSION['id_rol'])) { http_response_code(403); exit('No autorizado.'); }
require_once '../includes/constants.php';
require_once '../includes/permisos.php';
include '../config/conexion.php';
$rol = (int)$_SESSION['id_rol']; $uid = (int)($_SESSION['id_usuario'] ?? 0);
session_write_close();
$tipo = (string)($_GET['tipo'] ?? '');
$permitido = match ($tipo) {
    'tickets' => in_array($rol, [ROL_ADMIN, ROL_TECNICO], true),
    'inventario' => $rol === ROL_ADMIN || ($rol === ROL_TECNICO && rol_tiene_permiso($conexion, ROL_TECNICO, 'inventario.ver')),
    'bitacora', 'satisfaccion' => $rol === ROL_ADMIN,
    default => false,
};
if (!$permitido) { http_response_code(403); exit('No autorizado.'); }

$fecha = fn($k) => (isset($_GET[$k]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET[$k])) ? $_GET[$k] : null;
$desde = $fecha('desde'); $hasta = $fecha('hasta'); $texto = trim((string)($_GET['q'] ?? ''));
$where = []; $tipos = ''; $par = [];
$add = function (string $cond, string $t, $v) use (&$where, &$tipos, &$par) { $where[] = $cond; $tipos .= $t; $par[] = $v; };
$like = '%' . addcslashes($texto, '%_\\') . '%';

switch ($tipo) {
    case 'tickets':
        $sql = "SELECT t.codigo_ticket AS Codigo, t.asunto AS Asunto, e.nombre AS Estado, p.nombre AS Prioridad, c.nombre AS Categoria, d.nombre AS Departamento,
                       CONCAT(s.nombres,' ',s.apellidos) AS Solicitante, CONCAT(a.nombres,' ',a.apellidos) AS Tecnico, t.fecha_creacion AS Creado, t.fecha_cierre AS Cerrado,
                       ROUND(TIMESTAMPDIFF(MINUTE, t.fecha_creacion, t.fecha_cierre)/60, 1) AS Horas_resolucion, t.solucion AS Solucion
                FROM tickets t JOIN estados_ticket e ON e.id_estado_ticket = t.id_estado JOIN prioridades p ON p.id_prioridad = t.id_prioridad
                JOIN categoria_incidencias c ON c.id_categoria = t.id_categoria JOIN departamentos d ON d.id_departamento = t.id_departamento
                JOIN usuarios s ON s.id_usuario = t.id_usuario_solicitante LEFT JOIN usuarios a ON a.id_usuario = t.id_usuario_asignado";
        if ($rol === ROL_TECNICO) { $where[] = "(t.id_usuario_asignado = ? OR t.id_usuario_solicitante = ?)"; $tipos .= 'ii'; array_push($par, $uid, $uid); }
        if (isset($_GET['estado']) && ctype_digit((string)$_GET['estado'])) $add("t.id_estado = ?", 'i', (int)$_GET['estado']);
        if (isset($_GET['prioridad']) && ctype_digit((string)$_GET['prioridad'])) $add("t.id_prioridad = ?", 'i', (int)$_GET['prioridad']);
        if (isset($_GET['categoria']) && ctype_digit((string)$_GET['categoria'])) $add("t.id_categoria = ?", 'i', (int)$_GET['categoria']);
        if ($desde) $add("t.fecha_creacion >= ?", 's', $desde . ' 00:00:00');
        if ($hasta) $add("t.fecha_creacion <= ?", 's', $hasta . ' 23:59:59');
        if ($texto !== '') { $where[] = "(t.asunto LIKE ? OR t.codigo_ticket LIKE ?)"; $tipos .= 'ss'; $par[] = $like; $par[] = $like; }
        $orden = "ORDER BY t.fecha_creacion DESC"; break;
    case 'inventario':
        $sql = "SELECT i.codigo_equipo AS Codigo, i.tipo_equipo AS Tipo, i.marca AS Marca, i.modelo AS Modelo, i.serial AS Serial, i.estado AS Estado, i.ubicacion AS Ubicacion, d.nombre AS Departamento
                FROM inventario_tecnologico i JOIN departamentos d ON d.id_departamento = i.id_departamento";
        if (in_array($_GET['estado'] ?? '', ['activo', 'inactivo', 'mantenimiento'], true)) $add("i.estado = ?", 's', $_GET['estado']);
        if ($texto !== '') { $where[] = "(i.codigo_equipo LIKE ? OR i.tipo_equipo LIKE ? OR i.marca LIKE ? OR i.modelo LIKE ?)"; $tipos .= 'ssss'; array_push($par, $like, $like, $like, $like); }
        $orden = "ORDER BY i.codigo_equipo"; break;
    case 'bitacora':
        $sql = "SELECT fecha AS Fecha, usuario_login AS Usuario, accion AS Accion, tabla_afectada AS Tabla, id_registro AS Registro, descripcion AS Descripcion, ip AS IP FROM bitacora";
        if ($desde) $add("fecha >= ?", 's', $desde . ' 00:00:00');
        if ($hasta) $add("fecha <= ?", 's', $hasta . ' 23:59:59');
        if ($texto !== '') { $where[] = "(descripcion LIKE ? OR usuario_login LIKE ? OR accion LIKE ?)"; $tipos .= 'sss'; array_push($par, $like, $like, $like); }
        $orden = "ORDER BY fecha DESC"; break;
    default: // satisfaccion
        $sql = "SELECT t.codigo_ticket AS Ticket, e.puntuacion AS Estrellas, e.comentario AS Comentario, e.fecha AS Fecha, CONCAT(a.nombres,' ',a.apellidos) AS Tecnico, c.nombre AS Categoria
                FROM encuestas_ticket e JOIN tickets t ON t.id_tickets = e.id_ticket JOIN categoria_incidencias c ON c.id_categoria = t.id_categoria LEFT JOIN usuarios a ON a.id_usuario = t.id_usuario_asignado";
        if ($desde) $add("e.fecha >= ?", 's', $desde . ' 00:00:00');
        if ($hasta) $add("e.fecha <= ?", 's', $hasta . ' 23:59:59');
        $orden = "ORDER BY e.fecha DESC";
}
try {
    $st = $conexion->prepare($sql . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " $orden LIMIT 20000");
    if ($tipos !== '') $st->bind_param($tipos, ...$par);
    $st->execute(); $r = $st->get_result();
} catch (Throwable $e) { error_log('[exportar_csv] ' . $e->getMessage()); http_response_code(500); exit('No se pudo generar el archivo.'); }

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="helpdesk_' . $tipo . '_' . date('Ymd_His') . '.csv"');
header('Cache-Control: no-store');
$o = fopen('php://output', 'w');
fwrite($o, "\xEF\xBB\xBF");                      // BOM: Excel reconoce las tildes
$celda = function ($v) {                          // evita fórmulas en Excel (=, +, -, @) escritas por usuarios
    $v = (string)($v ?? '');
    return ($v !== '' && strpbrk($v[0], "=+-@\t\r") !== false) ? "'" . $v : $v;
};
$cab = false;
while ($f = $r->fetch_assoc()) {
    if (!$cab) { fputcsv($o, array_keys($f), ';'); $cab = true; }
    fputcsv($o, array_map($celda, $f), ';');
}
if (!$cab) fputcsv($o, ['Sin datos'], ';');
fclose($o);

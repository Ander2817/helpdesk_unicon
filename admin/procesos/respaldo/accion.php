<?php
// admin/procesos/respaldo/accion.php
// Controlador único del módulo de respaldo: crear | exportar | adjuntos | descargar | importar | restaurar | eliminar
// Solo POST, solo Administrador (id_rol = 3), con token CSRF.
session_start();

function denegar(string $msg = 'Acceso denegado.'): never {
    http_response_code(403);
    exit($msg);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') denegar();
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) denegar();
if (empty($_SESSION['csrf_respaldo']) || !hash_equals($_SESSION['csrf_respaldo'], (string)($_POST['csrf'] ?? ''))) {
    denegar('Token de seguridad inválido. Recarga la página e inténtalo de nuevo.');
}

require_once __DIR__ . '/../../../config/conexion.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/bitacora.php';
require_once __DIR__ . '/../../../includes/respaldo_lib.php';

$accion = $_POST['accion'] ?? '';
$login  = $_SESSION['usser'];
$volver = '../../respaldo.php';

/** Verifica la contraseña del administrador en sesión (se exige para restaurar). */
function clave_admin_valida(mysqli $c, string $login, string $clave): bool {
    $q = $c->prepare("SELECT contraseña FROM usuarios WHERE usuario_login = ? AND id_rol = 3");
    $q->bind_param("s", $login);
    $q->execute();
    $f = $q->get_result()->fetch_assoc();
    $q->close();
    return $f && $clave !== '' && password_verify($clave, $f['contraseña']);
}

function terminar(string $tipo, string $msj, string $volver): never {
    if (session_status() === PHP_SESSION_NONE) session_start(); // por si se cerró antes de una tarea larga
    set_alerta_flash($tipo, $msj);
    redirigir($volver);
}

@set_time_limit(0);
@ini_set('memory_limit', '512M');   // restaurar/exportar BD grandes
ignore_user_abort(true);

try {
    switch ($accion) {

        // ---------- Guardar un respaldo en el servidor ----------
        case 'crear':
            $ext    = (($_POST['formato'] ?? 'sql') === 'gz' && function_exists('gzopen')) ? '.sql.gz' : '.sql';
            $nombre = 'respaldo_' . date('Ymd_His') . $ext;
            session_write_close();
            $r = respaldo_generar($conexion, respaldo_dir() . DIRECTORY_SEPARATOR . $nombre);
            registrar_bitacora($conexion, 'BACKUP', null, null, "Creó el respaldo $nombre ({$r['tablas']} tablas, {$r['filas']} filas)");
            terminar('success', "Respaldo <strong>$nombre</strong> creado correctamente ({$r['tablas']} tablas, {$r['filas']} filas).", $volver);

        // ---------- Exportar y descargar al instante (no queda copia en el servidor) ----------
        case 'exportar':
            $ext = (($_POST['formato'] ?? 'sql') === 'gz' && function_exists('gzopen')) ? '.sql.gz' : '.sql';
            $nombre = 'helpdesk_unicon_' . date('Ymd_His') . $ext;
            $tmp = tempnam(sys_get_temp_dir(), 'bk') . $ext;
            session_write_close();
            $r = respaldo_generar($conexion, $tmp);
            registrar_bitacora($conexion, 'EXPORT', null, null, "Exportó la base de datos ($nombre, {$r['tablas']} tablas, {$r['filas']} filas)");
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $nombre . '"');
            header('Content-Length: ' . filesize($tmp));
            header('X-Content-Type-Options: nosniff');
            readfile($tmp);
            @unlink($tmp);
            exit();

        // ---------- Descargar los adjuntos de tickets (uploads/adjuntos) como .zip ----------
        case 'adjuntos':
            $nombre = 'adjuntos_' . date('Ymd_His') . '.zip';
            $tmp = tempnam(sys_get_temp_dir(), 'adj') . '.zip';
            session_write_close();
            $r = respaldo_zip_adjuntos($tmp);
            registrar_bitacora($conexion, 'EXPORT', null, null, "Descargó los adjuntos de tickets ($nombre, {$r['archivos']} archivos)");
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $nombre . '"');
            header('Content-Length: ' . filesize($tmp));
            header('X-Content-Type-Options: nosniff');
            readfile($tmp);
            @unlink($tmp);
            exit();

        // ---------- Descargar un respaldo guardado ----------
        case 'descargar':
            $ruta = respaldo_ruta((string)($_POST['nombre'] ?? ''));
            if (!$ruta) terminar('danger', 'El respaldo solicitado no existe.', $volver);
            session_write_close();
            registrar_bitacora($conexion, 'EXPORT', null, null, 'Descargó el respaldo ' . basename($ruta));
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($ruta) . '"');
            header('Content-Length: ' . filesize($ruta));
            header('X-Content-Type-Options: nosniff');
            readfile($ruta);
            exit();

        // ---------- Importar: subir un .sql / .sql.gz (queda validado y listado; NO se aplica) ----------
        case 'importar':
            $f = $_FILES['archivo'] ?? null;
            if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
                $causa = ($f['error'] ?? 0) === UPLOAD_ERR_INI_SIZE
                    ? 'El archivo supera el límite del servidor (upload_max_filesize = ' . ini_get('upload_max_filesize') . ').'
                    : 'No se recibió el archivo.';
                terminar('danger', $causa, $volver);
            }
            if ($f['size'] > RESPALDO_MAX_UPLOAD) terminar('danger', 'El archivo supera los ' . respaldo_formato_bytes(RESPALDO_MAX_UPLOAD) . '.', $volver);

            $orig = strtolower($f['name']);
            $es_gz = str_ends_with($orig, '.sql.gz');
            if (!$es_gz && !str_ends_with($orig, '.sql')) terminar('danger', 'Solo se aceptan archivos .sql o .sql.gz.', $volver);
            if ($es_gz && file_get_contents($f['tmp_name'], false, null, 0, 2) !== "\x1f\x8b") {
                terminar('danger', 'El archivo .gz no es un gzip válido.', $volver);
            }

            session_write_close();
            respaldo_validar($f['tmp_name']);   // lanza excepción si hay sentencias peligrosas o no es un respaldo
            $nombre = 'importado_' . date('Ymd_His') . ($es_gz ? '.sql.gz' : '.sql');
            if (!move_uploaded_file($f['tmp_name'], respaldo_dir() . DIRECTORY_SEPARATOR . $nombre)) {
                throw new RuntimeException('No se pudo guardar el archivo en la carpeta backups/.');
            }
            registrar_bitacora($conexion, 'IMPORT', null, null, "Importó el archivo " . mb_substr(basename($f['name']), 0, 120) . " como $nombre");
            terminar('success', "Archivo importado como <strong>$nombre</strong>. Ya aparece en la lista; usa «Restaurar» para aplicarlo.", $volver);

        // ---------- Restaurar ----------
        case 'restaurar':
            $ruta = respaldo_ruta((string)($_POST['nombre'] ?? ''));
            if (!$ruta) terminar('danger', 'El respaldo solicitado no existe.', $volver);
            if (($_POST['confirmar'] ?? '') !== 'RESTAURAR') terminar('warning', 'Debes escribir RESTAURAR para confirmar.', $volver);
            if (!clave_admin_valida($conexion, $login, (string)($_POST['password'] ?? ''))) {
                registrar_bitacora($conexion, 'RESTORE', null, null, 'Intento de restauración RECHAZADO: contraseña incorrecta (' . basename($ruta) . ')');
                terminar('danger', 'Contraseña incorrecta. No se realizó ningún cambio.', $volver);
            }
            session_write_close();
            $nombre = basename($ruta);
            $r = respaldo_restaurar($conexion, $ruta, ($_POST['incluir_bitacora'] ?? '') === '1');
            // Se escribe DESPUÉS de restaurar: queda registrada aunque se haya pedido restaurar también la bitácora del respaldo.
            $bit = (($_POST['incluir_bitacora'] ?? '') === '1') ? 'bitácora reemplazada por la del respaldo' : 'bitácora actual conservada';
            registrar_bitacora($conexion, 'RESTORE', null, null,
                "Restauró la base de datos desde $nombre ({$r['sentencias']} sentencias; $bit). Respaldo previo: {$r['respaldo_previo']}");
            require_once __DIR__ . '/../../../includes/notificaciones.php';
            noti_admins($conexion, "$login restauró la base de datos desde $nombre.", 'sistema', 'admin/bitacora.php', noti_id_actual($conexion));
            terminar('success', "Base de datos restaurada desde <strong>$nombre</strong>. Se guardó un respaldo previo: <strong>{$r['respaldo_previo']}</strong>.", $volver);

        // ---------- Eliminar un respaldo guardado ----------
        case 'eliminar':
            $ruta = respaldo_ruta((string)($_POST['nombre'] ?? ''));
            if (!$ruta) terminar('danger', 'El respaldo solicitado no existe.', $volver);
            $nombre = basename($ruta);
            if (!unlink($ruta)) throw new RuntimeException('No se pudo eliminar el archivo.');
            registrar_bitacora($conexion, 'DELETE', null, null, "Eliminó el respaldo $nombre");
            terminar('success', "Respaldo <strong>$nombre</strong> eliminado.", $volver);

        default:
            terminar('danger', 'Acción no válida.', $volver);
    }
} catch (Throwable $e) {
    error_log('[respaldo] ' . $e->getMessage());
    registrar_bitacora($conexion, 'ERROR', null, null, "Falló la acción de respaldo '$accion': " . mb_substr($e->getMessage(), 0, 300));
    terminar('danger', 'No se pudo completar la operación: ' . htmlspecialchars($e->getMessage()), $volver);
}

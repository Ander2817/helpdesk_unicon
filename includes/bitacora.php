<?php
// includes/bitacora.php
// Registro de acciones (bitácora). Uso:
//
//   require_once __DIR__ . '/../../../includes/bitacora.php';
//   registrar_bitacora($conexion, 'INSERT', 'usuarios', $nuevo_id, "Creó al usuario $user", null, ['usuario_login' => $user, ...]);
//
// Regla de oro: la bitácora NUNCA debe romper la operación principal. Si falla el registro,
// se escribe en el error_log de PHP y el flujo continúa.

/** Campos que jamás deben guardarse en la bitácora. */
const BITACORA_CAMPOS_SENSIBLES = ['contraseña', 'contrasena', 'password', 'confirm_password', 'clave', 'token'];

/** Quita campos sensibles de un arreglo antes de guardarlo. */
function bitacora_limpiar(?array $datos): ?array {
    if ($datos === null) return null;
    foreach ($datos as $k => $v) {
        if (in_array(strtolower((string)$k), BITACORA_CAMPOS_SENSIBLES, true)) {
            $datos[$k] = '[OCULTO]';
        } elseif (is_array($v)) {
            $datos[$k] = bitacora_limpiar($v);
        }
    }
    return $datos;
}

/**
 * Registra una acción.
 *
 * @param mysqli      $c           Conexión activa.
 * @param string      $accion      INSERT | UPDATE | DELETE | LOGIN | LOGOUT | BACKUP | EXPORT | IMPORT | RESTORE | ...
 * @param string|null $tabla       Tabla afectada (null si no aplica).
 * @param mixed       $id_registro Id del registro afectado.
 * @param string      $descripcion Texto legible: "Eliminó al usuario jperez".
 * @param array|null  $antes       Fila ANTES del cambio (UPDATE/DELETE).
 * @param array|null  $despues     Fila DESPUÉS del cambio (INSERT/UPDATE).
 */
function registrar_bitacora(mysqli $c, string $accion, ?string $tabla, $id_registro, string $descripcion, ?array $antes = null, ?array $despues = null): void {
    try {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $login = $_SESSION['usser'] ?? 'sistema';
        $rol   = isset($_SESSION['id_rol']) ? (int)$_SESSION['id_rol'] : null;
        $id_u  = !empty($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : null;

        // Tu login actual no guarda id_usuario en sesión: lo buscamos por el login (una vez por petición).
        if ($id_u === null && $login !== 'sistema') {
            static $cache = [];
            if (!array_key_exists($login, $cache)) {
                $q = $c->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
                $q->bind_param("s", $login);
                $q->execute();
                $f = $q->get_result()->fetch_assoc();
                $q->close();
                $cache[$login] = $f ? (int)$f['id_usuario'] : null;
            }
            $id_u = $cache[$login];
        }

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR;
        $json_antes   = $antes   !== null ? json_encode(bitacora_limpiar($antes), $flags)   : null;
        $json_despues = $despues !== null ? json_encode(bitacora_limpiar($despues), $flags) : null;

        // REMOTE_ADDR es la única IP confiable (X-Forwarded-For lo puede falsificar cualquiera).
        $ip  = substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45) ?: null;
        $ua  = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255) ?: null;
        $accion      = strtoupper(substr($accion, 0, 20));
        $descripcion = mb_substr($descripcion, 0, 500);
        $id_reg      = $id_registro !== null ? substr((string)$id_registro, 0, 64) : null;

        $stmt = $c->prepare(
            "INSERT INTO bitacora (id_usuario, usuario_login, id_rol, accion, tabla_afectada, id_registro, descripcion, datos_anteriores, datos_nuevos, ip, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isissssssss", $id_u, $login, $rol, $accion, $tabla, $id_reg, $descripcion, $json_antes, $json_despues, $ip, $ua);
        $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        error_log('[bitacora] No se pudo registrar: ' . $e->getMessage());
    }
}

/**
 * Lee una fila completa ANTES de modificarla/borrarla, para guardarla como "datos anteriores".
 * $tabla y $pk deben ser constantes del código (nunca input del usuario).
 */
function bitacora_fila(mysqli $c, string $tabla, string $pk, int $id): ?array {
    try {
        if (!preg_match('/^\w+$/', $tabla) || !preg_match('/^\w+$/', $pk)) return null;
        $q = $c->prepare("SELECT * FROM `$tabla` WHERE `$pk` = ? LIMIT 1");
        $q->bind_param("i", $id);
        $q->execute();
        $fila = $q->get_result()->fetch_assoc();
        $q->close();
        return $fila ?: null;
    } catch (Throwable $e) {
        error_log('[bitacora] No se pudo leer fila previa: ' . $e->getMessage());
        return null;
    }
}

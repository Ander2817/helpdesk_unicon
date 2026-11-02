<?php
// includes/login_guard.php — Límite de intentos de inicio de sesión.
// Regla: INTENTOS_FALLIDOS_MAX fallos (constants.php, 5) del mismo usuario desde la misma IP bloquean ese par
// durante LOGIN_BLOQUEO_SEGUNDOS (60 s). Pasado ese tiempo el contador se reinicia y vuelve a tener todos los intentos.
// Además, una IP con LOGIN_MAX_POR_IP fallos en la ventana queda bloqueada el mismo tiempo (ataque a varios usuarios).
// Si la tabla login_intentos no existe todavía, el login sigue funcionando (sin límite).
require_once __DIR__ . '/constants.php';

const LOGIN_BLOQUEO_SEGUNDOS = 60;   // <-- segundos de espera tras agotar los intentos (cámbialo aquí)
const LOGIN_VENTANA_MIN      = 15;   // los fallos se cuentan dentro de esta ventana
const LOGIN_MAX_POR_IP       = 25;

function login_ip(): string { return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45); }

/** Segundos que faltan para poder reintentar, o null si no está bloqueado (el cálculo lo hace la BD: no depende de zonas horarias). */
function login_bloqueado(mysqli $c, string $usuario): ?int {
    try {
        $ip = login_ip(); $v = LOGIN_VENTANA_MIN; $b = LOGIN_BLOQUEO_SEGUNDOS;
        $resta = 0;

        $q = $c->prepare("SELECT COUNT(*) n, TIMESTAMPDIFF(SECOND, NOW(), MAX(fecha) + INTERVAL $b SECOND) resta
                          FROM login_intentos WHERE usuario_login = ? AND ip = ? AND fecha > (NOW() - INTERVAL $v MINUTE)");
        $q->bind_param("ss", $usuario, $ip); $q->execute();
        $f = $q->get_result()->fetch_assoc(); $q->close();
        if ((int)$f['n'] >= INTENTOS_FALLIDOS_MAX) {
            if ((int)$f['resta'] > 0) $resta = (int)$f['resta'];
            else login_limpiar($c, $usuario);          // ya cumplió la espera: contador a cero
        }

        $q = $c->prepare("SELECT COUNT(*) n, TIMESTAMPDIFF(SECOND, NOW(), MAX(fecha) + INTERVAL $b SECOND) resta
                          FROM login_intentos WHERE ip = ? AND fecha > (NOW() - INTERVAL $v MINUTE)");
        $q->bind_param("s", $ip); $q->execute();
        $g = $q->get_result()->fetch_assoc(); $q->close();
        if ((int)$g['n'] >= LOGIN_MAX_POR_IP) {
            if ((int)$g['resta'] > 0) $resta = max($resta, (int)$g['resta']);
            else { $d = $c->prepare("DELETE FROM login_intentos WHERE ip = ?"); $d->bind_param("s", $ip); $d->execute(); $d->close(); }
        }

        return $resta > 0 ? $resta : null;
    } catch (Throwable $e) { error_log('[login_guard] ' . $e->getMessage()); return null; }
}

/** Intentos que le quedan a ese usuario antes del bloqueo (0 si ya está bloqueado). */
function login_intentos_restantes(mysqli $c, string $usuario): int {
    try {
        $ip = login_ip(); $v = LOGIN_VENTANA_MIN;
        $q = $c->prepare("SELECT COUNT(*) n FROM login_intentos WHERE usuario_login = ? AND ip = ? AND fecha > (NOW() - INTERVAL $v MINUTE)");
        $q->bind_param("ss", $usuario, $ip); $q->execute();
        $n = (int)$q->get_result()->fetch_assoc()['n']; $q->close();
        return max(0, INTENTOS_FALLIDOS_MAX - $n);
    } catch (Throwable $e) { return INTENTOS_FALLIDOS_MAX; }
}

/** Anota un fallo. Devuelve true si con este fallo el usuario queda bloqueado. */
function login_registrar_fallo(mysqli $c, string $usuario): bool {
    try {
        $ip = login_ip();
        $q = $c->prepare("INSERT INTO login_intentos (usuario_login, ip) VALUES (?, ?)");
        $q->bind_param("ss", $usuario, $ip); $q->execute(); $q->close();
        if (random_int(1, 50) === 1) $c->query("DELETE FROM login_intentos WHERE fecha < (NOW() - INTERVAL 2 DAY)");
        return login_bloqueado($c, $usuario) !== null;
    } catch (Throwable $e) { return false; }
}

function login_limpiar(mysqli $c, string $usuario): void {
    try {
        $ip = login_ip();
        $q = $c->prepare("DELETE FROM login_intentos WHERE usuario_login = ? AND ip = ?");
        $q->bind_param("ss", $usuario, $ip); $q->execute(); $q->close();
    } catch (Throwable $e) {}
}

/** "1 segundo" / "45 segundos" / "1 minuto y 5 segundos" */
function login_texto_espera(int $s): string {
    $s = max(1, $s);
    if ($s < 60) return $s . ($s === 1 ? ' segundo' : ' segundos');
    $m = intdiv($s, 60); $r = $s % 60;
    return $m . ($m === 1 ? ' minuto' : ' minutos') . ($r ? ' y ' . $r . ($r === 1 ? ' segundo' : ' segundos') : '');
}

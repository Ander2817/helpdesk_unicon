<?php
// includes/estados.php — Catálogo de estados de usuarios, equipos y departamentos SIN tablas nuevas.
// Los estados siguen siendo ENUM (como hoy); aquí se leen de la propia base, se les da etiqueta/color y se puede
// AGREGAR un estado nuevo de forma segura (ALTER ... MODIFY conservando los existentes). Qué estados bloquean el login
// se guarda en sistema_estado (clave 'estados_bloquean_login').

if (!function_exists('est_catalogo')) {

function est_catalogo(): array {
    return [
        'usuarios' => ['tabla' => 'usuarios', 'pk' => 'id_usuario', 'titulo' => 'Usuarios', 'icono' => 'fa-users',
                       'protegidos' => ['activo', 'inactivo']],
        'equipos'  => ['tabla' => 'inventario_tecnologico', 'pk' => 'id_inventario', 'titulo' => 'Equipos', 'icono' => 'fa-laptop',
                       'protegidos' => ['activo', 'inactivo', 'mantenimiento']],
        'departamentos' => ['tabla' => 'departamentos', 'pk' => 'id_departamento', 'titulo' => 'Departamentos', 'icono' => 'fa-building',
                       'protegidos' => ['activo', 'inactivo']],
    ];
}

/** Valores actuales del ENUM de tabla.estado (en el orden definido en la BD). */
function est_valores(mysqli $c, string $tabla): array {
    $permitidas = array_column(est_catalogo(), 'tabla');
    if (!in_array($tabla, $permitidas, true)) return [];
    $r = $c->query("SHOW COLUMNS FROM `$tabla` LIKE 'estado'")->fetch_assoc();
    if (!$r || !preg_match("/^enum\((.*)\)$/i", $r['Type'], $m)) return [];
    preg_match_all("/'((?:[^']|'')*)'/", $m[1], $v);
    return array_map(fn($x) => str_replace("''", "'", $x), $v[1]);
}

function est_etiqueta(string $v): string { return ucfirst(str_replace('_', ' ', $v)); }

function est_color(string $v): string {
    static $mapa = ['activo' => '#16a34a', 'inactivo' => '#64748b', 'suspendido' => '#dc2626', 'clausurado' => '#dc2626', 'mantenimiento' => '#d97706',
                    'reposo' => '#0284c7', 'vacaciones' => '#0284c7', 'pasante' => '#7c3aed'];
    return $mapa[$v] ?? '#475569';
}

/** Estados que impiden iniciar sesión. */
function estados_bloquean_login(?mysqli $c = null): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = ['inactivo', 'suspendido'];
    if ($c) {
        try {
            $r = $c->query("SELECT valor FROM sistema_estado WHERE clave = 'estados_bloquean_login'");
            if ($r && ($f = $r->fetch_assoc()) && trim($f['valor']) !== '') {
                $lista = array_values(array_filter(array_map('trim', explode(',', $f['valor']))));
                if ($lista) $cache = $lista;
            }
        } catch (Throwable $e) { /* se usan los valores por defecto */ }
    }
    return $cache;
}
function estado_bloquea_login(mysqli $c, string $estado): bool { return in_array($estado, estados_bloquean_login($c), true); }

function est_guardar_bloqueantes(mysqli $c, array $lista): void {
    $lista = array_values(array_unique(array_intersect($lista, est_valores($c, 'usuarios'))));
    foreach (['inactivo', 'suspendido'] as $obl) if (!in_array($obl, $lista, true)) $lista[] = $obl;   // estos dos siempre bloquean
    $v = implode(',', $lista);
    $s = $c->prepare("INSERT INTO sistema_estado (clave, valor) VALUES ('estados_bloquean_login', ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
    $s->bind_param("s", $v); $s->execute(); $s->close();
}

/** Agrega un valor nuevo al ENUM conservando los existentes, NOT NULL y el DEFAULT. Devuelve [ok, mensaje]. */
function est_agregar_valor(mysqli $c, string $clave, string $nuevo): array {
    $cat = est_catalogo();
    if (!isset($cat[$clave])) return [false, 'Tipo no válido.'];
    $nuevo = strtolower(trim($nuevo));
    if (!preg_match('/^[a-z][a-z_]{2,19}$/', $nuevo)) return [false, 'Use solo letras minúsculas y guion bajo (3 a 20 caracteres), sin tildes ni espacios. Ej.: licencia_medica'];
    $tabla = $cat[$clave]['tabla'];
    $actuales = est_valores($c, $tabla);
    if (!$actuales) return [false, 'No se pudo leer la definición de la columna.'];
    if (in_array($nuevo, $actuales, true)) return [false, 'Ese estado ya existe.'];
    $col = $c->query("SHOW COLUMNS FROM `$tabla` LIKE 'estado'")->fetch_assoc();
    $def = $col['Default'] ?? 'activo';
    $lista = implode(',', array_map(fn($x) => "'" . $c->real_escape_string($x) . "'", array_merge($actuales, [$nuevo])));
    $sql = "ALTER TABLE `$tabla` MODIFY `estado` ENUM($lista) NOT NULL DEFAULT '" . $c->real_escape_string($def) . "'";
    try { $c->query($sql); } catch (Throwable $e) { return [false, 'La base de datos rechazó el cambio: ' . $e->getMessage()]; }
    return [true, "Estado «" . est_etiqueta($nuevo) . "» agregado a " . $cat[$clave]['titulo'] . '.'];
}

}

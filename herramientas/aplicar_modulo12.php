<?php
// herramientas/aplicar_modulo12.php — aplica el módulo 12 (header/footer en TODAS las pantallas, móvil y asignación automática)
// a archivos que YA existen en tu proyecto.
// Uso (desde la carpeta del proyecto):   C:\xampp\php\php.exe herramientas\aplicar_modulo12.php
// Seguro de repetir: no duplica nada, guarda copia «.bak12» de cada archivo y, si el resultado tuviera un error de sintaxis, lo revierte solo.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Solo desde la línea de comandos.'); }
$raiz = rtrim($argv[1] ?? dirname(__DIR__), '/\\');
$hecho = 0; $manual = [];

function sintaxis_ok(string $archivo): bool {
    if (substr($archivo, -4) !== '.php') return true;
    $out = []; $rc = 0;
    @exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($archivo) . ' 2>&1', $out, $rc);
    return $rc === 0;
}
function respaldo(string $f): string { $b = $f . '.bak12'; if (is_file($b)) $b .= date('_His'); copy($f, $b); return $b; }

/** Parchea un archivo existente. $fn recibe el texto (con \n) y devuelve el nuevo texto o null si no halló el punto. */
function parchear(string $raiz, string $rel, string $marca, callable $fn, string $manual_txt, array &$manual, int &$hecho): void {
    $f = $raiz . '/' . $rel;
    if (!is_file($f)) { echo "[--] $rel no existe: se omite.\n"; return; }
    $orig = str_replace("\r\n", "\n", file_get_contents($f));
    if (strpos($orig, $marca) !== false) { echo "[ok] $rel ya estaba aplicado ($marca).\n"; return; }
    $n = $fn($orig);
    if ($n === null || $n === $orig) { echo "[!!] $rel: no encontré el punto de inserción.\n"; $manual[] = "$rel → $manual_txt"; return; }
    $bak = respaldo($f);
    file_put_contents($f, $n);
    if (!sintaxis_ok($f)) { copy($bak, $f); echo "[!!] $rel: el cambio daba error de sintaxis; se revirtió.\n"; $manual[] = "$rel → $manual_txt"; return; }
    $hecho++; echo "[+]  $rel actualizado (copia: " . basename($bak) . ").\n";
}

/** Convierte una página «suelta» (con su propio <html>) en una página con header, menú y footer del sistema.
 *  $reemplazos: [regex => reemplazo] que se aplican al PHP inicial, al contenido y a los scripts (rutas que cambian al mover el archivo). */
function convertir_pagina(string $s, array $reemplazos): ?string {
    if (strpos($s, 'includes/header2.php') !== false) return null;
    if (!preg_match('/^(<\?php.*?\?>)\s*<!DOCTYPE[^>]*>\s*<html[^>]*>\s*<head>(.*?)<\/head>\s*<body[^>]*>(.*)<\/body>\s*<\/html>\s*$/si', $s, $m)) return null;
    [, $php, $head, $body] = $m;

    // Estilos propios de la página y recursos extra del <head> (se conservan; lo que ya trae el header no se repite)
    $extra = '';
    if (preg_match_all('/<style\b[^>]*>.*?<\/style>/si', $head, $mm)) $extra .= implode("\n", $mm[0]) . "\n";
    if (preg_match_all('/<link\b[^>]*>/i', $head, $mm)) foreach ($mm[0] as $l)
        if (!preg_match('/fonts\.googleapis|bootstrap|font-awesome|dashboard2?\.css/i', $l)) $extra .= $l . "\n";
    if (preg_match_all('/<script\b[^>]*\bsrc=[^>]*><\/script>/i', $head, $mm)) foreach ($mm[0] as $l)
        if (!preg_match('/bootstrap/i', $l)) $extra .= $l . "\n";

    // Scripts del cuerpo: se mueven DESPUÉS del footer (que es quien carga Bootstrap), para que corran con todo cargado
    $scripts = '';
    $body = preg_replace_callback('/<script\b[^>]*>.*?<\/script>/si', function ($x) use (&$scripts) {
        if (preg_match('/src=[^>]*bootstrap[^>]*bundle/i', $x[0])) return '';   // el footer ya lo carga
        $scripts .= $x[0] . "\n"; return '';
    }, $body);

    foreach ($reemplazos as $re => $to) { $php = preg_replace($re, $to, $php); $body = preg_replace($re, $to, $body); $scripts = preg_replace($re, $to, $scripts); $extra = preg_replace($re, $to, $extra); }

    return $php . "\n<?php /* m12-layout: header, menú y footer del sistema */\n"
        . "require_once __DIR__ . '/../includes/header2.php';\n"
        . "require_once __DIR__ . '/../includes/sidebar.php';\n?>\n"
        . "<main class=\"hd-main\">\n" . $extra . trim($body) . "\n</main>\n"
        . "<?php ob_start(); ?>\n" . trim($scripts) . "\n<?php\n\$hd_extra_scripts = ob_get_clean();\n"
        . "require_once __DIR__ . '/../includes/footer2.php';\n"
        . "if (empty(\$hd_scripts_done)) echo \$hd_extra_scripts;\n";
}

/** Mueve/convierte una página. $origen y $destino son rutas relativas a la raíz; si son distintas, el origen queda como redirección. */
function mover_pagina(string $raiz, string $origen, string $destino, array $reemplazos, array &$manual, int &$hecho): void {
    $fo = $raiz . '/' . $origen; $fd = $raiz . '/' . $destino;
    if (!is_file($fo)) { echo "[--] $origen no existe: se omite.\n"; return; }
    if ($origen !== $destino && is_file($fd)) {
        $t = file_get_contents($fd);
        if (strpos($t, 'm12-layout') !== false) { echo "[ok] $destino ya estaba aplicado.\n"; return; }
        echo "[!!] $destino ya existe y no es de este módulo: no lo toco.\n"; $manual[] = "$destino ya existe; revisa que use header2.php/sidebar.php/footer2.php"; return;
    }
    $orig = str_replace("\r\n", "\n", file_get_contents($fo));
    if (strpos($orig, 'm12-layout') !== false) { echo "[ok] $origen ya estaba aplicado.\n"; return; }
    if (strpos($orig, 'm12-redirect') !== false) { echo "[ok] $origen ya redirige.\n"; return; }
    $n = convertir_pagina($orig, $reemplazos);
    if ($n === null) { echo "[!!] $origen: su estructura es distinta a la esperada.\n"; $manual[] = "$origen → ver GUIA.md, sección «Convertir una página a mano»"; return; }
    $bak = respaldo($fo);
    file_put_contents($fd, $n);
    if (!sintaxis_ok($fd)) { if ($origen !== $destino) @unlink($fd); else copy($bak, $fo); echo "[!!] $origen: el resultado daba error de sintaxis; se revirtió.\n"; $manual[] = "$origen → ver GUIA.md"; return; }
    if ($origen !== $destino) {
        $volver = str_repeat('../', substr_count($origen, '/') - substr_count($destino, '/')) . basename($destino);
        file_put_contents($fo, "<?php /* m12-redirect: esta pantalla se movió a $destino */\nheader('Location: $volver'); exit();\n");
    }
    $hecho++; echo "[+]  $origen → " . ($origen === $destino ? 'ahora usa header/menú/footer' : "movido a $destino (el archivo viejo redirige)") . " (copia: " . basename($bak) . ").\n";
}

// ======================================================================================================
// 1) HEADER: carga el CSS y el JS móvil (botón ☰) + enlace de Usuarios a su nueva ubicación
parchear($raiz, 'includes/header2.php', 'm12-responsive', function ($s) {
    $r = preg_replace('#(<link[^>]+dashboard2\.css[^>]*>)#i', "$1\n    <link rel=\"stylesheet\" href=\"../assets/css/responsive.css\"> <!-- m12-responsive -->", $s, 1, $n1);
    if (!$n1) return null;
    $r = preg_replace('#</head>#i', "    <script src=\"../assets/js/responsive.js\" defer></script>\n</head>", $r, 1, $n2);
    if (!$n2) return null;
    return str_replace('href="procesos/usuarios.php"', 'href="usuarios.php"', $r);
}, 'en el <head> añade <link rel="stylesheet" href="../assets/css/responsive.css"> después de dashboard2.css y <script src="../assets/js/responsive.js" defer></script> antes de </head>', $manual, $hecho);

// 2) FOOTER: si una página dejó scripts pendientes, se imprimen al final (los usa la conversión de páginas)
parchear($raiz, 'includes/footer2.php', 'm12-scripts', function ($s) {
    $r = preg_replace('#</body>#i', "<?php /* m12-scripts */ if (!empty(\$hd_extra_scripts)) { echo \$hd_extra_scripts; \$hd_scripts_done = true; } ?>\n</body>", $s, 1, $n);
    return $n ? $r : null;
}, 'justo antes de </body> añade: <?php if (!empty($hd_extra_scripts)) { echo $hd_extra_scripts; $hd_scripts_done = true; } ?>', $manual, $hecho);

// 3) MENÚ: Usuarios y Departamentos apuntan a la nueva ubicación + enlace «Asignación de tickets»
parchear($raiz, 'includes/sidebar.php', 'm12-asignacion', function ($s) {
    $s = str_replace(['href="procesos/usuarios.php"', 'href="procesos/departamentos.php"'], ['href="usuarios.php"', 'href="departamentos.php"'], $s);
    $enlace = "\n        <?php /* m12-asignacion */ ?>\n        <a href=\"asignacion.php\" class=\"hd-sidebar-link <?= \$pagina_actual=='asignacion.php'?'active':'' ?>\">\n            <i class=\"fas fa-user-gear\"></i> Asignación de tickets\n        </a>";
    $r = preg_replace('#(<a href="tickets\.php\?estado=sin_asignar".*?</a>)#s', '$1' . $enlace, $s, 1, $n);
    return $n ? $r : null;
}, 'en el menú del administrador (sección Tickets) añade un enlace a asignacion.php con el icono fa-user-gear', $manual, $hecho);

// 4) Enlaces sueltos hacia las pantallas movidas (panel del administrador y header antiguo)
foreach (['admin/dashboard.php', 'includes/header.php'] as $rel) {
    $f = "$raiz/$rel";
    if (!is_file($f)) continue;
    $t = file_get_contents($f);
    $n = str_replace(['href="procesos/usuarios.php"', 'href="../admin/procesos/usuarios.php"', 'href="procesos/departamentos.php"', 'href="../admin/procesos/departamentos.php"'],
                     ['href="usuarios.php"', 'href="../admin/usuarios.php"', 'href="departamentos.php"', 'href="../admin/departamentos.php"'], $t);
    if ($n !== $t) { respaldo($f); file_put_contents($f, $n); echo "[+]  $rel: enlaces a Usuarios/Departamentos actualizados.\n"; $hecho++; }
}

// 5) PANTALLAS que se veían sin header/menú/footer
$rutas_admin = [
    '#(["\'\s])\.\./\.\./(config|includes|index\.php|auth|assets|uploads)#' => '$1../$2',
    '#href="\.\./dashboard\.php"#' => 'href="dashboard.php"',
    '#(["\'])(usuarios|departamentos)/([A-Za-z_]+\.php)#' => '$1procesos/$2/$3',
];
mover_pagina($raiz, 'admin/procesos/usuarios.php', 'admin/usuarios.php', $rutas_admin, $manual, $hecho);
mover_pagina($raiz, 'admin/procesos/departamentos.php', 'admin/departamentos.php', $rutas_admin, $manual, $hecho);
mover_pagina($raiz, 'usuario/mis_tickets.php', 'usuario/mis_tickets.php', [], $manual, $hecho);
mover_pagina($raiz, 'usuario/ticket_nuevo.php', 'usuario/ticket_nuevo.php', [], $manual, $hecho);

// 6) ASIGNACIÓN AUTOMÁTICA al crear un ticket (después de guardarlo)
parchear($raiz, 'procesos/create_ticket.php', 'm12-asignacion', function ($s) {
    $hook = "\n        // m12-asignacion: asignación automática (si el administrador la tiene activada). Si falla, el ticket ya quedó creado.\n"
          . "        try { if (is_file(__DIR__ . '/../includes/asignacion.php')) { require_once __DIR__ . '/../includes/asignacion.php'; asig_auto(\$conexion, (int)\$id_ticket_nuevo); } } catch (Throwable \$e_asig) { /* sin asignar: el admin lo verá en la cola */ }\n";
    $r = preg_replace('#(adjuntos_guardar\([^;]*;\s*\$conexion->commit\(\);)#', '$1' . $hook, $s, 1, $n);
    if (!$n) $r = preg_replace('#(\$conexion->commit\(\);)#', '$1' . $hook, $s, 1, $n);
    return $n ? $r : null;
}, 'justo después de $conexion->commit(); (el que sigue a adjuntos_guardar) añade: require_once __DIR__."/../includes/asignacion.php"; asig_auto($conexion, (int)$id_ticket_nuevo);', $manual, $hecho);

echo "\n";
if ($manual) {
    echo "Quedaron " . count($manual) . " paso(s) para hacer a mano (detalle en GUIA.md):\n";
    foreach ($manual as $m) echo "  - $m\n";
}
echo "Listo. Cambios aplicados: $hecho.\n";

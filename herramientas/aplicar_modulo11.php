<?php
// herramientas/aplicar_modulo11.php — aplica los cambios del módulo 11 a archivos que YA existen en tu proyecto.
// Uso (desde la carpeta del proyecto):   C:\xampp\php\php.exe herramientas\aplicar_modulo11.php
// Seguro de repetir: no duplica nada, guarda copia «.bak11» de cada archivo y, si el resultado tuviera un error de sintaxis, lo revierte solo.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Solo desde la línea de comandos.'); }
$raiz = rtrim($argv[1] ?? dirname(__DIR__), '/\\');
$hecho = 0; $manual = [];

function sintaxis_ok(string $archivo): bool {
    if (substr($archivo, -4) !== '.php') return true;
    $php = PHP_BINARY; $out = []; $rc = 0;
    @exec(escapeshellarg($php) . ' -l ' . escapeshellarg($archivo) . ' 2>&1', $out, $rc);
    return $rc === 0;
}
function parchear(string $raiz, string $rel, string $marca, callable $fn, string $manual_txt, array &$manual, int &$hecho): void {
    $f = $raiz . '/' . $rel;
    if (!is_file($f)) { echo "[--] $rel no existe: se omite.\n"; return; }
    $orig = file_get_contents($f);
    if (strpos($orig, $marca) !== false) { echo "[ok] $rel ya estaba aplicado ($marca).\n"; return; }
    $n = $fn(str_replace("\r\n", "\n", $orig));
    if ($n === null || $n === str_replace("\r\n", "\n", $orig)) { echo "[!!] $rel: no encontré el punto de inserción.\n"; $manual[] = "$rel → $manual_txt"; return; }
    $bak = $f . '.bak11'; if (is_file($bak)) $bak .= date('_His'); copy($f, $bak);
    file_put_contents($f, $n);
    if (!sintaxis_ok($f)) { copy($bak, $f); echo "[!!] $rel: el cambio daba error de sintaxis; se revirtió.\n"; $manual[] = "$rel → $manual_txt"; return; }
    $hecho++; echo "[+]  $rel actualizado (copia: " . basename($bak) . ").\n";
}

// 1) Menú del administrador: Gestión de estados, Reporte de gestión, Calendario, Etiquetas QR
parchear($raiz, 'includes/sidebar.php', 'm11-admin', function ($s) {
    $enlaces = <<<'H'
        <?php /* m11-admin */ ?>
        <a href="reportes_gestion.php" class="hd-sidebar-link <?= $pagina_actual=='reportes_gestion.php'?'active':'' ?>">
            <i class="fas fa-chart-line"></i> Reporte de gestión
        </a>
        <a href="calendario_mantenimientos.php" class="hd-sidebar-link <?= $pagina_actual=='calendario_mantenimientos.php'?'active':'' ?>">
            <i class="fas fa-calendar-days"></i> Calendario mantenimientos
        </a>
        <a href="qr_equipos.php" class="hd-sidebar-link <?= $pagina_actual=='qr_equipos.php'?'active':'' ?>">
            <i class="fas fa-qrcode"></i> Etiquetas QR
        </a>
        <a href="gestion_estados.php" class="hd-sidebar-link <?= $pagina_actual=='gestion_estados.php'?'active':'' ?>">
            <i class="fas fa-toggle-on"></i> Gestión de estados
        </a>

H;
    $r = preg_replace('~(<a href="estado_sistema\.php"[^>]*>.*?</a>\s*\n)~s', '$1' . $enlaces, $s, 1, $n);
    if ($n) return $r;
    return preg_replace('~(<a href="reportes\.php"[^>]*>.*?</a>\s*\n)~s', '$1' . $enlaces, $s, 1);
}, 'añade en el menú del admin los enlaces a reportes_gestion.php, calendario_mantenimientos.php, qr_equipos.php y gestion_estados.php (ver GUIA).', $manual, $hecho);

// 2) Menú del técnico: Calendario (segundo enlace a inventario.php = el del técnico)
parchear($raiz, 'includes/sidebar.php', 'm11-tec', function ($s) {
    if (!preg_match_all('~<a href="inventario\.php"[^>]*>.*?</a>[ \t]*\n~s', $s, $m, PREG_OFFSET_CAPTURE) || count($m[0]) < 2) return null;
    [$txt, $pos] = $m[0][1];
    $enlace = "        <?php /* m11-tec */ ?>\n        <a href=\"calendario_mantenimientos.php\" class=\"hd-sidebar-link <?= \$pagina_actual=='calendario_mantenimientos.php'?'active':'' ?>\">\n            <i class=\"fas fa-calendar-days\"></i> Calendario mantenimientos\n        </a>\n";
    return substr($s, 0, $pos + strlen($txt)) . $enlace . substr($s, $pos + strlen($txt));
}, 'en el menú del técnico, después de «Inventario TI», añade un enlace a calendario_mantenimientos.php (ver GUIA).', $manual, $hecho);

// 3) Login: los estados que bloquean el acceso salen de la configuración de «Gestión de estados»
parchear($raiz, 'auth/login.php', 'estado_bloquea_login', function ($s) {
    $s = preg_replace("~require_once\('\.\./includes/functions\.php'\);~", "require_once('../includes/functions.php');\nrequire_once('../includes/estados.php');", $s, 1);
    $r = str_replace("if (in_array(\$row['estado'], ['inactivo', 'suspendido'], true)) {", "if (estado_bloquea_login(\$conexion, \$row['estado'])) {", $s, $n);
    return $n ? $r : null;
}, "en auth/login.php cambia in_array(\$row['estado'], ['inactivo','suspendido'], true) por estado_bloquea_login(\$conexion, \$row['estado']) y agrega require_once('../includes/estados.php').", $manual, $hecho);

// 4) usuarios.php: «eliminar» ahora desactiva y el mensaje depende de la respuesta real del servidor
parchear($raiz, 'admin/procesos/usuarios.php', 'm11-softdelete', function ($s) {
    $nuevo = "// m11-softdelete\n                    if (String(response).trim() !== 'success') {\n                        Swal.fire('No se pudo completar', String(response).replace(/<[^>]*>/g, ''), 'error');\n                        return;\n                    }\n                    Swal.fire({icon: 'success', title: 'Usuario desactivado', text: 'No se borró: pasó a estado Inactivo y conserva su historial.', timer: 2200, showConfirmButton: false}).then(() => location.reload());";
    $r = preg_replace('~//\s*Si el backend responde con un fragmento HTML.*?\$\(this\)\.remove\(\);\s*\}\);~s', $nuevo, $s, 1, $n);
    if (!$n) return null;
    $r = str_replace("Esta acción no se puede deshacer.", "El usuario pasará a Inactivo (no se borra su historial).", $r);
    return str_replace("Sí, eliminar", "Sí, desactivar", $r);
}, 'reemplaza el success del $.ajax de .btn-eliminar por el código de la GUIA (apartado «Borrado lógico»).', $manual, $hecho);

// 5) Nuevo ticket: equipo preseleccionado cuando se llega desde un QR (?equipo=ID)
parchear($raiz, 'usuario/ticket_nuevo.php', 'm11-equipo', function ($s) {
    $js = "<script>/* m11-equipo */(function(){var m=location.search.match(/[?&]equipo=(\\d+)/);if(!m)return;var s=document.getElementById('select-inventario');if(!s)return;s.value=m[1];if(window.jQuery)window.jQuery(s).trigger('change');})();</script>\n";
    return preg_replace('~</body>~i', $js . '</body>', $s, 1);
}, 'antes de </body> pega el <script> de preselección de equipo (ver GUIA).', $manual, $hecho);

echo "\nCambios aplicados: $hecho\n";
if ($manual) { echo "Pendiente manual:\n"; foreach ($manual as $m) echo " - $m\n"; }

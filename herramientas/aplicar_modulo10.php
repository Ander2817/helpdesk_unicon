<?php
// herramientas/aplicar_modulo10.php — aplica los pequeños cambios del módulo 10 a archivos que YA existen en tu proyecto.
// Uso (desde la carpeta del proyecto):   C:\xampp\php\php.exe herramientas\aplicar_modulo10.php
// Es seguro repetirlo (no duplica nada) y guarda una copia «.bak10» de cada archivo que cambia.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Solo desde la línea de comandos.'); }
$raiz = rtrim($argv[1] ?? dirname(__DIR__), '/\\');
$hecho = 0; $manual = [];

function parchear(string $raiz, string $rel, string $marca, callable $fn, string $manual_txt, array &$manual, int &$hecho): void {
    $f = $raiz . '/' . $rel;
    if (!is_file($f)) { echo "[--] $rel no existe: se omite.\n"; return; }
    $s = file_get_contents($f);
    if (strpos($s, $marca) !== false) { echo "[ok] $rel ya estaba aplicado.\n"; return; }
    $n = $fn($s);
    if ($n === null || $n === $s) { echo "[!!] $rel: no encontré el punto de inserción.\n"; $manual[] = "$rel → $manual_txt"; return; }
    $bak = $f . '.bak10'; if (is_file($bak)) $bak .= date('_His'); copy($f, $bak); file_put_contents($f, $n); $hecho++;
    echo "[+]  $rel actualizado (copia de seguridad: ".basename($bak).").\n";
}

// 1) Menú lateral del admin: enlaces "Estado del sistema" y "Satisfacción"
parchear($raiz, 'includes/sidebar.php', 'estado_sistema.php', function ($s) {
    $enlaces = <<<'H'
        <a href="satisfaccion.php" class="hd-sidebar-link <?= $pagina_actual=='satisfaccion.php'?'active':'' ?>">
            <i class="fas fa-star"></i> Satisfacción
        </a>
        <a href="estado_sistema.php" class="hd-sidebar-link <?= $pagina_actual=='estado_sistema.php'?'active':'' ?>">
            <i class="fas fa-heartbeat"></i> Estado del sistema
        </a>

H;
    // se inserta justo después del enlace "Reportes" del administrador
    return preg_replace('~(<a href="reportes\.php"[^>]*>.*?</a>\s*\n)~s', '$1' . $enlaces, $s, 1);
}, 'añade, después del enlace de Reportes, los enlaces a satisfaccion.php y estado_sistema.php (están en la GUIA).', $manual, $hecho);

// 2) Tareas automáticas (autocierre + avisos de mantenimiento) junto a la revisión de SLA de la campana
parchear($raiz, 'procesos/notificaciones.php', 'tareas_programadas.php', function ($s) {
    $add = "\nif (is_file(__DIR__ . '/../includes/tareas_programadas.php')) { require_once __DIR__ . '/../includes/tareas_programadas.php'; tareas_ejecutar(\$conexion); }   // autocierre y avisos de mantenimiento (cada 15 min)";
    return preg_replace('~(if \(function_exists\(\'sla_revisar_vencidos\'\)\) sla_revisar_vencidos\(\$conexion\);[^\n]*)~', '$1' . $add, $s, 1);
}, 'debajo de la línea que llama a sla_revisar_vencidos($conexion) pega la línea de tareas_ejecutar (está en la GUIA).', $manual, $hecho);

// 3) Formulario de ticket del usuario: equipos de cualquier departamento (los del suyo primero) + botón/burbuja de IA
parchear($raiz, 'usuario/ticket_nuevo.php', 'AS propio', function ($s) {
    $vie = "    SELECT i.id_inventario, i.codigo_equipo, i.tipo_equipo, i.marca, i.modelo\n    FROM inventario_tecnologico i\n    INNER JOIN usuarios u ON i.id_departamento = u.id_departamento\n    WHERE u.usuario_login = ? AND i.estado = 'activo'\n";
    $nue = "    SELECT i.id_inventario, i.codigo_equipo, i.tipo_equipo, i.marca, i.modelo, (i.id_departamento = u.id_departamento) AS propio\n    FROM inventario_tecnologico i\n    JOIN usuarios u ON u.usuario_login = ?\n    WHERE i.estado = 'activo'\n    ORDER BY propio DESC, i.codigo_equipo\n";
    $s = str_replace("\r\n", "\n", $s);
    if (strpos($s, $vie) === false) return null;
    $s = str_replace($vie, $nue, $s);
    $s = str_replace("<?= htmlspecialchars(\$eq['modelo']) ?>\n                            </option>",
        "<?= htmlspecialchars(\$eq['modelo']) ?>\n                                <?= empty(\$eq['propio']) ? ' · (otro departamento)' : '' ?>\n                            </option>", $s);
    return $s;
}, 'en la consulta de equipos cambia el INNER JOIN por el JOIN de la GUIA (apartado 8).', $manual, $hecho);
parchear($raiz, 'usuario/ticket_nuevo.php', 'ia_widget.js', function ($s) {
    $tag = "<script src=\"<?= URL_BASE ?>assets/js/ia_widget.js\" data-rol=\"<?= (int)\$_SESSION['id_rol'] ?>\" data-url=\"<?= URL_BASE ?>procesos/ia_asistente.php\" defer></script>\n";
    return preg_replace('~</body>~i', $tag . '</body>', $s, 1);
}, 'antes de </body> pega la línea <script ... ia_widget.js ...> del módulo 08.', $manual, $hecho);

echo "\nCambios aplicados: $hecho\n";
if ($manual) { echo "Pendiente manual:\n"; foreach ($manual as $m) echo " - $m\n"; }

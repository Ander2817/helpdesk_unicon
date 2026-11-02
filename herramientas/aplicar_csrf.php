<?php
// herramientas/aplicar_csrf.php
// Uso (una sola vez, desde la carpeta del proyecto):   C:\xampp\php\php.exe herramientas\aplicar_csrf.php
//   Opciones:  --simular  (solo muestra qué haría)      --revertir  (quita las líneas añadidas)
// Añade UNA línea a cada proceso que recibe datos por POST:  csrf_verificar();
// Crea una copia .bak_csrf de cada archivo que modifica. Se puede ejecutar varias veces sin duplicar nada.
if (PHP_SAPI !== 'cli') { exit("Ejecutar solo desde la línea de comandos.\n"); }

$raiz     = realpath(__DIR__ . '/..');
$simular  = in_array('--simular', $argv, true);
$revertir = in_array('--revertir', $argv, true);
$marca    = '/* csrf */';

// Carpetas donde viven los procesos y archivos que NO se tocan (pantallas de login/registro/recuperación, o con su propio token)
$carpetas = ['procesos', 'admin/procesos', 'tecnico/procesos', 'usuario/procesos'];
$excluir  = ['procesos/solicitar_reset.php', 'procesos/actualizar_clave.php', 'procesos/notificaciones.php',
             'procesos/buscar.php', 'procesos/kb_buscar.php', 'admin/procesos/respaldo/accion.php',
             'admin/procesos/bitacora_data.php', 'admin/procesos/bitacora_csv.php', 'admin/procesos/bitacora_depurar.php'];

$it = [];
foreach ($carpetas as $c) {
    $dir = $raiz . '/' . $c;
    if (!is_dir($dir)) continue;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) {
        if ($f->getExtension() === 'php') $it[] = str_replace('\\', '/', $f->getPathname());
    }
}
sort($it);
$cambios = 0;
foreach ($it as $ruta) {
    $rel = ltrim(substr($ruta, strlen(str_replace('\\', '/', $raiz))), '/');
    if (in_array($rel, $excluir, true)) continue;
    $s = file_get_contents($ruta);

    if ($revertir) {
        $n = preg_replace('/^[ \t]*require_once [^\n]*includes\/csrf\.php\'; csrf_verificar\(\); ' . preg_quote($marca, '/') . "\r?\n/m", '', $s, -1, $q);
        if ($q) { echo "REVERTIDO  $rel\n"; $cambios++; if (!$simular) file_put_contents($ruta, $n); }
        continue;
    }
    if (strpos($s, 'csrf_verificar') !== false) { echo "ya tiene   $rel\n"; continue; }
    if (!preg_match('/\$_POST|\$_FILES/', $s))     continue;            // no recibe datos
    if (!preg_match('/session_start\(\s*\)\s*;/', $s)) { echo "SIN session_start (revisar a mano): $rel\n"; continue; }

    // ruta relativa desde ese archivo hasta includes/csrf.php
    $prof = substr_count(dirname($rel), '/') + (dirname($rel) === '.' ? 0 : 1);
    $linea = "require_once __DIR__ . '/" . str_repeat('../', $prof) . "includes/csrf.php'; csrf_verificar(); $marca";
    $n = preg_replace('/(session_start\(\s*\)\s*;[^\n]*\r?\n)/', '$1' . $linea . "\n", $s, 1);
    echo ($simular ? 'se añadiría ' : 'OK         ') . $rel . "\n";
    $cambios++;
    if (!$simular) { copy($ruta, $ruta . '.bak_csrf'); file_put_contents($ruta, $n); }
}

// ---------- FASE 2: pantallas SUELTAS (con su propio <head>, sin header2.php) que envían datos por AJAX/formulario ----------
// Ej.: Usuarios y Departamentos. Sin esto el servidor rechazaría sus envíos. Se les añade el script anti-CSRF antes de </head>.
$marca_js = '/* csrf-js */';
$paginas = [];
foreach (['admin', 'tecnico', 'usuario'] as $c) {
    $dir = $raiz . '/' . $c;
    if (!is_dir($dir)) continue;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) {
        if ($f->getExtension() === 'php') $paginas[] = str_replace('\\', '/', $f->getPathname());
    }
}
sort($paginas);
foreach ($paginas as $ruta) {
    $rel = ltrim(substr($ruta, strlen(str_replace('\\', '/', $raiz))), '/');
    $s = file_get_contents($ruta);

    if ($revertir) {
        $n = preg_replace('/^[ \t]*<\?php require_once [^\n]*includes\/csrf\.php\'; echo csrf_script\(\); ' . preg_quote($marca_js, '/') . ' \?>' . "\r?\n/m", '', $s, -1, $q);
        if ($q) { echo "REVERTIDO  $rel (script)\n"; $cambios++; if (!$simular) file_put_contents($ruta, $n); }
        continue;
    }
    if (strpos($s, 'csrf_script') !== false || strpos($s, 'header2.php') !== false) continue;   // ya lo tiene o lo recibe del header
    if (stripos($s, '</head>') === false) continue;                                              // no es una página completa
    if (!preg_match('/\$\.ajax|\$\.post|fetch\(|XMLHttpRequest|<form[^>]*method\s*=\s*["\']post/i', $s)) continue;

    $prof = substr_count(dirname($rel), '/') + 1;
    $linea = "<?php require_once __DIR__ . '/" . str_repeat('../', $prof) . "includes/csrf.php'; echo csrf_script(); $marca_js ?>";
    $n = preg_replace('/([ \t]*)<\/head>/i', $linea . "\n$1</head>", $s, 1);
    echo ($simular ? 'se añadiría (script) ' : 'OK (script) ') . $rel . "\n";
    $cambios++;
    if (!$simular) { copy($ruta, $ruta . '.bak_csrf'); file_put_contents($ruta, $n); }
}

echo "\n$cambios archivo(s) " . ($revertir ? 'revertido(s)' : 'modificado(s)') . ($simular ? ' (simulación, no se escribió nada)' : '') . ".\n";

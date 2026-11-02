<?php
// includes/respaldo_lib.php
// Respaldo y restauración de la BD en PHP puro (sin exec/mysqldump: funciona igual en XAMPP y en hostings compartidos).

const RESPALDO_MAX_UPLOAD = 200 * 1024 * 1024; // 200 MB (debe coincidir con backups/.htaccess de admin/procesos/respaldo)

/** Tablas que una restauración NO sobrescribe (se conservan tal como están), salvo que el admin lo pida. */
const RESPALDO_TABLAS_PROTEGIDAS = ['bitacora'];

/** Carpeta donde se guardan los respaldos (protegida con .htaccess). */
function respaldo_dir(): string {
    $dir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'backups';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!is_file($dir . '/.htaccess')) file_put_contents($dir . '/.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
    if (!is_file($dir . '/index.html')) file_put_contents($dir . '/index.html', '');
    return $dir;
}

/** Valida un nombre de archivo de respaldo y devuelve su ruta completa, o null. Evita path traversal. */
function respaldo_ruta(string $nombre): ?string {
    if (!preg_match('/^[A-Za-z0-9_\-\.]+\.(sql|sql\.gz)$/', $nombre) || str_contains($nombre, '..')) return null;
    $ruta = respaldo_dir() . DIRECTORY_SEPARATOR . $nombre;
    return is_file($ruta) ? $ruta : null;
}

/** Lista los respaldos guardados (.sql y .sql.gz), del más nuevo al más viejo. */
function respaldo_listar(): array {
    $out = [];
    $dir = respaldo_dir();
    foreach (scandir($dir) ?: [] as $nombre) {
        if (!preg_match('/\.(sql|sql\.gz)$/', $nombre) || !is_file("$dir/$nombre")) continue;
        $out[] = ['nombre' => $nombre, 'tamano' => filesize("$dir/$nombre"), 'fecha' => filemtime("$dir/$nombre")];
    }
    usort($out, fn($a, $b) => $b['fecha'] <=> $a['fecha']);
    return $out;
}

/** Tamaño aproximado (datos + índices) de la BD actual, en bytes. */
function respaldo_tamano_bd(mysqli $c): int {
    return (int)$c->query("SELECT COALESCE(SUM(data_length + index_length), 0) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetch_row()[0];
}

/**
 * Empaqueta uploads/adjuntos en un .zip (dentro queda la carpeta "adjuntos/").
 * Para restaurar: descomprime el .zip dentro de la carpeta uploads/ del proyecto.
 * @return array ['archivos' => int]
 */
function respaldo_zip_adjuntos(string $ruta): array {
    if (!class_exists('ZipArchive')) throw new RuntimeException('La extensión zip de PHP no está activa (en XAMPP: php.ini → quita el ; de extension=zip y reinicia Apache).');
    $base = realpath(__DIR__ . '/../uploads/adjuntos');
    if (!$base || !is_dir($base)) throw new RuntimeException('No existe la carpeta uploads/adjuntos.');
    $zip = new ZipArchive();
    if ($zip->open($ruta, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('No se pudo crear el archivo .zip temporal.');
    $n = 0;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (!$f->isFile()) continue;
        $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($base) + 1));
        $zip->addFile($f->getPathname(), 'adjuntos/' . $rel);
        $n++;
    }
    if ($n === 0) { $zip->close(); throw new RuntimeException('No hay archivos adjuntos para respaldar.'); }
    $zip->close();
    return ['archivos' => $n];
}

function respaldo_formato_bytes(int $b): string {
    foreach (['B', 'KB', 'MB', 'GB'] as $i => $u) {
        if ($b < 1024 || $i === 3) return ($i ? number_format($b, 1) : $b) . ' ' . $u;
        $b /= 1024;
    }
}

// ====================================================================
//  EXPORTAR
// ====================================================================

/**
 * Genera un volcado completo (estructura + datos) en $ruta. Si $ruta termina en .gz se comprime.
 * @return array ['tablas' => int, 'filas' => int]
 */
function respaldo_generar(mysqli $c, string $ruta): array {
    $gz  = str_ends_with($ruta, '.gz');
    $fh  = $gz ? gzopen($ruta, 'wb6') : fopen($ruta, 'wb');
    if (!$fh) throw new RuntimeException('No se pudo crear el archivo de respaldo (permisos de la carpeta backups/).');
    $w = fn(string $s) => $gz ? gzwrite($fh, $s) : fwrite($fh, $s);

    $db = $c->query("SELECT DATABASE()")->fetch_row()[0];
    $w("-- Respaldo HelpDesk Unicon\n-- Base de datos: `$db`\n-- Generado: " . date('Y-m-d H:i:s') . "\n\n");
    $w("SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\nSET time_zone = \"+00:00\";\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\nSET UNIQUE_CHECKS = 0;\n\n");

    $tablas = [];
    $r = $c->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    while ($f = $r->fetch_row()) $tablas[] = $f[0];
    $r->free();

    $total_filas = 0;
    foreach ($tablas as $t) {
        $q = '`' . str_replace('`', '``', $t) . '`';
        $create = $c->query("SHOW CREATE TABLE $q")->fetch_row()[1];
        $w("-- --------------------------------------------------------\n-- Tabla $q\n\nDROP TABLE IF EXISTS $q;\n$create;\n\n");

        // Lectura sin buffer: no carga toda la tabla en memoria
        $res    = $c->query("SELECT * FROM $q", MYSQLI_USE_RESULT);
        $campos = $res->fetch_fields();
        $cols   = implode(', ', array_map(fn($f) => '`' . $f->name . '`', $campos));
        $lote   = [];
        while ($fila = $res->fetch_row()) {
            $vals = [];
            foreach ($fila as $i => $v) {
                $f = $campos[$i];
                if ($v === null) {
                    $vals[] = 'NULL';
                } elseif (in_array($f->type, [0, 1, 2, 3, 4, 5, 8, 9, 13, 246], true)) { // numéricos (incl. DECIMAL)
                    $vals[] = $v;
                } elseif (($f->flags & 128) || $f->type === 16) {                  // BINARY / BLOB binario / BIT
                    $vals[] = $v === '' ? "''" : '0x' . bin2hex($v);
                } else {
                    $vals[] = "'" . $c->real_escape_string($v) . "'";
                }
            }
            $lote[] = '(' . implode(', ', $vals) . ')';
            $total_filas++;
            if (count($lote) >= 200) {
                $w("INSERT INTO $q ($cols) VALUES\n" . implode(",\n", $lote) . ";\n");
                $lote = [];
            }
        }
        $res->free();
        if ($lote) $w("INSERT INTO $q ($cols) VALUES\n" . implode(",\n", $lote) . ";\n");
        $w("\n");
    }
    $w("SET FOREIGN_KEY_CHECKS = 1;\nSET UNIQUE_CHECKS = 1;\n-- Fin del respaldo\n");
    $gz ? gzclose($fh) : fclose($fh);

    return ['tablas' => count($tablas), 'filas' => $total_filas];
}

// ====================================================================
//  LECTOR DE SENTENCIAS SQL (respeta comillas, comentarios y ';' dentro de textos)
// ====================================================================

/**
 * Recorre un .sql / .sql.gz y devuelve cada sentencia completa, una a una (generador: no carga el archivo entero).
 * Los comentarios (incluidos los /*!40101 ... *\/ de phpMyAdmin) se descartan.
 */
function respaldo_sentencias(string $ruta): Generator {
    $gz = str_ends_with($ruta, '.gz');
    $fh = $gz ? gzopen($ruta, 'rb') : fopen($ruta, 'rb');
    if (!$fh) throw new RuntimeException('No se pudo abrir el archivo.');

    $sent = '';
    $q = null;          // comilla abierta: ' " `
    $bloque = false;    // dentro de /* ... */
    $linea_n = 0;

    while (($linea = $gz ? gzgets($fh) : fgets($fh)) !== false) {
        $linea_n++;
        $n = strlen($linea);
        for ($i = 0; $i < $n; $i++) {
            $ch = $linea[$i];
            $sig = $i + 1 < $n ? $linea[$i + 1] : '';

            if ($bloque) {
                if ($ch === '*' && $sig === '/') { $bloque = false; $i++; }
                continue;
            }
            if ($q !== null) {
                $sent .= $ch;
                if ($ch === '\\' && $q !== '`') {            // escape: copiar el siguiente carácter tal cual
                    if ($i + 1 < $n) { $sent .= $sig; $i++; }
                } elseif ($ch === $q) {
                    if ($sig === $q) { $sent .= $sig; $i++; } // comilla duplicada ('')
                    else $q = null;
                }
                continue;
            }
            // Fuera de texto
            if ($ch === '/' && $sig === '*') { $bloque = true; $i++; continue; }
            if ($ch === '#') break;                                                   // comentario hasta fin de línea
            if ($ch === '-' && $sig === '-' && ($i + 2 >= $n || ctype_space($linea[$i + 2]))) break;
            if ($ch === "'" || $ch === '"' || $ch === '`') { $q = $ch; $sent .= $ch; continue; }
            if ($ch === ';') {
                $s = trim($sent);
                $sent = '';
                if ($s !== '') yield [$linea_n, $s];
                continue;
            }
            $sent .= $ch;
        }
    }
    $gz ? gzclose($fh) : fclose($fh);
    $s = trim($sent);
    if ($q !== null || $bloque) throw new RuntimeException('El archivo está incompleto o dañado (texto sin cerrar al final).');
    if ($s !== '') yield [$linea_n, $s];
}

/**
 * Lista blanca: un respaldo solo puede contener sentencias de estructura/datos de tablas de ESTA base de datos.
 * Rechaza GRANT, CREATE USER, DROP DATABASE, USE otra_bd, LOAD DATA, tablas con prefijo `otra_bd`.`tabla`, etc.
 * @return string|null mensaje de error, o null si la sentencia es aceptable
 */
function respaldo_sentencia_permitida(string $s): ?string {
    if (preg_match('/^(START\s+TRANSACTION|COMMIT|UNLOCK\s+TABLES)$/i', $s)) return null;
    if (preg_match('/^SET\s+(@{0,2}(SESSION\.)?(SQL_MODE|TIME_ZONE|FOREIGN_KEY_CHECKS|UNIQUE_CHECKS|AUTOCOMMIT|SQL_NOTES)\s*=|NAMES\b|CHARACTER_SET_\w+\s*=|@OLD_\w+\s*=\s*@@\w+$)/i', $s)) return null;
    if (preg_match('/^(DROP\s+TABLE(\s+IF\s+EXISTS)?|CREATE\s+TABLE(\s+IF\s+NOT\s+EXISTS)?|INSERT(\s+IGNORE)?\s+INTO|REPLACE\s+INTO|ALTER\s+TABLE|LOCK\s+TABLES)\s+`[^`]+`(?!\s*\.)/i', $s)) return null;
    return 'Sentencia no permitida: ' . mb_substr(preg_replace('/\s+/', ' ', $s), 0, 80);
}

/** Recorre todo el archivo SIN ejecutar nada. Lanza excepción si algo no es válido. @return int cantidad de sentencias */
function respaldo_validar(string $ruta): int {
    $n = 0; $tiene_create = false;
    foreach (respaldo_sentencias($ruta) as [$linea, $s]) {
        if ($err = respaldo_sentencia_permitida($s)) throw new RuntimeException("Línea $linea: $err");
        if (stripos($s, 'CREATE TABLE') === 0) $tiene_create = true;
        $n++;
    }
    if (!$tiene_create) throw new RuntimeException('El archivo no contiene ninguna tabla (CREATE TABLE). ¿Es un respaldo de la base de datos?');
    return $n;
}

// ====================================================================
//  RESTAURAR
// ====================================================================

/** Nombre (en minúsculas) de la tabla sobre la que actúa una sentencia, o null. */
function respaldo_tabla_destino(string $s): ?string {
    if (preg_match('/^(?:DROP\s+TABLE(?:\s+IF\s+EXISTS)?|CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?|INSERT(?:\s+IGNORE)?\s+INTO|REPLACE\s+INTO|ALTER\s+TABLE|LOCK\s+TABLES)\s+`([^`]+)`/i', $s, $m)) {
        return strtolower($m[1]);
    }
    return null;
}

/**
 * Ejecuta el respaldo sobre la BD actual. Valida primero; ejecuta después.
 * Por defecto NO toca las tablas protegidas (la bitácora) que ya existan: así el rastro de auditoría sobrevive a cualquier restauración.
 * @return int sentencias ejecutadas
 */
function respaldo_ejecutar(mysqli $c, string $ruta, bool $incluir_bitacora = false): int {
    respaldo_validar($ruta);                                  // pasada 1: validar (no toca la BD)
    @set_time_limit(0);
    $c->query("SET FOREIGN_KEY_CHECKS = 0");
    $c->query("SET UNIQUE_CHECKS = 0");
    $c->query("SET NAMES utf8mb4");
    $c->query("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
    $n = 0;
    try {
        $r = $c->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $existentes = array_column($r->fetch_all(), 0);
        $r->free();

        // Tablas que se conservan intactas (solo si ya existen en la BD actual)
        $conservar = [];
        if (!$incluir_bitacora) {
            foreach ($existentes as $t) {
                if (in_array(strtolower($t), RESPALDO_TABLAS_PROTEGIDAS, true)) $conservar[] = strtolower($t);
            }
        }

        // Los volcados de phpMyAdmin no traen DROP TABLE: se vacía la BD para que el CREATE TABLE no choque.
        foreach ($existentes as $t) {
            if (in_array(strtolower($t), $conservar, true)) continue;
            $c->query('DROP TABLE IF EXISTS `' . str_replace('`', '``', $t) . '`');
        }

        foreach (respaldo_sentencias($ruta) as [$linea, $s]) {  // pasada 2: ejecutar
            $t = respaldo_tabla_destino($s);
            if ($t !== null && in_array($t, $conservar, true)) continue;   // tabla protegida: se salta
            try {
                $c->query($s);
            } catch (mysqli_sql_exception $e) {
                throw new RuntimeException("Error en la línea $linea: " . $e->getMessage());
            }
            $n++;
        }
    } finally {
        $c->query("SET FOREIGN_KEY_CHECKS = 1");
        $c->query("SET UNIQUE_CHECKS = 1");
    }
    return $n;
}

/**
 * Restaura con red de seguridad: antes de tocar nada crea un respaldo "pre_restauracion_...".
 * Si la restauración falla a la mitad, intenta volver automáticamente a ese respaldo.
 * @return array ['sentencias' => int, 'respaldo_previo' => string]
 */
function respaldo_restaurar(mysqli $c, string $ruta, bool $incluir_bitacora = false): array {
    respaldo_validar($ruta);                                  // si el archivo es inválido, ni siquiera se hace el respaldo previo
    $previo = 'pre_restauracion_' . date('Ymd_His') . (function_exists('gzopen') ? '.sql.gz' : '.sql');
    respaldo_generar($c, respaldo_dir() . DIRECTORY_SEPARATOR . $previo);
    try {
        $n = respaldo_ejecutar($c, $ruta, $incluir_bitacora);
    } catch (Throwable $e) {
        $revertido = false;
        try { respaldo_ejecutar($c, respaldo_dir() . DIRECTORY_SEPARATOR . $previo, $incluir_bitacora); $revertido = true; } catch (Throwable $e2) {}
        throw new RuntimeException($e->getMessage() . ($revertido
            ? ' — La base de datos fue devuelta automáticamente a su estado anterior.'
            : ' — ATENCIÓN: además falló el retorno automático; restaura manualmente el archivo ' . $previo . '.'));
    }
    return ['sentencias' => $n, 'respaldo_previo' => $previo];
}

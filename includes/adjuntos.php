<?php
/**
 * includes/adjuntos.php
 * Todo lo relacionado con archivos adjuntos de tickets: límites, validación, guardado y utilidades.
 *
 * SEGURIDAD (por qué está hecho así):
 *  - Los archivos se guardan en uploads/adjuntos/ con un nombre ALEATORIO y extensión ".dat".
 *    El nombre original solo vive en la base de datos. Así, aunque alguien lograra pedir el archivo
 *    directamente por URL, el servidor nunca lo ejecutaría como PHP.
 *  - La carpeta lleva un .htaccess que prohíbe el acceso directo. Los archivos solo salen por
 *    procesos/descargar_adjunto.php, que verifica que quien lo pide pueda ver ese ticket.
 *  - Se bloquean ejecutables y scripts (.exe, .php, .js, .bat ...). El resto de tipos se acepta.
 */

// ===== LÍMITES (cámbialos aquí si necesitas otros) =====
const ADJUNTO_MAX_BYTES        = 5 * 1024 * 1024;   // 5 MB por archivo
const ADJUNTOS_MAX_POR_TICKET  = 5;                 // hasta 5 archivos por ticket

// Carpeta de almacenamiento: <proyecto>/uploads/adjuntos
const ADJUNTOS_SUBCARPETA = '/uploads/adjuntos';

// Extensiones que NUNCA se aceptan: programas y scripts que podrían ejecutarse o infectar
const ADJUNTO_EXT_BLOQUEADAS = [
    'php','php3','php4','php5','php7','php8','phtml','phar','pht','inc',
    'exe','msi','dll','sys','com','scr','pif','cpl','bat','cmd','lnk','reg','inf',
    'js','jse','vbs','vbe','wsf','wsh','ps1','psm1','sh','bash','jar','hta','htaccess','htpasswd',
    'asp','aspx','jsp','cgi','pl','py',
];

const ADJUNTO_EXT_IMAGEN = ['jpg','jpeg','png','gif','webp','bmp'];

function adjuntos_directorio(): string {
    return dirname(__DIR__) . ADJUNTOS_SUBCARPETA;
}

/** Convierte "40M", "512K", "1G" (formato de php.ini) a bytes. */
function adjuntos_ini_a_bytes(string $valor): int {
    $valor = trim($valor);
    if ($valor === '') return 0;
    $n = (int)$valor;
    switch (strtolower(substr($valor, -1))) {
        case 'g': $n *= 1024;   // no break
        case 'm': $n *= 1024;   // no break
        case 'k': $n *= 1024;
    }
    return $n;
}

/**
 * Tamaño máximo REAL por archivo: el menor entre nuestro límite y lo que permite el servidor
 * (upload_max_filesize). Así la pantalla nunca promete más de lo que PHP aceptará.
 */
function adjuntos_limite_por_archivo(): int {
    $servidor = adjuntos_ini_a_bytes((string)ini_get('upload_max_filesize'));
    return ($servidor > 0) ? min(ADJUNTO_MAX_BYTES, $servidor) : ADJUNTO_MAX_BYTES;
}

function formato_bytes(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024) . ' KB';
    return $bytes . ' B';
}

function adjunto_extension(string $nombre): string {
    return strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
}

function adjunto_es_imagen(string $ext): bool {
    return in_array(strtolower($ext), ADJUNTO_EXT_IMAGEN, true);
}

/** Tipo de contenido que se envía al descargar (lo decidimos nosotros, no el navegador). */
function adjunto_mime(string $ext): string {
    static $mapa = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
        'webp' => 'image/webp', 'bmp' => 'image/bmp',
        'pdf' => 'application/pdf', 'txt' => 'text/plain', 'log' => 'text/plain', 'csv' => 'text/csv',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'zip' => 'application/zip', 'rar' => 'application/vnd.rar', '7z' => 'application/x-7z-compressed',
    ];
    return $mapa[strtolower($ext)] ?? 'application/octet-stream';
}

/** Ícono de Font Awesome según el tipo de archivo. */
function adjunto_icono(string $ext): string {
    $ext = strtolower($ext);
    if (adjunto_es_imagen($ext))                       return 'fa-file-image';
    if ($ext === 'pdf')                                return 'fa-file-pdf';
    if (in_array($ext, ['doc','docx','rtf','odt'], true))  return 'fa-file-word';
    if (in_array($ext, ['xls','xlsx','csv','ods'], true))  return 'fa-file-excel';
    if (in_array($ext, ['ppt','pptx','odp'], true))        return 'fa-file-powerpoint';
    if (in_array($ext, ['zip','rar','7z','gz','tar'], true)) return 'fa-file-archive';
    if (in_array($ext, ['txt','log','json','xml','ini','cfg','conf'], true)) return 'fa-file-alt';
    return 'fa-file';
}

/** Lista de extensiones bloqueadas, lista para mostrar en pantalla. */
function adjuntos_extensiones_bloqueadas_texto(): string {
    return '.exe, .bat, .js, .php, .msi, .ps1, .vbs …';
}

/**
 * Convierte $_FILES['adjuntos'] (formato anidado de PHP) en una lista simple de archivos,
 * descartando las casillas vacías (cuando el usuario no eligió nada).
 */
function adjuntos_normalizar(?array $campo): array {
    if (!$campo || !isset($campo['name'])) return [];
    if (!is_array($campo['name'])) {
        $campo = array_map(fn($v) => [$v], $campo);
    }
    $lista = [];
    foreach ($campo['name'] as $i => $nombre) {
        if (($campo['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        $lista[] = [
            'name'     => (string)$nombre,
            'tmp_name' => (string)($campo['tmp_name'][$i] ?? ''),
            'error'    => (int)($campo['error'][$i] ?? UPLOAD_ERR_NO_FILE),
            'size'     => (int)($campo['size'][$i] ?? 0),
        ];
    }
    return $lista;
}

/** Devuelve la lista de problemas (vacía = todo bien). Se valida TODO antes de crear el ticket. */
function adjuntos_validar(array $lista): array {
    $errores = [];
    $limite = adjuntos_limite_por_archivo();

    if (count($lista) > ADJUNTOS_MAX_POR_TICKET) {
        $errores[] = 'Máximo ' . ADJUNTOS_MAX_POR_TICKET . ' archivos por ticket (elegiste ' . count($lista) . ').';
    }

    foreach ($lista as $f) {
        $nombre = $f['name'];
        $ext = adjunto_extension($nombre);

        if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
            $errores[] = "«{$nombre}» supera el tamaño máximo permitido (" . formato_bytes($limite) . ").";
            continue;
        }
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errores[] = "«{$nombre}» no se pudo subir correctamente. Inténtalo de nuevo.";
            continue;
        }
        if ($f['size'] <= 0) {
            $errores[] = "«{$nombre}» está vacío.";
            continue;
        }
        if ($f['size'] > $limite) {
            $errores[] = "«{$nombre}» pesa " . formato_bytes($f['size']) . " y el máximo es " . formato_bytes($limite) . ".";
            continue;
        }
        // Se revisan TODAS las partes del nombre: "reporte.php.jpg" también se rechaza
        $partes = array_slice(explode('.', strtolower($nombre)), 1);
        $peligrosa = array_values(array_intersect($partes, ADJUNTO_EXT_BLOQUEADAS));
        if ($peligrosa) {
            $errores[] = "«{$nombre}»: los archivos .{$peligrosa[0]} no se permiten por seguridad.";
        }
    }
    return $errores;
}

/** Crea la carpeta de almacenamiento y su protección si todavía no existen. */
function adjuntos_preparar_carpeta(string $dir): bool {
    $base = adjuntos_directorio();
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) return false;

    $htaccess = $base . '/.htaccess';
    if (!file_exists($htaccess)) {
        @file_put_contents($htaccess,
            "# Los adjuntos solo se descargan a través de procesos/descargar_adjunto.php\n" .
            "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n" .
            "<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n" .
            "Options -Indexes\n");
    }
    $index = $base . '/index.html';
    if (!file_exists($index)) @file_put_contents($index, '');
    return is_writable($dir);
}

/**
 * Guarda los archivos ya validados y registra cada uno en la tabla adjuntos.
 * Debe llamarse dentro de una transacción. Cada ruta escrita en disco se anota en $escritos
 * (por referencia) en el mismo instante, de modo que si algo falla a mitad de camino quien llama
 * puede borrar lo ya escrito al hacer rollback.
 *
 * @throws RuntimeException si no se puede escribir un archivo
 */
function adjuntos_guardar(mysqli $conexion, int $id_ticket, array $lista, array &$escritos = []): array {
    if (!$lista) return [];

    $subdir = date('Y');
    $dir = adjuntos_directorio() . '/' . $subdir;
    if (!adjuntos_preparar_carpeta($dir)) {
        throw new RuntimeException('No se pudo preparar la carpeta de archivos adjuntos.');
    }

    $stmt = $conexion->prepare("INSERT INTO adjuntos (id_ticket, nombre_archivo, ruta_archivo, tipo_archivo) VALUES (?, ?, ?, ?)");

    foreach ($lista as $f) {
        // Nombre original limpio (sin rutas, sin caracteres de control), tope de 255 por la columna
        $original = preg_replace('/[\x00-\x1F\x7F\\\\\/]+/', '', basename(str_replace('\\', '/', $f['name'])));
        if (function_exists('mb_substr')) $original = mb_substr($original, 0, 200);
        else $original = substr($original, 0, 200);
        if ($original === '') $original = 'archivo';

        $ext = substr(adjunto_extension($original), 0, 30);
        $guardado = bin2hex(random_bytes(16)) . '.dat';
        $relativa = $subdir . '/' . $guardado;
        $destino = $dir . '/' . $guardado;

        if (!move_uploaded_file($f['tmp_name'], $destino)) {
            throw new RuntimeException("No se pudo guardar «{$original}».");
        }
        @chmod($destino, 0664);
        $escritos[] = $destino;

        $stmt->bind_param("isss", $id_ticket, $original, $relativa, $ext);
        $stmt->execute();
    }
    $stmt->close();
    return $escritos;
}

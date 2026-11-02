<?php
// includes/adjuntos_comentario.php — archivos adjuntos dentro de un comentario (usa las mismas reglas de seguridad que los del ticket)
require_once __DIR__ . '/adjuntos.php';

const ADJUNTOS_MAX_POR_COMENTARIO = 3;

function adjuntos_comentario_validar(array $lista): array {
    if (count($lista) > ADJUNTOS_MAX_POR_COMENTARIO) {
        return ['Máximo ' . ADJUNTOS_MAX_POR_COMENTARIO . ' archivos por comentario (elegiste ' . count($lista) . ').'];
    }
    return adjuntos_validar($lista);
}

/** Guarda los archivos y los liga al comentario. Lanza RuntimeException si no se puede escribir un archivo. */
function adjuntos_guardar_comentario(mysqli $c, int $id_ticket, int $id_comentario, array $lista, array &$escritos = []): void {
    if (!$lista) return;
    $subdir = date('Y');
    $dir = adjuntos_directorio() . '/' . $subdir;
    if (!adjuntos_preparar_carpeta($dir)) throw new RuntimeException('No se pudo preparar la carpeta de archivos adjuntos.');

    $stmt = $c->prepare("INSERT INTO adjuntos (id_ticket, id_comentario, nombre_archivo, ruta_archivo, tipo_archivo) VALUES (?, ?, ?, ?, ?)");
    foreach ($lista as $f) {
        $original = preg_replace('/[\x00-\x1F\x7F\\\\\/]+/', '', basename(str_replace('\\', '/', $f['name'])));
        $original = mb_substr($original, 0, 200);
        if ($original === '') $original = 'archivo';
        $ext = substr(adjunto_extension($original), 0, 30);
        $guardado = bin2hex(random_bytes(16)) . '.dat';
        $relativa = $subdir . '/' . $guardado;
        $destino = $dir . '/' . $guardado;
        if (!move_uploaded_file($f['tmp_name'], $destino)) throw new RuntimeException("No se pudo guardar «{$original}».");
        @chmod($destino, 0664);
        $escritos[] = $destino;
        $stmt->bind_param("iisss", $id_ticket, $id_comentario, $original, $relativa, $ext);
        $stmt->execute();
    }
    $stmt->close();
}

<?php
// admin/reporte_csv.php?desde=AAAA-MM-DD&hasta=AAAA-MM-DD  -> detalle ticket por ticket para Excel
session_start();
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) {
    http_response_code(403);
    exit('No autorizado.');
}
require_once '../includes/constants.php';
include '../config/conexion.php';
require_once '../includes/reporte_datos.php';

$rango = reporte_rango($_GET['desde'] ?? null, $_GET['hasta'] ?? null);
$filas = reporte_detalle($conexion, $rango['desde'], $rango['hasta']);

/**
 * Anti "inyección de fórmulas": si un texto empieza con = + - @ Excel lo ejecuta como fórmula.
 * Como los asuntos los escriben los usuarios, se les antepone un apóstrofo para que se lean como texto.
 */
function csv_seguro($valor): string {
    $v = (string)$valor;
    return ($v !== '' && strpbrk($v[0], "=+-@\t\r") !== false) ? "'" . $v : $v;
}
function csv_fecha(?string $f): string { return $f ? date('d/m/Y H:i', strtotime($f)) : ''; }

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="tickets_' . str_replace('-', '', $rango['desde']) . '_' . str_replace('-', '', $rango['hasta']) . '.csv"');
header('Cache-Control: private, max-age=0, must-revalidate');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");   // BOM UTF-8: Excel reconoce así las tildes y la ñ

// Separador ";" y coma decimal: es lo que Excel en español espera al abrir el archivo
$sep = ';'; $enc = '"'; $esc = '\\'; $eol = "\r\n";
fputcsv($out, ['Código', 'Asunto', 'Solicitante', 'Departamento', 'Categoría', 'Prioridad', 'Estado', 'Técnico',
               'Creado', 'Cerrado', 'Tiempo de resolución (h)', 'Objetivo SLA (h)', 'Cumple SLA', 'Adjuntos'], $sep, $enc, $esc, $eol);

foreach ($filas as $f) {
    $cerrado = in_array((int)$f['id_estado'], [TICKET_RESUELTO, TICKET_CERRADO], true) && $f['fecha_cierre'];
    $minutos = $cerrado ? (int)round((strtotime($f['fecha_cierre']) - strtotime($f['fecha_creacion'])) / 60) : null;
    fputcsv($out, [
        csv_seguro($f['codigo_ticket']),
        csv_seguro($f['asunto']),
        csv_seguro($f['solicitante']),
        csv_seguro($f['departamento']),
        csv_seguro($f['categoria']),
        csv_seguro($f['prioridad']),
        csv_seguro($f['estado']),
        csv_seguro($f['tecnico'] ?? 'Sin asignar'),
        csv_fecha($f['fecha_creacion']),
        csv_fecha($cerrado ? $f['fecha_cierre'] : null),
        $minutos !== null ? str_replace('.', ',', (string)round($minutos / 60, 1)) : '',
        str_replace('.', ',', (string)round($f['objetivo'] / 60, 1)),
        $minutos !== null ? ($minutos <= (int)$f['objetivo'] ? 'Sí' : 'No') : '',
        (int)$f['adjuntos'],
    ], $sep, $enc, $esc, $eol);
}
fclose($out);

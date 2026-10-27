<?php
// admin/reporte_pdf.php?desde=AAAA-MM-DD&hasta=AAAA-MM-DD  -> descarga el reporte en PDF
session_start();
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) {
    http_response_code(403);
    exit('No autorizado.');
}
require_once '../includes/constants.php';
include '../config/conexion.php';
require_once '../includes/reporte_datos.php';
require_once '../includes/ReportePDF.php';

$rango = reporte_rango($_GET['desde'] ?? null, $_GET['hasta'] ?? null);
$datos = reporte_obtener($conexion, $rango['desde'], $rango['hasta']);

// Nombre completo de quien genera el reporte (aparece en el pie de página)
$st = $conexion->prepare("SELECT CONCAT(nombres, ' ', apellidos) AS n FROM usuarios WHERE usuario_login = ?");
$st->bind_param("s", $_SESSION['usser']);
$st->execute();
$generado_por = $st->get_result()->fetch_assoc()['n'] ?? $_SESSION['usser'];
$st->close();

$pdf = new ReportePDF($generado_por);
$pdf->portada($datos, $rango);
$pdf->volumenMensual($datos['por_mes']);
$pdf->tecnicos($datos['tecnicos']);
$pdf->sla($datos['sla']);
$pdf->barras('Tickets por categoría', 'Qué tipo de problema genera más tickets.', $datos['categorias'], ReportePDF::NARANJA);
$pdf->barras('Tickets por departamento', 'Qué áreas generan más tickets.', $datos['departamentos'], ReportePDF::AZUL);
$pdf->notas();

// FPDF se niega a enviar el PDF si ya salió cualquier carácter antes: limpiamos por si acaso
while (ob_get_level() > 0) { ob_end_clean(); }
$pdf->Output('D', 'reporte_tickets_' . str_replace('-', '', $rango['desde']) . '_' . str_replace('-', '', $rango['hasta']) . '.pdf');

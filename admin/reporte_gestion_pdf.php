<?php
// admin/reporte_gestion_pdf.php?desde=AAAA-MM-DD&hasta=AAAA-MM-DD — descarga el reporte de gestión completo en PDF.
session_start();
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 3) { http_response_code(403); exit('No autorizado.'); }
require_once '../includes/constants.php';
include '../config/conexion.php';
require_once '../includes/reporte_datos.php';
require_once '../includes/reporte_gestion.php';
require_once '../includes/ReporteGestionPDF.php';
$rango = reporte_rango($_GET['desde'] ?? null, $_GET['hasta'] ?? null);
$datos = rg_obtener($conexion, $rango['desde'], $rango['hasta']);
$st = $conexion->prepare("SELECT CONCAT(nombres, ' ', apellidos) AS n FROM usuarios WHERE usuario_login = ?");
$st->bind_param("s", $_SESSION['usser']); $st->execute();
$por = $st->get_result()->fetch_assoc()['n'] ?? $_SESSION['usser']; $st->close();
$pdf = new ReporteGestionPDF($por);
$pdf->contenido($datos);
while (ob_get_level() > 0) { ob_end_clean(); }
$pdf->Output('D', 'reporte_gestion_' . str_replace('-', '', $rango['desde']) . '_' . str_replace('-', '', $rango['hasta']) . '.pdf');

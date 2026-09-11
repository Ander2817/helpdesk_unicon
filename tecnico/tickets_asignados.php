<?php
session_start();
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 2) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/constants.php';
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';

$titulo_pagina  = 'Asignados a Mí';
$icono_pagina   = 'fa-user-check';
$filtro_estados = [TICKET_PROCESO, TICKET_PENDIENTE];
$mensaje_vacio  = 'No tienes tickets activos en este momento.';

include_once '../includes/lista_tickets_tecnico.php';
include_once '../includes/footer2.php';

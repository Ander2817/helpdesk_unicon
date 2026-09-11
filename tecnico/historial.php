<?php
session_start();
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 2) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/constants.php';
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';

$titulo_pagina  = 'Mi Historial';
$icono_pagina   = 'fa-history';
$filtro_estados = null; // todos los estados, historial completo
$mensaje_vacio  = 'Aún no tienes tickets en tu historial.';

include_once '../includes/lista_tickets_tecnico.php';
include_once '../includes/footer2.php';

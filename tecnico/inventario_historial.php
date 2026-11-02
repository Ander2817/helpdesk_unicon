<?php
session_start();
if (!isset($_SESSION['usser']) || !in_array((int)($_SESSION['id_rol'] ?? 0), [2], true)) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/functions.php';
include '../config/conexion.php';

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
include_once '../includes/equipo_historial.php';
include_once '../includes/footer2.php';

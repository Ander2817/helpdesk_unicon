<?php
// ver_ticket.php — IDÉNTICO para los 3 roles. Se copia a admin/, tecnico/ y usuario/
// (el rol lo valida header2.php según la carpeta, y ticket_detalle.php según cada ticket).
session_start();
if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    header("Location: ../index.php"); exit();
}
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
include_once '../includes/ticket_detalle.php';
include_once '../includes/footer2.php';

<?php
// tickets_crear.php — idéntico para admin y técnico. Se copia a admin/ y a tecnico/
session_start();
if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/constants.php';
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
include_once '../includes/ticket_form.php';
include_once '../includes/footer2.php';

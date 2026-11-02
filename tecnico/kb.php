<?php
session_start();
if (!isset($_SESSION['usser'])) { header("Location: ../index.php"); exit(); }
require_once '../includes/functions.php';
include '../config/conexion.php';

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
include_once '../includes/kb_vista.php';
include_once '../includes/footer2.php';

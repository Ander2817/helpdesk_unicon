<?php
// procesos/tomar_ticket.php
// DESACTIVADO: los técnicos ya no pueden asignarse tickets a sí mismos.
// La asignación la hace únicamente el administrador (ver procesos/asignar_ticket.php).
// Este archivo se conserva como bloqueo para que ninguna petición antigua o manual
// (marcador, botón en caché, POST directo) pueda saltarse la regla.
http_response_code(403);
header('Content-Type: text/html; charset=utf-8');
echo "<div class='alert alert-danger'><i class='fas fa-times-circle me-2'></i>Los tickets los asigna el administrador. Un técnico no puede tomar tickets por su cuenta.</div>";
exit();

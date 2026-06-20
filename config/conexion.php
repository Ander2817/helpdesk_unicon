<?php
// conexion.php
$host = "localhost";
$usuario = "root";
$password = ""; // Por defecto en XAMPP
$base_datos = "helpdesk_unicon";
// Habilitar el reporte de excepciones en MySQLi (esencial para el bloque try-catch)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
// Inicializar la conexión mediante el objeto MySQLi
$conexion = new mysqli($host, $usuario, $password, $base_datos);
// Establecer el juego de caracteres a UTF-8 para evitar problemas con tildes o eñes
$conexion->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
// En entornos de producción, se recomienda registrar el error en un log y no mostrar detalles internos
die("Error crítico de conexión a la Base de Datos: " . $e->getMessage());
}
?>

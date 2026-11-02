<?php
// conexion.php
// Credenciales: por defecto las de XAMPP; se pueden cambiar en config/local.php sin tocar este archivo
if (is_file(__DIR__ . '/local.php')) require_once __DIR__ . '/local.php';
$host = defined('DB_HOST') ? DB_HOST : "localhost";
$usuario = defined('DB_USER') ? DB_USER : "root";
$password = defined('DB_PASS') ? DB_PASS : ""; // Por defecto en XAMPP
$base_datos = defined('DB_NAME') ? DB_NAME : "helpdesk_unicon";
// Habilitar el reporte de excepciones en MySQLi (esencial para el bloque try-catch)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
// Inicializar la conexión mediante el objeto MySQLi
$conexion = new mysqli($host, $usuario, $password, $base_datos);
// Establecer el juego de caracteres a UTF-8 para evitar problemas con tildes o eñes
$conexion->set_charset("utf8mb4");
// PHP y MariaDB deben usar la misma zona horaria (si no, "hace 5 h" o los bloqueos de login salen mal)
date_default_timezone_set(defined('APP_TIMEZONE') ? APP_TIMEZONE : 'America/Caracas');
$conexion->query("SET time_zone = '" . date('P') . "'");
} catch (mysqli_sql_exception $e) {
// En entornos de producción, se recomienda registrar el error en un log y no mostrar detalles internos
error_log("[conexion] " . $e->getMessage());
die(defined('APP_ENV') && APP_ENV === 'produccion'
    ? "El sistema no está disponible en este momento. Intenta más tarde."
    : "Error crítico de conexión a la Base de Datos: " . $e->getMessage());
}
?>

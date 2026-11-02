<?php
 // ARCHIVO DE CONSTANTES GLOBALES Útil para cambiar cosas mas rapido


// 1. INFORMACIÓN DE LA EMPRESA Y SISTEMA
define('NOMBRE_SISTEMA', 'Unicon HelpDesk');
define('VERSION_SISTEMA', '1.0.0');
define('EMPRESA_NOMBRE', 'Industrias Unicon C.A.');

// Ajustes locales opcionales (config/local.php): APP_ENV, URL_BASE, credenciales de BD. Ver config/local.ejemplo.php
if (is_file(__DIR__ . '/../config/local.php')) require_once __DIR__ . '/../config/local.php';
if (!defined('APP_ENV')) define('APP_ENV', 'local');          // 'local' | 'produccion'
date_default_timezone_set(defined('APP_TIMEZONE') ? APP_TIMEZONE : 'America/Caracas');   // misma hora en PHP y en la BD

// Rutas base: si no se fijó en config/local.php, se deduce de la petición
define('URL_BASE_CONFIGURADA', defined('URL_BASE'));
if (!defined('URL_BASE')) {
    $__esq  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $__host = preg_replace('/[^A-Za-z0-9.:\-\[\]]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $__raiz = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $__proy = realpath(__DIR__ . '/..');
    $__ruta = ($__raiz && $__proy && strpos($__proy, $__raiz) === 0)
        ? str_replace('\\', '/', substr($__proy, strlen($__raiz))) : '/helpdesk_unicon';
    define('URL_BASE', $__esq . '://' . $__host . rtrim($__ruta, '/') . '/');
    unset($__esq, $__host, $__raiz, $__proy, $__ruta);
}

// En producción los errores de PHP no se muestran en pantalla: van al log del servidor
if (APP_ENV === 'produccion') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}


// 2. CONFIGURACIÓN DE ROLES

define('ROL_USUARIO', 1);
define('ROL_TECNICO', 2);
define('ROL_ADMIN', 3);

// 3. ESTADOS DE TICKETS
define('TICKET_ABIERTO', 1);
define('TICKET_PROCESO', 2);
define('TICKET_PENDIENTE', 3);
define('TICKET_RESUELTO', 4);
define('TICKET_CERRADO', 5);

// 4. ESTADOS DE INVENTARIO
define('EQUIPO_OPERATIVO', 1);
define('EQUIPO_ALMACEN', 2);
define('EQUIPO_MANTENIMIENTO', 3);
define('EQUIPO_DESINCORPORADO', 4);

// 5. SEGURIDAD Y REGLAS DE NEGOCIO
define('LONGITUD_MIN_PASS', 12);
define('INTENTOS_FALLIDOS_MAX', 5);
define('TIEMPO_SESION_MINUTOS', 30);

// 6. CONTACTO CORPORATIVO
define('EMAIL_SOPORTE', 'SoporteTecnicoSistemas@unicon.com.ve');
define('TELEFONO_SOPORTE', '+58 (244) 400.4800');
define('EXT', '2386');
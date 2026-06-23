<?php
 // ARCHIVO DE CONSTANTES GLOBALES Útil para cambiar cosas mas rapido


// 1. INFORMACIÓN DE LA EMPRESA Y SISTEMA
define('NOMBRE_SISTEMA', 'Unicon HelpDesk');
define('VERSION_SISTEMA', '1.0.0');
define('EMPRESA_NOMBRE', 'Industrias Unicon C.A.');

// Rutas base
define('URL_BASE', 'http://localhost/helpdesk/'); 


// 2. CONFIGURACIÓN DE ROLES

define('ROL_USUARIO', 1);
define('ROL_TECNICO', 2);
define('ROL_ADMIN', 3);

// 3. ESTADOS DE TICKETS
define('TICKET_ABIERTO', 1);
define('TICKET_PROCESO', 2);
define('TICKET_RESUELTO', 3);

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
<?php
// config/local.ejemplo.php  →  copia este archivo como config/local.php y ajusta SOLO lo que necesites.
// local.php NO se sube a GitHub (agrégalo a .gitignore). Todo es opcional: sin este archivo todo sigue funcionando como hoy.

// 'local' (tu XAMPP) o 'produccion' (servidor real: oculta errores de PHP y los manda al log)
define('APP_ENV', 'local');

// Dirección pública del sistema, con "/" al final. En producción DEBE definirse aquí
// (si no, se deduce de la petición y no se envían correos de recuperación por seguridad).
// define('URL_BASE', 'https://soporte.unicon.com.ve/');

// Credenciales de la base de datos (por defecto: localhost / root / sin clave / helpdesk_unicon)
// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'helpdesk_unicon');

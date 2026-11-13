<?php
// config/local.ejemplo.php  →  copia este archivo como config/local.php y ajusta SOLO lo que necesites.
// local.php NO se sube a GitHub (agrégalo a .gitignore). Todo es opcional: sin este archivo todo sigue funcionando como hoy.

// 'local' (tu XAMPP) o 'produccion' (servidor real: oculta errores de PHP y los manda al log)
define('APP_ENV', 'produccion');

// Dirección pública del sistema, con "/" al final. En producción DEBE definirse aquí
// (si no, se deduce de la petición y no se envían correos de recuperación por seguridad).
define('URL_BASE', 'https://helpdeskunicon.gt.tc/');

// Credenciales de la base de datos (por defecto: localhost / root / sin clave / helpdesk_unicon)
 define('DB_HOST', 'sql302.infinityfree.com');
 define('DB_USER', 'if0_43034800');
 define('DB_PASS', 'cw1pJW6jx2EH');
 define('DB_NAME', 'if0_43034800_helpdesk_unicon');
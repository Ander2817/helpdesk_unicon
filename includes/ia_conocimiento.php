<?php
// includes/ia_conocimiento.php — Base de conocimiento INTEGRADA del asistente (guía del propio sistema + consejos generales de TI), por rol.
// No usa la base de datos ni tablas nuevas. Sirve para: (1) responder localmente aunque la IA no esté disponible y
// (2) darle contexto fiable al modelo cuando sí lo está. Para añadir una respuesta, agrega una línea al arreglo correspondiente.
// Formato: [palabras_clave (texto en minúsculas sin tildes, separadas por coma), 'Título', 'Respuesta']

function ia_norm(string $t): string { return strtr(mb_strtolower($t), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n']); }

function ia_guia_entradas(int $rol): array {
    // ---- Consejos generales de TI (para todos los roles)
    $general = [
        ['lenta,lento,tarda,congela,trabada,colgada', 'Equipo lento', "1) Reinicia el equipo (no basta con suspenderlo). 2) Cierra programas y pestañas que no uses. 3) Comprueba que Windows no esté actualizando. 4) Libera espacio en el disco (menos de 10 % libre ralentiza todo). 5) Si sigue igual después de reiniciar, crea un ticket indicando desde cuándo ocurre."],
        ['internet,wifi,red,conexion,conectar,sin red', 'Sin internet o red', "1) Mira si el cable está bien conectado o si el Wi-Fi está activado. 2) Prueba abrir otra página y, si puedes, usa otro equipo para saber si falla solo el tuyo. 3) Reinicia el equipo. 4) Si falla en varios equipos a la vez, es la red: crea un ticket con prioridad alta indicando el área afectada."],
        ['imprimir,impresora,impresion,cola,atasco,papel,toner', 'Problemas de impresión', "1) Verifica que la impresora esté encendida y con papel/tóner. 2) Cancela trabajos atascados en la cola de impresión. 3) Comprueba que sea la impresora seleccionada. 4) Apaga y enciende la impresora. Si sigue sin imprimir, crea el ticket con el modelo de la impresora."],
        ['contrasena,clave,password,olvide,bloqueada,acceso,iniciar sesion,no puedo entrar', 'Contraseña o acceso', "Si olvidaste tu contraseña o tu cuenta está bloqueada, crea un ticket (categoría de cuentas/accesos) para que soporte la restablezca. Por seguridad no compartas tu clave con nadie, ni siquiera con soporte. Tras varios intentos fallidos el acceso se bloquea unos minutos: espera y vuelve a intentar."],
        ['pantalla,monitor,negro,sin imagen,video,hdmi', 'Pantalla sin imagen', "1) Revisa que el monitor esté encendido y el cable (HDMI/VGA/DP) firme en ambos extremos. 2) Prueba presionar Windows + P y elegir «Duplicar». 3) Si el equipo enciende pero no hay imagen, reinícialo. 4) Si persiste, ticket indicando el código del equipo."],
        ['correo,outlook,email,bandeja', 'Correo electrónico', "1) Comprueba que tengas internet. 2) Cierra y vuelve a abrir el programa de correo. 3) Si pide la clave una y otra vez, avisa por ticket. 4) Si no puedes enviar adjuntos grandes, prueba comprimirlos o compartir un enlace."],
        ['virus,malware,sospechoso,phishing,estafa,hackeo', 'Seguridad / correo sospechoso', "No abras enlaces ni archivos de correos que no esperabas, aunque parezcan de alguien conocido. No ingreses tu clave en páginas a las que llegaste por un enlace. Si ya lo hiciste, cambia tu contraseña y crea un ticket urgente describiendo qué pasó."],
        ['respaldo,backup,perdi,archivo,borre,recuperar', 'Archivos perdidos', "No sigas guardando cosas en el equipo si borraste algo importante (puede sobrescribirse). Revisa la Papelera de reciclaje y las «Versiones anteriores» del archivo. Crea un ticket con el nombre del archivo, la carpeta y la fecha aproximada."],
    ];
    // ---- Cómo usar el sistema, por rol
    $usuario = [
        ['ayuda,puedes,haces,hola,buenas,que sabes,funciones,como funciona', 'Qué puedo hacer por ti', "Puedo: 1) ayudarte a resolver un problema antes de crear un ticket, 2) explicarte cómo usar HelpDesk (crear, seguir y valorar tickets, comentar, adjuntar archivos), y 3) darte consejos de internet, impresión, contraseñas, pantalla, correo y seguridad. Cuéntame qué te pasa."],
        ['crea,creo,nuevo ticket,abrir ticket,abro,reportar,reporto,levantar,generar ticket,hacer un ticket,hago un ticket', 'Cómo crear un ticket', "Menú «Nuevo ticket»: describe el problema (qué pasó, desde cuándo, qué mensaje sale), elige la categoría y la prioridad, selecciona el equipo afectado si aplica y adjunta una captura si ayuda. Al enviarlo recibirás un código (ej. TK-0001) para seguirlo."],
        ['qr,escanear,etiqueta,codigo qr,pegatina', 'Reportar un equipo con su QR', "Cada equipo puede tener una etiqueta QR. Escanéala con el teléfono: si ya iniciaste sesión se abre «Nuevo ticket» con ese equipo elegido; si no, inicia sesión y vuelve a escanearla."],
        ['estado,abierto,proceso,pendiente,resuelto,cerrado,significa', 'Estados de un ticket', "Abierto: recibido, aún sin atender. En proceso: un técnico lo está trabajando. Pendiente: el técnico espera una respuesta o dato tuyo (responde en los comentarios para que avance). Resuelto: ya tiene solución; confírmala. Cerrado: finalizado. Un ticket resuelto que no reabres se cierra solo pasados unos días."],
        ['pendiente,esperando,responder,comentario,tecnico pregunta', 'Ticket en «Pendiente»', "Significa que el técnico necesita algo de ti. Abre el ticket y responde en los comentarios (puedes adjuntar archivos). Mientras esté pendiente, el reloj del tiempo de atención se detiene, así que no te perjudica."],
        ['cuanto tarda,tiempo,sla,demora,cuando,respuesta,plazo,urgente,prioridad', 'Tiempos de atención', "Cada prioridad tiene un tiempo objetivo de atención que ves en el ticket (barra de SLA). Una prioridad más alta se atiende antes, pero úsala solo si el trabajo está detenido o afecta a varias personas."],
        ['seguimiento,ver mis tickets,mis tickets,historial,estado de mi ticket', 'Seguir mis tickets', "En «Mis tickets» ves todos los que has creado y su estado. Al abrir uno ves el historial, los comentarios y las notificaciones (campana) cuando el técnico responde o cambia el estado."],
        ['valorar,encuesta,calificar,estrellas,satisfaccion,opinion', 'Valorar la atención', "Cuando un ticket pasa a Resuelto/Cerrado puedes calificar la atención de 1 a 5 estrellas y dejar un comentario. Eso ayuda a mejorar el servicio."],
        ['adjuntar,archivo,captura,imagen,foto,subir', 'Adjuntar archivos', "Al crear el ticket o en un comentario puedes adjuntar capturas, PDF y otros archivos permitidos. Una captura del mensaje de error acelera mucho la solución."],
        ['perfil,cambiar clave,cambiar contrasena,mis datos,telefono', 'Mi perfil', "En «Perfil» puedes actualizar tus datos de contacto y cambiar tu contraseña. Usa una clave de al menos 8 caracteres que no uses en otros sitios."],
        ['cancelar,cerrar mi ticket,reabrir,ya se arreglo,resolvio solo', 'Ya se resolvió / reabrir', "Si el problema se arregló solo, escribe un comentario en el ticket para que el técnico lo cierre. Si un ticket resuelto vuelve a fallar, coméntalo ahí mismo: se retoma."],
        ['base de conocimiento,articulos,manual,guia,como hacer', 'Base de conocimiento', "Antes de crear un ticket puedes buscar en la base de conocimiento: contiene guías paso a paso redactadas por soporte. Si encuentras tu caso, puede que lo resuelvas en minutos."],
    ];
    $tecnico = [
        ['ayuda,puedes,haces,hola,buenas,que sabes,funciones', 'Qué puedo hacer por ti', "Dentro de un ticket puedo darte diagnóstico, redactar la solución, resumirlo o proponer una respuesta al usuario. También respondo dudas de diagnóstico (impresoras, red, cuentas, Windows) y de cómo usar el sistema."],
        ['tomar,tomo,tomas,asignar,asignarme,autoasignar,sin asignar,agarrar', 'Tomar un ticket', "En «Tickets» ve a los sin asignar y toma el ticket: pasa a ser tuyo y su estado cambia a En proceso. Revisa la descripción, el equipo asociado y los adjuntos antes de contactar al usuario."],
        ['pendiente,esperando usuario,pausar,pausa,sla', 'Pendiente y pausa del SLA', "Si necesitas un dato o una acción del usuario, cambia el ticket a Pendiente y explícalo en un comentario. Mientras esté Pendiente el SLA se pausa: ese tiempo no cuenta contra ti. Al volver a En proceso el reloj continúa."],
        ['cerrar,resuelto,solucion,resolver,cierre', 'Resolver y cerrar', "Al resolver, rellena el campo «Solución» (qué se hizo y la causa): alimenta la base de casos similares que usa este asistente. El estado Resuelto avisa al usuario para que valore; si no responde, el sistema lo cierra automáticamente pasados unos días."],
        ['mantenimiento,mantenimientos,preventivo,calendario,proximo,registro un mantenimiento,registrar un mantenimiento', 'Mantenimientos de equipos', "En «Inventario» registra el mantenimiento de un equipo (tipo, descripción, fecha y próxima fecha). El calendario de mantenimientos muestra los próximos y los vencidos; recibes un aviso unos días antes."],
        ['equipo,inventario,serial,codigo,historial del equipo,ficha', 'Consultar un equipo', "El inventario muestra marca, modelo, serial, ubicación y estado de cada equipo, además de sus mantenimientos y los tickets asociados. Un equipo con muchos tickets seguidos probablemente necesita revisión profunda."],
        ['base de conocimiento,kb,articulo,publicar,manual', 'Base de conocimiento', "Si resolviste algo que seguramente se repetirá, documenta el procedimiento como artículo (si tienes permiso de edición). Título claro, pasos numerados y el síntoma tal como lo describe el usuario."],
        ['comentario,responder,usuario,mensaje,nota interna', 'Comunicarte con el usuario', "Usa los comentarios del ticket: el usuario recibe una notificación. Sé concreto (qué haces, qué necesitas, cuándo). Puedo redactar el mensaje por ti desde el botón «Respuesta» dentro del ticket."],
        ['reporte,estadisticas,mis metricas,rendimiento,satisfaccion', 'Mis métricas', "Tu panel muestra tus tickets asignados, pendientes y resueltos. La satisfacción de tus usuarios (estrellas) la ve la administración; responde rápido y deja la solución bien redactada para mejorarla."],
        ['escalar,no puedo,no se resolver,administrador,ayuda del admin', 'Cuando no puedes resolverlo', "Deja constancia en un comentario de lo que ya probaste, ponlo Pendiente si dependes de alguien externo y avisa a la administración. Si el equipo parece dañado, regístralo en el inventario con estado «mantenimiento»."],
    ];
    $admin = [
        ['ayuda,puedes,haces,hola,buenas,que sabes,funciones', 'Qué puedo hacer por ti', "Puedo darte cifras reales del sistema (tickets de la semana, SLA vencidos y en riesgo, carga por técnico, categorías y equipos con más fallas, satisfacción) y explicarte cómo hacer cualquier tarea de administración. Pregunta, por ejemplo: «¿Quién tiene más carga?» o «¿Cómo desactivo un usuario?»."],
        ['crear usuario,crear un usuario,crear cuenta,nuevo usuario,usuario nuevo,agregar usuario,agrego un usuario,anadir usuario,dar de alta,alta de usuario', 'Crear un usuario', "Menú «Usuarios»: botón de nuevo usuario, completa nombre, correo, usuario de acceso, contraseña inicial, departamento y rol (Usuario, Técnico o Administrador). Indícale que cambie su clave en el primer ingreso."],
        ['desactiv,eliminar usuario,eliminar a un usuario,eliminar un usuario,borrar usuario,borrar a un usuario,dar de baja,baja de usuario,inactivo,suspender,bloquear usuario', 'Dar de baja a un usuario', "Nada se borra: «eliminar» pasa al usuario a Inactivo para conservar su historial de tickets y la bitácora. Desde «Gestión de estados» puedes poner también Suspendido, Reposo, Vacaciones o Pasante, y decidir qué estados bloquean el acceso. No puedes bloquearte a ti mismo ni al último administrador activo."],
        ['estado,estados,reposo,vacaciones,pasante,suspendido,cambiar estado,gestion de estados', 'Estados de usuarios y equipos', "«Gestión de estados» (menú) permite cambiar el estado de usuarios, equipos y departamentos con un desplegable, filtrar por estado, y agregar estados nuevos a la lista sin tocar datos. Cada cambio queda en la bitácora."],
        ['rol,roles,permisos,permiso,tecnico puede', 'Roles y permisos', "En «Roles» defines qué puede hacer cada rol (por ejemplo editar la base de conocimiento, cerrar tickets o registrar mantenimientos). Los cambios aplican de inmediato al siguiente ingreso de cada persona."],
        ['sla,prioridad,prioridades,tiempo objetivo,vencido,riesgo', 'SLA y prioridades', "Cada prioridad tiene un tiempo objetivo (menú «Prioridades»). Un ticket queda «en riesgo» al llegar al 80 % y «vencido» al superarlo; el tiempo en estado Pendiente (esperando al usuario) no cuenta. Los avisos llegan a las campanas de la administración."],
        ['reporte,reportes,pdf,tendencia,comparacion,mes anterior,gestion', 'Reportes', "«Reportes» ofrece el reporte clásico y «Reporte de gestión» añade tendencia mensual, comparación con el período anterior, SLA por prioridad, equipos con fallas repetidas y satisfacción, con descarga en PDF. También hay exportación a CSV de los tickets."],
        ['calendario,mantenimiento,mantenimientos,preventivo,vencidos', 'Calendario de mantenimientos', "«Calendario de mantenimientos» muestra por mes los equipos con mantenimiento programado y una lista de vencidos; se puede exportar a .ics para Google Calendar u Outlook. Los técnicos y administradores reciben aviso unos días antes."],
        ['qr,etiquetas,imprimir etiqueta,codigo qr', 'Etiquetas QR de equipos', "«Etiquetas QR» genera una etiqueta por equipo (filtrable por departamento) lista para imprimir. Antes de imprimir confirma que URL_BASE (includes/constants.php) tenga la dirección real del servidor y no «localhost»."],
        ['categoria,categorias,agregar categoria', 'Categorías', "En «Categorías» creas o desactivas tipos de incidencia. Desactiva en lugar de borrar para no perder el histórico de los tickets."],
        ['inventario,equipo nuevo,registrar equipo,activo fijo', 'Inventario', "En «Inventario» registras equipos con código, tipo, marca, modelo, serial, ubicación y departamento. Los estados son activo, inactivo y mantenimiento; desde «Gestión de estados» puedes cambiarlos en bloque de uno en uno."],
        ['bitacora,auditoria,quien cambio,historial de cambios', 'Bitácora', "Cada acción sensible (cambios de estado, desactivaciones, nuevos estados, configuración) se registra con quién, cuándo y el valor anterior y nuevo. Consúltala si alguien pregunta «¿quién cambió esto?»."],
        ['estado del sistema,salud,tareas automaticas,revision,sistema_estado,errores', 'Estado del sistema', "«Estado del sistema» muestra si la revisión de SLA y las tareas automáticas (autocierre, avisos de mantenimiento) están al día, los accesos fallidos, el uso del asistente y la salud de las tablas. Hay un botón para forzar la revisión."],
        ['satisfaccion,encuestas,estrellas,calificacion', 'Satisfacción', "«Satisfacción» muestra el promedio de estrellas, la distribución, el promedio por técnico y por categoría, y los comentarios. Úsalo para detectar categorías mal atendidas."],
        ['seguridad,bloqueo,intentos,contrasena admin,accesos fallidos', 'Seguridad', "Tras varios intentos fallidos de acceso se bloquea por unos minutos la combinación usuario/IP. Revisa «Estado del sistema» para ver intentos recientes. Mantén el sitio con su archivo .htaccess y no dejes la carpeta herramientas accesible."],
    ];
    return array_merge($rol === 3 ? $admin : ($rol === 2 ? $tecnico : $usuario), $general);
}

/** Entradas más relevantes para una consulta. Devuelve [['t'=>título,'r'=>respuesta,'p'=>puntos], ...]. */
function ia_guia(int $rol, string $consulta, int $limite = 3): array {
    $q = ia_norm($consulta); if (mb_strlen($q) < 3) return [];
    $res = [];
    foreach (ia_guia_entradas($rol) as $e) {
        $p = 0;
        foreach (explode(',', $e[0]) as $k) { $k = trim($k); if ($k !== '' && preg_match('/(?<![a-z0-9])' . preg_quote($k, '/') . '/', $q)) $p += (strpos($k, ' ') !== false ? 2 : 1); }
        if ($p > 0) $res[] = ['t' => $e[1], 'r' => $e[2], 'p' => $p];
    }
    usort($res, fn($a, $b) => $b['p'] <=> $a['p']);
    return array_slice($res, 0, $limite);
}
function ia_guia_texto(array $g): string {
    return implode("\n\n", array_map(fn($x) => '▸ ' . $x['t'] . "\n" . $x['r'], $g));
}

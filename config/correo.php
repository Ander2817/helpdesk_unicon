<?php
// config/correo.ejemplo.php  →  copia como config/correo.php
//
// modo 'log'  : NO envía nada; escribe cada correo en storage/correos.log (ideal en XAMPP para probar y copiar el enlace).
// modo 'smtp' : envía de verdad usando PHPMailer (ya está en /vendor).
return [
    'modo'         => 'smtp',

    // --- solo para modo 'smtp' ---
    'host'         => 'smtp.gmail.com',       // ej. smtp.office365.com, smtp.gmail.com
    'puerto'       => 587,                    // 587 (tls) o 465 (ssl)
    'seguridad'    => 'tls',                  // 'tls' | 'ssl' | ''
    'usuario'      => 'monterreyleandro84@gmail.com',
    'clave'        => 'iugx uyhp nbiu owol',
    'desde'        => 'monterreyleandro84@gmail.com',
    'desde_nombre' => 'Unicon HelpDesk',

    // Además de la campana, enviar por correo los avisos de tickets (asignación, comentarios, resuelto, SLA…).
    // OJO: el envío es inmediato; con SMTP lento puede añadir 1-2 s a cada acción.
    'avisos_por_correo' => true,
];

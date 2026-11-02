<?php
// includes/correo.php — Envío de correos (PHPMailer ya está en /vendor).
// Configuración en config/correo.php (copia de config/correo.ejemplo.php). Sin ese archivo: modo 'log'
// = los correos NO salen: se guardan en storage/correos.log (para probar en XAMPP sin servidor de correo).
require_once __DIR__ . '/constants.php';

function correo_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;
    $cfg = ['modo' => 'log', 'host' => '', 'puerto' => 587, 'seguridad' => 'tls', 'usuario' => '', 'clave' => '',
            'desde' => EMAIL_SOPORTE, 'desde_nombre' => NOMBRE_SISTEMA, 'avisos_por_correo' => false];
    $f = __DIR__ . '/../config/correo.php';
    if (is_file($f)) { $c = require $f; if (is_array($c)) $cfg = array_merge($cfg, $c); }
    return $cfg;
}

/** HTML de correo con la imagen del sistema. $cuerpo ya debe venir escapado/seguro. */
function correo_plantilla(string $titulo, string $cuerpo, ?string $boton = null, ?string $url = null): string {
    $b = ($boton && $url)
        ? '<p style="margin:22px 0;"><a href="' . htmlspecialchars($url) . '" style="background:#E87722;color:#fff;text-decoration:none;padding:11px 22px;border-radius:7px;font-weight:600;display:inline-block;">' . htmlspecialchars($boton) . '</a></p>'
          . '<p style="font-size:12px;color:#64748b;">Si el botón no funciona, copia este enlace en tu navegador:<br><span style="word-break:break-all;">' . htmlspecialchars($url) . '</span></p>'
        : '';
    return '<div style="background:#f1f5f9;padding:24px;font-family:Arial,Helvetica,sans-serif;">'
         . '<div style="max-width:560px;margin:auto;background:#fff;border-radius:10px;overflow:hidden;border:1px solid #e2e8f0;">'
         . '<div style="background:#0f2a4a;color:#fff;padding:16px 22px;font-size:17px;font-weight:700;">' . htmlspecialchars(NOMBRE_SISTEMA) . '</div>'
         . '<div style="padding:22px;color:#1e293b;font-size:14px;line-height:1.55;"><h2 style="margin:0 0 12px;font-size:17px;color:#0f2a4a;">' . htmlspecialchars($titulo) . '</h2>'
         . $cuerpo . $b . '</div>'
         . '<div style="padding:12px 22px;background:#f8fafc;color:#94a3b8;font-size:11px;">' . htmlspecialchars(EMPRESA_NOMBRE) . ' — mensaje automático, no respondas a este correo.</div>'
         . '</div></div>';
}

/** Envía (o registra) un correo. Devuelve true si salió bien. Nunca lanza excepciones. */
function correo_enviar(string $para, string $asunto, string $html, string $nombre_para = ''): bool {
    $cfg = correo_config();
    if (!filter_var($para, FILTER_VALIDATE_EMAIL)) return false;

    if (($cfg['modo'] ?? 'log') !== 'smtp') {
        $dir = __DIR__ . '/../storage';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        preg_match_all('/href="([^"]+)"/', $html, $m); $m[1] = array_map('html_entity_decode', $m[1] ?? []);
        $texto = trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(['</p>', '</h2>', '<br>'], "\n", $html))));
        $linea = "==== " . date('Y-m-d H:i:s') . " ====\nPara:    $para\nAsunto:  $asunto\nEnlaces: " . implode("\n         ", array_unique($m[1] ?? [])) . "\nTexto:   $texto\n\n";
        return @file_put_contents($dir . '/correos.log', $linea, FILE_APPEND | LOCK_EX) !== false;
    }
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        $m = new PHPMailer\PHPMailer\PHPMailer(true);
        $m->isSMTP();
        $m->Host = $cfg['host'];
        $m->Port = (int)$cfg['puerto'];
        $m->SMTPAuth = ($cfg['usuario'] !== '');
        $m->Username = $cfg['usuario'];
        $m->Password = $cfg['clave'];
        $m->SMTPSecure = $cfg['seguridad'] === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                       : ($cfg['seguridad'] === 'tls' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : '');
        $m->SMTPAutoTLS = ($cfg['seguridad'] !== '');
        $m->Timeout = 8;
        $m->CharSet = 'UTF-8';
        $m->setFrom($cfg['desde'], $cfg['desde_nombre']);
        $m->addAddress($para, $nombre_para);
        $m->isHTML(true);
        $m->Subject = $asunto;
        $m->Body = $html;
        $m->AltBody = trim(strip_tags(str_replace(['</p>', '<br>'], "\n", $html)));
        $m->send();
        return true;
    } catch (Throwable $e) {
        error_log('[correo] ' . $e->getMessage());
        return false;
    }
}

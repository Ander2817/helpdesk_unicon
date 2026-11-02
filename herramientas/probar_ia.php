<?php
// herramientas/probar_ia.php — prueba la conexión con la IA y muestra el motivo exacto si falla.
// Uso (Git Bash/CMD):  C:\xampp\php\php.exe herramientas\probar_ia.php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Solo desde la línea de comandos.'); }
require_once __DIR__ . '/../includes/ia.php';
$c = ia_config();
$k = (string)$c['api_key'];
echo "Proveedor : {$c['proveedor']}\n";
echo "Modelo    : " . ($c['modelo'] ?: '(por defecto)') . "\n";
echo "Clave     : " . ($k === '' ? '(vacía)' : substr($k, 0, 4) . '…' . substr($k, -3) . ' (' . strlen($k) . ' caracteres)') . "\n";
echo "Activo    : " . (!empty($c['activo']) ? 'sí' : 'NO (activo = false)') . "\n";
echo "cURL      : " . (function_exists('curl_init') ? 'activo' : 'NO ACTIVO (extension=curl en php.ini)') . "\n";
echo "Disponible: " . (ia_disponible() ? 'sí' : 'NO: revisa proveedor/clave/activo en config/ia.php') . "\n\n";
$r = ia_llamar('Responde en español, una sola frase.', [], 'Di hola.');
echo $r['ok'] ? "OK → " . $r['texto'] . "\n" : "FALLÓ → " . $r['error'] . "\n";

<?php
// procesos/ia_asistente.php — endpoint JSON del asistente. POST: accion, mensaje, id_ticket, hist (JSON). Responde {ok, texto, fuente, categoria_id, categoria}.
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function ia_salir(array $d, int $code = 200): void { http_response_code($code); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit(); }
if (!isset($_SESSION['usser'], $_SESSION['id_rol'], $_SESSION['id_usuario'])) ia_salir(['ok' => false, 'texto' => 'Sesión expirada.'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') ia_salir(['ok' => false, 'texto' => 'Método no permitido.'], 405);
require_once '../includes/constants.php';
require_once '../includes/csrf.php';
csrf_verificar();
include '../config/conexion.php';
require_once '../includes/ia.php';

$rol = (int)$_SESSION['id_rol']; $uid = (int)$_SESSION['id_usuario'];
session_write_close();
$carpeta = [ROL_ADMIN => 'admin', ROL_TECNICO => 'tecnico', ROL_USUARIO => 'usuario'][$rol] ?? 'usuario';
$nombreRol = [ROL_ADMIN => 'admin', ROL_TECNICO => 'tecnico', ROL_USUARIO => 'usuario'][$rol] ?? 'usuario';

$permitidas = [ROL_USUARIO => ['ayuda_ticket', 'chat'],
               ROL_TECNICO => ['chat', 'ayuda_ticket', 'ticket_diagnostico', 'ticket_solucion', 'ticket_resumen', 'ticket_respuesta'],
               ROL_ADMIN   => ['chat', 'ayuda_ticket', 'admin_chat', 'ticket_diagnostico', 'ticket_solucion', 'ticket_resumen', 'ticket_respuesta']];
$accion = (string)($_POST['accion'] ?? 'chat');
if (!in_array($accion, $permitidas[$rol] ?? [], true)) ia_salir(['ok' => false, 'texto' => 'Esta opción no está disponible para tu rol.'], 403);

$mensaje = trim((string)($_POST['mensaje'] ?? ''));
if (mb_strlen($mensaje) > 1500) $mensaje = mb_substr($mensaje, 0, 1500);
$idTicket = (int)($_POST['id_ticket'] ?? 0);
$esTicket = strpos($accion, 'ticket_') === 0;
if (!$esTicket && mb_strlen($mensaje) < 3) ia_salir(['ok' => false, 'texto' => 'Escribe tu consulta.']);

if ($msg = ia_limite($conexion, $uid)) ia_salir(['ok' => false, 'texto' => $msg], 429);

// historial corto de la conversación (solo texto, últimos 6)
$hist = [];
$h = json_decode((string)($_POST['hist'] ?? '[]'), true);
if (is_array($h)) foreach (array_slice($h, -6) as $x)
    if (is_array($x) && in_array($x['r'] ?? '', ['u', 'a'], true) && is_string($x['t'] ?? null)) $hist[] = ['r' => $x['r'], 't' => ia_sanear($x['t'], 800)];

$cats = ia_categorias($conexion);
$contexto = ''; $kb = []; $sim = []; $catId = null; $catNom = null; $textoLocal = '';

try {
    if ($esTicket) {
        if (!$idTicket || !ia_puede_ver_ticket($conexion, $idTicket, $uid, $rol)) ia_salir(['ok' => false, 'texto' => 'No tienes acceso a ese ticket.'], 403);
        $t = ia_contexto_ticket($conexion, $idTicket);
        if (!$t) ia_salir(['ok' => false, 'texto' => 'Ticket no encontrado.'], 404);
        $contexto = ia_texto_ticket($t);
        $busca = $t['asunto'] . ' ' . $t['descripcion'];
        $kb = ia_kb($conexion, $busca); $sim = ia_similares($conexion, $busca, 3, $idTicket);
        $textoLocal = ia_local_ayuda($kb, $sim, $carpeta);
        if ($accion === 'ticket_solucion' && $textoLocal) $textoLocal = "Sin IA no puedo redactar la solución. " . $textoLocal;
    } elseif ($accion === 'admin_chat') {
        $S = ia_snapshot($conexion, ia_dias_de_pregunta($mensaje));
        $contexto = ia_snapshot_texto($S); $textoLocal = $contexto;
    } else {
        $kb = ia_kb($conexion, $mensaje); $sim = ($rol === ROL_USUARIO) ? [] : ia_similares($conexion, $mensaje, 3);
        if ($rol === ROL_USUARIO) $sim = ia_similares($conexion, $mensaje, 2); // para el usuario solo se usa en el modo local; no se muestran datos de personas
        $textoLocal = ia_local_ayuda($kb, $sim, $carpeta);
        if ($accion === 'ayuda_ticket' && $cats) $contexto = 'Categorías disponibles: ' . implode(', ', array_column($cats, 'nombre'));
    }
} catch (Throwable $e) {
    error_log('[ia] contexto: ' . $e->getMessage());
    ia_salir(['ok' => false, 'texto' => 'No se pudo preparar la consulta.'], 500);
}

// bloque de datos para el modelo
$ctx = [];
if ($contexto) $ctx[] = $contexto;
if ($kb)  $ctx[] = "Artículos de la base de conocimiento:\n" . implode("\n", array_map(fn($a) => '- ' . $a['titulo'] . ': ' . $a['texto'], $kb));
if ($sim && $rol !== ROL_USUARIO) $ctx[] = "Casos resueltos anteriormente:\n" . implode("\n", array_map(fn($s) => '- ' . $s['asunto'] . ' → ' . $s['solucion'], $sim));
$pregunta = $esTicket ? ['ticket_diagnostico' => 'Dame el diagnóstico de este ticket.', 'ticket_solucion' => 'Redacta la solución para este ticket.',
                         'ticket_resumen' => 'Resume este ticket.', 'ticket_respuesta' => 'Redacta una respuesta para el usuario.'][$accion] : ia_sanear($mensaje, 1500);
$extra = '';
if ($accion === 'ayuda_ticket' && $cats) $extra = "\nAl final añade una línea exacta: CATEGORIA_SUGERIDA: <una de las categorías disponibles o NINGUNA>.";
$envio = ($ctx ? "<<<DATOS\n" . implode("\n\n", $ctx) . "\nDATOS>>>\n\n" : '') . ($esTicket ? '' : 'Consulta del usuario: ') . $pregunta . $extra;

$fuente = 'local'; $texto = $textoLocal; $ok = true; $aviso = '';
if (ia_disponible()) {
    $r = ia_llamar(ia_prompt($nombreRol, $accion), $esTicket ? [] : $hist, $envio);
    if ($r['ok']) { $texto = $r['texto']; $fuente = 'ia'; }
    else { error_log('[ia] ' . $r['error']); $aviso = "\n\n(El asistente de IA no respondió; te muestro lo que hay en el sistema.)" . ($rol === ROL_ADMIN ? "\nMotivo (solo lo ves como admin): " . $r['error'] : ''); $ok = false; }
} else { $aviso = $textoLocal ? '' : "\n\n(El asistente de IA aún no está configurado.)"; }

$catResp = null;
if ($fuente === 'ia' && $accion === 'ayuda_ticket') { [$texto, $catId, $catNom] = ia_extraer_categoria($texto, $cats); }
if ($texto === '') $texto = 'No encontré información para eso.';
ia_registrar($conexion, $uid, $rol, $accion, $idTicket ?: null, $pregunta, $fuente, $ok);
ia_salir(['ok' => true, 'texto' => $texto . $aviso, 'fuente' => $fuente, 'categoria_id' => $catId, 'categoria' => $catNom]);

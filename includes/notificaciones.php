<?php
// includes/notificaciones.php
// Avisos para el usuario (campana de la barra superior). Regla de oro: NUNCA rompen la operación principal.
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/flujo_tickets.php';
require_once __DIR__ . '/notificaciones_extra.php';

function noti_cortar(string $s, int $n): string {
    $s = trim(preg_replace('/\s+/', ' ', $s));
    return mb_strlen($s) > $n ? mb_substr($s, 0, $n - 1) . '…' : $s;
}

/** id_usuario de quien está en sesión (usa la sesión o lo busca por login). */
function noti_id_actual(mysqli $c): ?int {
    if (!empty($_SESSION['id_usuario'])) return (int)$_SESSION['id_usuario'];
    if (empty($_SESSION['usser'])) return null;
    try {
        $q = $c->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
        $q->bind_param("s", $_SESSION['usser']);
        $q->execute();
        $f = $q->get_result()->fetch_assoc();
        $q->close();
        return $f ? (int)$f['id_usuario'] : null;
    } catch (Throwable $e) { return null; }
}

function noti_nombre(mysqli $c, ?int $id): string {
    if (!$id) return 'Alguien';
    try {
        $q = $c->prepare("SELECT CONCAT(nombres, ' ', apellidos) FROM usuarios WHERE id_usuario = ?");
        $q->bind_param("i", $id);
        $q->execute();
        $f = $q->get_result()->fetch_row();
        $q->close();
        return $f[0] ?? 'Alguien';
    } catch (Throwable $e) { return 'Alguien'; }
}

/** Datos básicos del ticket para armar los mensajes. */
function noti_ticket(mysqli $c, int $id_ticket): ?array {
    try {
        $q = $c->prepare("SELECT t.codigo_ticket, t.asunto, t.id_usuario_solicitante, t.id_usuario_asignado,
                                 p.nombre AS prioridad, p.nivel AS prioridad_nivel
                          FROM tickets t LEFT JOIN prioridades p ON t.id_prioridad = p.id_prioridad
                          WHERE t.id_tickets = ?");
        $q->bind_param("i", $id_ticket);
        $q->execute();
        $f = $q->get_result()->fetch_assoc();
        $q->close();
        return $f ?: null;
    } catch (Throwable $e) { return null; }
}

/** Crea UNA notificación. Si la tabla aún no tiene las columnas nuevas (tipo/url), usa el formato básico. */
function notificar(mysqli $c, int $id_usuario, ?int $id_ticket, string $mensaje, string $tipo = 'ticket', ?string $url = null): void {
    $mensaje = noti_cortar($mensaje, 255);
    try {
        $q = $c->prepare("INSERT INTO notificaciones (id_usuario, id_ticket, tipo, mensaje, url) VALUES (?, ?, ?, ?, ?)");
        $q->bind_param("iisss", $id_usuario, $id_ticket, $tipo, $mensaje, $url);
        $q->execute();
        $q->close();
        noti_correo($c, $id_usuario, $id_ticket, $mensaje, $tipo);   // copia por correo (si está activado en config/correo.php)
    } catch (Throwable $e) {
        try {
            $q = $c->prepare("INSERT INTO notificaciones (id_usuario, id_ticket, mensaje) VALUES (?, ?, ?)");
            $q->bind_param("iis", $id_usuario, $id_ticket, $mensaje);
            $q->execute();
            $q->close();
        } catch (Throwable $e2) {
            error_log('[notificaciones] ' . $e2->getMessage());
        }
    }
}

/** Notifica a varios usuarios (sin repetir y sin avisar a quien hizo la acción). */
function notificar_varios(mysqli $c, array $ids, ?int $id_ticket, string $mensaje, string $tipo = 'ticket', ?string $url = null, ?int $excluir = null): void {
    foreach (array_unique(array_filter(array_map('intval', $ids))) as $id) {
        if ($excluir !== null && $id === $excluir) continue;
        notificar($c, $id, $id_ticket, $mensaje, $tipo, $url);
    }
}

function noti_ids_admins(mysqli $c): array {
    try {
        $r = $c->query("SELECT id_usuario FROM usuarios WHERE id_rol = " . ROL_ADMIN . " AND estado = 'activo'");
        return array_column($r->fetch_all(), 0);
    } catch (Throwable $e) { return []; }
}

/** Aviso a todos los administradores activos (menos $excluir). $url es relativa a la raíz: 'admin/bitacora.php'. */
function noti_admins(mysqli $c, string $mensaje, string $tipo = 'general', ?string $url = null, ?int $excluir = null, ?int $id_ticket = null): void {
    notificar_varios($c, noti_ids_admins($c), $id_ticket, $mensaje, $tipo, $url, $excluir);
}

// ---------------------------------------------------------------------
//  EVENTOS DE TICKETS
// ---------------------------------------------------------------------

/** Ticket nuevo: avisa a los admins (resaltando prioridad alta/crítica) y, si lo creó otra persona, al solicitante. */
function noti_evento_ticket_creado(mysqli $c, int $id_ticket, ?int $id_actor, int $id_solicitante): void {
    try {
        $t = noti_ticket($c, $id_ticket); if (!$t) return;
        $nivel = (int)($t['prioridad_nivel'] ?? 0);
        $marca = $nivel >= 4 ? '[CRÍTICA] ' : ($nivel === 3 ? '[ALTA] ' : '');
        $quien = noti_nombre($c, $id_solicitante);
        noti_admins($c, "{$marca}Nuevo ticket {$t['codigo_ticket']} de $quien: " . noti_cortar($t['asunto'], 80) . " — sin asignar",
            'ticket', null, $id_actor, $id_ticket);
        if ($id_actor !== null && $id_actor !== $id_solicitante) {
            notificar($c, $id_solicitante, $id_ticket, "Se creó el ticket {$t['codigo_ticket']} a tu nombre: " . noti_cortar($t['asunto'], 80));
        }
    } catch (Throwable $e) { error_log('[notificaciones] ' . $e->getMessage()); }
}

/** El admin asignó el ticket: avisa al técnico y al solicitante. */
function noti_evento_asignado(mysqli $c, int $id_ticket, ?int $id_actor, int $id_tecnico, string $nombre_tecnico): void {
    try {
        $t = noti_ticket($c, $id_ticket); if (!$t) return;
        notificar($c, $id_tecnico, $id_ticket, "Se te asignó el ticket {$t['codigo_ticket']}: " . noti_cortar($t['asunto'], 90));
        if ((int)$t['id_usuario_solicitante'] !== $id_tecnico) {
            notificar($c, (int)$t['id_usuario_solicitante'], $id_ticket, "Tu ticket {$t['codigo_ticket']} fue asignado a $nombre_tecnico y ya está en atención.");
        }
    } catch (Throwable $e) { error_log('[notificaciones] ' . $e->getMessage()); }
}

/** Un técnico tomó el ticket: avisa a los admins y al solicitante. */
function noti_evento_tomado(mysqli $c, int $id_ticket, int $id_tecnico): void {
    try {
        $t = noti_ticket($c, $id_ticket); if (!$t) return;
        $nombre = noti_nombre($c, $id_tecnico);
        noti_admins($c, "$nombre tomó el ticket {$t['codigo_ticket']}: " . noti_cortar($t['asunto'], 80), 'ticket', null, $id_tecnico, $id_ticket);
        if ((int)$t['id_usuario_solicitante'] !== $id_tecnico) {
            notificar($c, (int)$t['id_usuario_solicitante'], $id_ticket, "Tu ticket {$t['codigo_ticket']} fue tomado por $nombre y ya está en atención.");
        }
    } catch (Throwable $e) { error_log('[notificaciones] ' . $e->getMessage()); }
}

/** Comentario nuevo: avisa a la otra parte (solicitante ⇄ técnico) y a los admins si nadie atiende el ticket. */
function noti_evento_comentario(mysqli $c, int $id_ticket, int $id_autor, string $texto): void {
    try {
        $t = noti_ticket($c, $id_ticket); if (!$t) return;
        $sol = (int)$t['id_usuario_solicitante'];
        $asig = (int)($t['id_usuario_asignado'] ?? 0);
        $destinos = [];
        if ($id_autor === $sol) {
            if ($asig) $destinos[] = $asig; else $destinos = noti_ids_admins($c);   // sin técnico: lo ven los admins
        } else {
            $destinos[] = $sol;                                                      // respondió el técnico o un admin
            if ($asig) $destinos[] = $asig;
        }
        $msg = noti_nombre($c, $id_autor) . " comentó en {$t['codigo_ticket']}: «" . noti_cortar($texto, 100) . "»";
        notificar_varios($c, $destinos, $id_ticket, $msg, 'ticket', null, $id_autor);
    } catch (Throwable $e) { error_log('[notificaciones] ' . $e->getMessage()); }
}

/** Cambio de estado: avisa al solicitante, a los admins si lo hizo un técnico, y al técnico si lo hizo un admin. */
function noti_evento_estado(mysqli $c, int $id_ticket, ?int $id_actor, int $rol_actor, int $estado_nuevo, int $estado_anterior): void {
    try {
        $t = noti_ticket($c, $id_ticket); if (!$t) return;
        $cod = $t['codigo_ticket'];
        $sol = (int)$t['id_usuario_solicitante'];
        $asig = (int)($t['id_usuario_asignado'] ?? 0);
        $actor = noti_nombre($c, $id_actor);
        $nombre_nuevo = nombre_estado($estado_nuevo);

        $para_solicitante = match ($estado_nuevo) {
            TICKET_RESUELTO  => "Tu ticket $cod fue resuelto. Revisa la solución en el detalle del ticket.",
            TICKET_CERRADO   => "Tu ticket $cod fue cerrado.",
            TICKET_PENDIENTE => "Tu ticket $cod quedó pendiente: se necesita tu respuesta o más información.",
            TICKET_PROCESO   => "Tu ticket $cod está en proceso.",
            TICKET_ABIERTO   => "Tu ticket $cod fue reabierto.",
            default          => "Tu ticket $cod cambió a «{$nombre_nuevo}».",
        };
        notificar($c, $sol, $id_ticket, $para_solicitante);

        if ($rol_actor === ROL_TECNICO) {
            $aviso = match ($estado_nuevo) {
                TICKET_RESUELTO  => "$actor resolvió el ticket $cod (listo para cerrar).",
                TICKET_PENDIENTE => "$actor dejó el ticket $cod en pendiente.",
                default          => null,
            };
            if ($aviso) noti_admins($c, $aviso, 'ticket', null, $id_actor, $id_ticket);
        } elseif ($rol_actor === ROL_ADMIN && $asig && $asig !== $id_actor && $asig !== $sol) {
            notificar($c, $asig, $id_ticket, "El administrador cambió el estado de $cod a «{$nombre_nuevo}».");
        }
    } catch (Throwable $e) { error_log('[notificaciones] ' . $e->getMessage()); }
}

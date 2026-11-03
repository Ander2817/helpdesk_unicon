<?php
// includes/sla.php — Indicador de cumplimiento del tiempo objetivo por prioridad (prioridades.tiempo_respuesta_objetivo, en minutos).
// Tiempo desde la creación hasta la resolución/cierre, DESCONTANDO el tiempo en que el ticket estuvo «Pendiente» (esperando al usuario).
require_once __DIR__ . '/constants.php';

const SLA_RIESGO_PCT = 80;   // desde este % del tiempo se marca "en riesgo"

/**
 * Fragmento SQL (sin parámetros) con los MINUTOS que el ticket $t pasó en estado «Pendiente» (esperando al usuario).
 * Ese tiempo NO cuenta contra el SLA. Se calcula con historial_ticket (cada cambio de estado ya queda registrado ahí):
 * cada tramo va desde que pasó a Pendiente hasta el siguiente cambio de estado (o hasta el cierre / ahora).
 * Se puede pegar dentro de cualquier consulta que tenga la tabla tickets con el alias $t.
 */
function sla_pausa_sql(string $t = 't'): string {
    return "COALESCE((SELECT SUM(TIMESTAMPDIFF(MINUTE, hp.fecha_cambio, COALESCE((SELECT MIN(hn.fecha_cambio) FROM historial_ticket hn
            WHERE hn.id_ticket = hp.id_ticket AND hn.campo_modificado = 'Estado'
              AND (hn.fecha_cambio > hp.fecha_cambio OR (hn.fecha_cambio = hp.fecha_cambio AND hn.id_historial > hp.id_historial))), $t.fecha_cierre, NOW())))
            FROM historial_ticket hp WHERE hp.id_ticket = $t.id_tickets AND hp.campo_modificado = 'Estado' AND hp.valor_nuevo = 'Pendiente'), 0)";
}
/** Minutos transcurridos «efectivos» de un ticket: desde la creación hasta el cierre/ahora, menos el tiempo pendiente del usuario. */
function sla_minutos_efectivos_sql(string $t = 't'): string {
    return "GREATEST(0, TIMESTAMPDIFF(MINUTE, $t.fecha_creacion, COALESCE($t.fecha_cierre, NOW())) - " . sla_pausa_sql($t) . ")";
}

function sla_formato(int $min): string {
    $min = max(0, $min);
    if ($min < 60)    return $min . ' min';
    if ($min < 1440)  return floor($min / 60) . ' h' . ($min % 60 ? ' ' . ($min % 60) . ' min' : '');
    $d = floor($min / 1440); $h = floor(($min % 1440) / 60);
    return $d . ' d' . ($h ? " $h h" : '');
}

/** Estado del SLA de un ticket: ['estado'=>ok|riesgo|vencido|cumplido|incumplido, 'texto', 'color', ...] o null. */
function sla_para_ticket(mysqli $c, int $id_ticket): ?array {
    try {
        $q = $c->prepare("SELECT t.id_estado, " . sla_minutos_efectivos_sql('t') . " AS trans, " . sla_pausa_sql('t') . " AS pausa, p.tiempo_respuesta_objetivo AS obj
                          FROM tickets t JOIN prioridades p ON t.id_prioridad = p.id_prioridad WHERE t.id_tickets = ?");
        $q->bind_param("i", $id_ticket); $q->execute();
        $f = $q->get_result()->fetch_assoc(); $q->close();
        if (!$f || (int)$f['obj'] <= 0) return null;
    } catch (Throwable $e) { return null; }

    $trans = (int)$f['trans']; $obj = (int)$f['obj']; $pausa = (int)$f['pausa'];
    $nota = $pausa > 0 ? ' · ' . sla_formato($pausa) . ' en pausa' : '';
    $cerrado = in_array((int)$f['id_estado'], [TICKET_RESUELTO, TICKET_CERRADO], true);
    $r = ['transcurrido' => $trans, 'objetivo' => $obj, 'pausa' => $pausa];
    if ($cerrado) {
        $ok = $trans <= $obj;
        return $r + ['estado' => $ok ? 'cumplido' : 'incumplido', 'color' => $ok ? '#16a34a' : '#dc2626',
                     'texto' => ($ok ? 'Cumplido' : 'Incumplido') . ' — ' . sla_formato($trans) . ' de ' . sla_formato($obj) . $nota];
    }
    if ((int)$f['id_estado'] === TICKET_PENDIENTE) {      // esperando al usuario: el reloj está detenido
        return $r + ['estado' => 'pausa', 'color' => '#64748b',
                     'texto' => 'En pausa (esperando al usuario) — van ' . sla_formato($trans) . ' de ' . sla_formato($obj) . $nota];
    }
    if ($trans >= $obj) return $r + ['estado' => 'vencido', 'color' => '#dc2626', 'texto' => 'Vencido hace ' . sla_formato($trans - $obj) . ' (objetivo ' . sla_formato($obj) . ')' . $nota];
    $est = ($trans * 100 / $obj >= SLA_RIESGO_PCT) ? 'riesgo' : 'ok';
    return $r + ['estado' => $est, 'color' => $est === 'riesgo' ? '#d97706' : '#16a34a',
                 'texto' => ($est === 'riesgo' ? 'En riesgo — ' : 'En tiempo — ') . 'quedan ' . sla_formato($obj - $trans) . $nota];
}

function sla_meta_html(array $s): string {
    return '<div class="hd-meta-item"><span>SLA</span><span style="color:' . $s['color'] . ';font-weight:600;">' . htmlspecialchars($s['texto']) . '</span></div>';
}

/**
 * Revisa los tickets activos y avisa (una sola vez por ticket y nivel) cuando entran en riesgo o vencen.
 * Se ejecuta como mucho cada 10 minutos, disparado por el refresco de la campana (no necesita cron).
 */
function sla_revisar_vencidos(mysqli $c): void {
    try {
        require_once __DIR__ . '/notificaciones.php';
        $c->query("INSERT IGNORE INTO sistema_estado (clave, valor, actualizado) VALUES ('sla_revision', '', '2000-01-01 00:00:00')");
        $u = $c->prepare("UPDATE sistema_estado SET actualizado = NOW() WHERE clave = 'sla_revision' AND actualizado < (NOW() - INTERVAL 10 MINUTE)");
        $u->execute();
        if ($u->affected_rows !== 1) return;     // otra petición lo hizo hace poco

        $pct = SLA_RIESGO_PCT / 100;
        $r = $c->query("SELECT t.id_tickets, t.codigo_ticket, t.id_usuario_asignado, p.nombre AS prioridad,
                               p.tiempo_respuesta_objetivo AS obj, " . sla_minutos_efectivos_sql('t') . " AS trans
                        FROM tickets t JOIN prioridades p ON t.id_prioridad = p.id_prioridad
                        WHERE t.id_estado NOT IN (" . TICKET_RESUELTO . "," . TICKET_CERRADO . "," . TICKET_PENDIENTE . ") AND p.tiempo_respuesta_objetivo > 0
                          AND TIMESTAMPDIFF(MINUTE, t.fecha_creacion, NOW()) >= p.tiempo_respuesta_objetivo * $pct LIMIT 200");
        $ins = $c->prepare("INSERT IGNORE INTO sla_alertas (id_ticket, nivel) VALUES (?, ?)");
        while ($t = $r->fetch_assoc()) {
            $id = (int)$t['id_tickets']; $obj = (int)$t['obj']; $trans = (int)$t['trans'];
            if ($trans < $obj * $pct) continue;                      // con el tiempo pendiente descontado aún no llega al umbral
            $nivel = $trans >= $obj ? 'vencido' : 'riesgo';
            $ins->bind_param("is", $id, $nivel); $ins->execute();
            if ($ins->affected_rows !== 1) continue;                 // ya se avisó

            $asig = (int)($t['id_usuario_asignado'] ?? 0);
            if ($nivel === 'vencido') {
                $msg = "[SLA VENCIDO] El ticket {$t['codigo_ticket']} ({$t['prioridad']}) superó su tiempo objetivo de " . sla_formato($obj) . '.';
                noti_admins($c, $msg, 'sla', null, null, $id);
                if ($asig) notificar($c, $asig, $id, $msg, 'sla');
            } else {
                $msg = "[SLA en riesgo] El ticket {$t['codigo_ticket']} ({$t['prioridad']}) vence en " . sla_formato($obj - $trans) . '.';
                if ($asig) notificar($c, $asig, $id, $msg, 'sla'); else noti_admins($c, $msg . ' Aún sin técnico.', 'sla', null, null, $id);
            }
        }
    } catch (Throwable $e) { error_log('[sla] ' . $e->getMessage()); }
}

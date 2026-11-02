<?php
/**
 * includes/flujo_tickets.php
 * Fuente única de verdad sobre qué cambios de estado se permiten.
 * La usan la pantalla (ticket_detalle.php) para dibujar las opciones y el servidor
 * (cambiar_estado_ticket.php) para validarlas, así nunca se contradicen.
 */
require_once __DIR__ . '/../includes/constants.php';
require_once __DIR__ . '/permisos.php';

/**
 * Estados a los que se puede pasar desde $estado_actual.
 * Un ticket NUNCA vuelve a "Abierto": ese estado solo significa "sin técnico todavía".
 * Pasar de Abierto a En Proceso ocurre automáticamente cuando el admin asigna un técnico.
 */
function estados_siguientes(int $estado_actual, int $rol_id): array {
    // El administrador puede mover el ticket a CUALQUIER estado que exista en la BD (distinto del actual),
    // incluido reabrirlo. El técnico sigue el flujo normal de abajo.
    if ($rol_id === ROL_ADMIN) {
        return array_values(array_diff(array_keys(todos_los_estados()), [$estado_actual]));
    }
    // Técnico: puede mover entre todos los estados de trabajo (En Proceso, Pendiente, Resuelto).
    // No puede devolver a «Abierto» (significa «sin técnico») y «Cerrado» solo con el permiso «Cerrar tickets».
    if ($rol_id === ROL_TECNICO) {
        if ($estado_actual === TICKET_CERRADO) return [];
        $ids = array_diff(array_keys(todos_los_estados()), [$estado_actual, TICKET_ABIERTO]);
        if (!puede_cerrar_tickets($rol_id)) $ids = array_diff($ids, [TICKET_CERRADO]);
        return array_values($ids);
    }
    $mapa = [
        TICKET_ABIERTO   => [TICKET_PROCESO],                       // solo ticket heredado: ya tiene técnico pero seguía "Abierto"
        TICKET_PROCESO   => [TICKET_PENDIENTE, TICKET_RESUELTO],
        TICKET_PENDIENTE => [TICKET_PROCESO, TICKET_RESUELTO],
        TICKET_RESUELTO  => puede_cerrar_tickets($rol_id) ? [TICKET_CERRADO] : [], // cerrar: admin, o técnico con el permiso «Cerrar tickets»
        TICKET_CERRADO   => [],
    ];
    return $mapa[$estado_actual] ?? [];
}

/**
 * Todos los estados que hay en la tabla estados_ticket: [id => nombre]. Se lee una sola vez por petición.
 * Si no hay conexión disponible, usa los 5 estados por defecto.
 */
function todos_los_estados(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [
        TICKET_ABIERTO   => 'Abierto',
        TICKET_PROCESO   => 'En Proceso',
        TICKET_PENDIENTE => 'Pendiente',
        TICKET_RESUELTO  => 'Resuelto',
        TICKET_CERRADO   => 'Cerrado',
    ];
    global $conexion;
    if (isset($conexion) && $conexion instanceof mysqli) {
        try {
            $db = [];
            $r = $conexion->query("SELECT id_estado_ticket, nombre FROM estados_ticket ORDER BY id_estado_ticket");
            while ($f = $r->fetch_assoc()) $db[(int)$f['id_estado_ticket']] = $f['nombre'];
            if ($db) $cache = $db;
        } catch (Throwable $e) { /* se queda con los de por defecto */ }
    }
    return $cache;
}

function puede_cerrar_tickets(int $rol_id): bool {
    if ($rol_id === ROL_ADMIN) return true;
    global $conexion;
    return isset($conexion) && $conexion instanceof mysqli && rol_tiene_permiso($conexion, $rol_id, 'tickets.cerrar');
}

function nombre_estado(int $id_estado): string {
    return todos_los_estados()[$id_estado] ?? (string)$id_estado;
}

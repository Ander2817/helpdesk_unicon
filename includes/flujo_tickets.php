<?php
/**
 * includes/flujo_tickets.php
 * Fuente única de verdad sobre qué cambios de estado se permiten.
 * La usan la pantalla (ticket_detalle.php) para dibujar las opciones y el servidor
 * (cambiar_estado_ticket.php) para validarlas, así nunca se contradicen.
 */
require_once __DIR__ . '/../includes/constants.php';

/**
 * Estados a los que se puede pasar desde $estado_actual.
 * Un ticket NUNCA vuelve a "Abierto": ese estado solo significa "sin técnico todavía".
 * Pasar de Abierto a En Proceso ocurre automáticamente cuando el admin asigna un técnico.
 */
function estados_siguientes(int $estado_actual, int $rol_id): array {
    $mapa = [
        TICKET_ABIERTO   => [TICKET_PROCESO],                       // solo ticket heredado: ya tiene técnico pero seguía "Abierto"
        TICKET_PROCESO   => [TICKET_PENDIENTE, TICKET_RESUELTO],
        TICKET_PENDIENTE => [TICKET_PROCESO, TICKET_RESUELTO],
        TICKET_RESUELTO  => ($rol_id === ROL_ADMIN) ? [TICKET_CERRADO] : [], // cerrar es del admin
        TICKET_CERRADO   => [],
    ];
    return $mapa[$estado_actual] ?? [];
}

function nombre_estado(int $id_estado): string {
    static $nombres = [
        TICKET_ABIERTO   => 'Abierto',
        TICKET_PROCESO   => 'En Proceso',
        TICKET_PENDIENTE => 'Pendiente',
        TICKET_RESUELTO  => 'Resuelto',
        TICKET_CERRADO   => 'Cerrado',
    ];
    return $nombres[$id_estado] ?? (string)$id_estado;
}

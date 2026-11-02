<?php
// includes/permisos.php
// Permisos configurables por rol. El Administrador SIEMPRE los tiene todos (no se puede quitar).
// Se editan en Admin > Roles y se guardan en la tabla rol_permisos.
require_once __DIR__ . '/constants.php';

/** Catálogo: clave => [nombre, descripción, roles a los que se puede conceder]. */
function permisos_catalogo(): array {
    return [
        'inventario.ver' => [
            'nombre' => 'Ver el inventario tecnológico',
            'descripcion' => 'Consulta de equipos en solo lectura. Crear, editar y eliminar equipos es solo del administrador.',
            'roles' => [ROL_TECNICO],
        ],
        'tickets.crear_para_otros' => [
            'nombre' => 'Crear tickets a nombre de otro usuario',
            'descripcion' => 'Permite elegir al solicitante al crear un ticket (llamadas o solicitudes presenciales).',
            'roles' => [ROL_TECNICO],
        ],
        'tickets.tomar' => [
            'nombre' => 'Tomar tickets sin asignar',
            'descripcion' => 'El técnico puede asignarse a sí mismo tickets que nadie atiende. Apagado: solo el administrador asigna.',
            'roles' => [ROL_TECNICO],
        ],
        'tickets.cerrar' => [
            'nombre' => 'Cerrar tickets resueltos',
            'descripcion' => 'Puede pasar un ticket de «Resuelto» a «Cerrado». Apagado: solo el administrador cierra.',
            'roles' => [ROL_TECNICO],
        ],
        'inventario.mantenimiento' => [
            'nombre' => 'Registrar mantenimientos de equipos',
            'descripcion' => 'Puede anotar mantenimientos (preventivo, correctivo…) en el historial de cada equipo.',
            'roles' => [ROL_TECNICO],
        ],
        'kb.editar' => [
            'nombre' => 'Escribir en la base de conocimiento',
            'descripcion' => 'Puede crear, editar y eliminar artículos de ayuda para los usuarios.',
            'roles' => [ROL_TECNICO],
        ],
    ];
}

/** Si la tabla rol_permisos aún no existe, se usan estos valores. */
function permisos_por_defecto(int $rol): array {
    return $rol === ROL_TECNICO ? ['inventario.ver', 'tickets.crear_para_otros', 'inventario.mantenimiento', 'kb.editar'] : [];
}

/** Lista de permisos que tiene un rol (el admin: todos). Se lee una vez por petición. */
function permisos_de_rol(mysqli $c, int $rol): array {
    static $cache = [];
    if (isset($cache[$rol])) return $cache[$rol];
    $todos = array_keys(permisos_catalogo());
    if ($rol === ROL_ADMIN) return $cache[$rol] = $todos;
    try {
        $q = $c->prepare("SELECT permiso FROM rol_permisos WHERE id_rol = ?");
        $q->bind_param("i", $rol);
        $q->execute();
        $lista = array_column($q->get_result()->fetch_all(), 0);
        $q->close();
    } catch (Throwable $e) {
        $lista = permisos_por_defecto($rol);
    }
    return $cache[$rol] = array_values(array_intersect($lista, $todos));
}

function rol_tiene_permiso(mysqli $c, int $rol, string $permiso): bool {
    return in_array($permiso, permisos_de_rol($c, $rol), true);
}

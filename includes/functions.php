<?php
// SISTEMA DE HELPDESK E INVENTARIO - Funciones para usar
// Archivo centralizado de funciones

function verificar_sesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['usser'])) {
        header("Location: /index.php");
        exit();
    }
}

function limpiar_inputs(string $dato): string {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    return $dato;
}


function procesar_opcional(?string $dato): ?string { // para nulls como telefono
    if ($dato === null || empty(trim($dato))) {
        return null; // transformar en null antes de db
    }
    return limpiar_inputs($dato); // si no esta en null
}

/**
 * Redirige al usuario y detiene la ejecución del script.
 * @param string $ruta Ruta a donde enviar al usuario.
 */
function redirigir(string $ruta = '../index.php'): void {
    header("Location: " . $ruta);
    exit();
}

/**
 * Guarda un mensaje temporal en la sesión (Mensaje Flash).
 * @param string $tipo El estilo de Bootstrap (success, danger, warning, info)
 * @param string $mensaje El texto que verá el usuario
 */
function set_alerta_flash(string $tipo, string $mensaje): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['alerta_flash'] = [
        'tipo' => $tipo,
        'mensaje' => $mensaje
    ];
}

/**
 * Muestra la alerta guardada en la interfaz y la borra de la sesión.
 * @return string Código HTML de la alerta de Bootstrap
 */
function mostrar_alerta_sistema(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['alerta_flash'])) {
        return '';
    }

    $tipo = $_SESSION['alerta_flash']['tipo'];
    $msj = $_SESSION['alerta_flash']['mensaje'];

    // Borramos la alerta para que no vuelva a aparecer al recargar
    unset($_SESSION['alerta_flash']);

    // Retorna el bloque HTML listo con  Bootstrap god
    return "<div class='alert alert-{$tipo} alert-dismissible fade show' role='alert'>
                {$msj}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}
?>
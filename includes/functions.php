<?php

 //SISTEMA DE HELPDESK E INVENTARIO - Funciones pa usar
 // Archivo centralizado de funciones

    //Redireccion



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
    return htmlspecialchars($dato, ENT_QUOTES, 'UTF-8');


    function procesar_opcional(?string $dato): ?string { //para nulls como telefono
    if (empty(trim($dato))) {
        return null; //transformar en null antes de db
    }
    return limpiar_inputs($dato); //si no esta en null
}


?>
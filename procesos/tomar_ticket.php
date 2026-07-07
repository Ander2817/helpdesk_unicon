<?php
session_start();
if (!isset($_SESSION['usser']) || (int)$_SESSION['id_rol'] < 2) {
    die('Acceso denegado.');
}

include('../config/conexion.php');

if (isset($_POST['id'])) {
    $id_ticket = (int)$_POST['id'];

    // Obtener id del técnico
    $stmt_user = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario_login = ?");
    $stmt_user->bind_param("s", $_SESSION['usser']);
    $stmt_user->execute();
    $tecnico = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();

    if (!$tecnico) {
        die('Usuario no encontrado.');
    }

    $id_tecnico = $tecnico['id_usuario'];
    $id_estado_proceso = 2; // En Proceso

    // Verificar que el ticket aún esté sin asignar
    $check = $conexion->prepare("SELECT id_tickets FROM tickets WHERE id_tickets = ? AND id_usuario_asignado IS NULL");
    $check->bind_param("i", $id_ticket);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $check->close();
        die('Este ticket ya fue tomado por otro técnico.');
    }
    $check->close();

    // Asignar técnico y cambiar estado a En Proceso
    $stmt = $conexion->prepare("UPDATE tickets SET id_usuario_asignado = ?, id_estado = ? WHERE id_tickets = ?");
    $stmt->bind_param("iii", $id_tecnico, $id_estado_proceso, $id_ticket);

    if ($stmt->execute()) {
        // Registrar en historial
        $campo = 'Asignación';
        $valor_anterior = 'Sin asignar';
        $valor_nuevo = 'Asignado a ' . $_SESSION['usser'];
        $stmt_hist = $conexion->prepare("INSERT INTO historial_ticket (id_ticket, id_usuario, campo_modificado, valor_anterior, valor_nuevo) VALUES (?, ?, ?, ?, ?)");
        $stmt_hist->bind_param("iisss", $id_ticket, $id_tecnico, $campo, $valor_anterior, $valor_nuevo);
        $stmt_hist->execute();
        $stmt_hist->close();

        echo 'success';
    } else {
        echo 'Error al asignar el ticket: ' . $conexion->error;
    }

    $stmt->close();
}

$conexion->close();
exit();

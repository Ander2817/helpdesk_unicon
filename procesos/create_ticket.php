<?php
session_start();
if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    die('<div class="alert alert-danger">Acceso denegado.</div>');
}

include('../config/conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Recoger y limpiar datos
    $asunto      = trim($_POST['asunto'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $id_categoria = (int)($_POST['id_categoria'] ?? 0);
    $id_prioridad = (int)($_POST['id_prioridad'] ?? 0);
    $id_inventario = !empty($_POST['id_inventario']) ? (int)$_POST['id_inventario'] : null;

    // 2. Validaciones básicas
    if (empty($asunto) || empty($descripcion) || !$id_categoria || !$id_prioridad) {
        die('<div class="alert alert-warning"><i class="fas fa-exclamation-circle me-2"></i>Completa todos los campos obligatorios.</div>');
    }

    // 3. Obtener datos del usuario solicitante
    $stmt_user = $conexion->prepare("SELECT id_usuario, id_departamento FROM usuarios WHERE usuario_login = ?");
    $stmt_user->bind_param("s", $_SESSION['usser']);
    $stmt_user->execute();
    $usuario = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();

    if (!$usuario) {
        die('<div class="alert alert-danger">Error al obtener los datos del usuario.</div>');
    }

    $id_usuario_solicitante = $usuario['id_usuario'];
    $id_departamento        = $usuario['id_departamento'];

    // 4. Generar código único TK-2026-0001
    $anio  = date('Y');
    $stmt_count = $conexion->prepare("SELECT COUNT(*) FROM tickets WHERE YEAR(fecha_creacion) = ?");
    $stmt_count->bind_param("s", $anio);
    $stmt_count->execute();
    $stmt_count->bind_result($total);
    $stmt_count->fetch();
    $stmt_count->close();

    $codigo_ticket = 'TK-' . $anio . '-' . str_pad($total + 1, 4, '0', STR_PAD_LEFT);

    // 5. Verificar que el código no exista (seguridad)
    $check = $conexion->prepare("SELECT id_tickets FROM tickets WHERE codigo_ticket = ?");
    $check->bind_param("s", $codigo_ticket);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        // Si por alguna razón existe, agrega un sufijo
        $codigo_ticket .= '-' . time();
    }
    $check->close();

    // 6. Insertar ticket (estado 1 = Abierto, sin técnico asignado)
    $id_estado = 1;
    $stmt = $conexion->prepare("
        INSERT INTO tickets 
        (codigo_ticket, asunto, descripcion, id_usuario_solicitante, id_departamento, id_categoria, id_prioridad, id_estado, id_inventario)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssiiiiii",
        $codigo_ticket,
        $asunto,
        $descripcion,
        $id_usuario_solicitante,
        $id_departamento,
        $id_categoria,
        $id_prioridad,
        $id_estado,
        $id_inventario
    );

    if ($stmt->execute()) {
        $id_ticket_nuevo = $conexion->insert_id;
        $stmt->close();

        echo '<div class="alert alert-success d-flex align-items-center gap-2">
                <i class="fas fa-check-circle fa-lg"></i>
                <div>
                    <strong>¡Ticket creado exitosamente!</strong><br>
                    <small>Código: <strong>' . htmlspecialchars($codigo_ticket) . '</strong> — Serás redirigido en un momento...</small>
                </div>
              </div>';
    } else {
        echo '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Error al crear el ticket: ' . htmlspecialchars($conexion->error) . '</div>';
    }
}

$conexion->close();
exit();

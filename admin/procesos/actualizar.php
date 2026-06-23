<?php

// 1. Incluimos la conexión subiendo dos niveles
include('../../config/conexion.php');

// 2. Capturamos el ID que viene por la URL
$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID de usuario no especificado.");
}

// 3. Traemos todas las opciones disponibles para armar los menú desplegables (SELECT)
$query_deptos = $conexion->query("SELECT id_departamento, nombre FROM departamentos WHERE estado = 'activo'");
$query_roles  = $conexion->query("SELECT id_rol, nombre FROM roles");

// 4. Traemos los datos actuales del usuario que se va a editar
$query_user = $conexion->query("SELECT id_usuario, nombres, apellidos, correo, usuario_login, telefono, estado, id_departamento, id_rol 
                                FROM usuarios 
                                WHERE id_usuario = " . (int)$id);

$row = $query_user->fetch_assoc();

if (!$row) {
    die("El usuario no existe en el sistema.");
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
</head>
<body>

    <form action="edit_user.php" method="POST">

        <h1>Editar Usuario</h1>

        <input type="hidden" name="id" value="<?= $row['id_usuario'] ?>">
        
        <label>Nombres:</label> <br>
        <input type="text" name="name" placeholder="Nombres" value="<?= htmlspecialchars($row['nombres']) ?>">
        <br><br>
        
        <label>Apellidos:</label> <br>
        <input type="text" name="lastname" placeholder="Apellidos" value="<?= htmlspecialchars($row['apellidos']) ?>">
        <br><br>
        
        <label>Correo:</label> <br>
        <input type="email" name="email" placeholder="Correo" value="<?= htmlspecialchars($row['correo']) ?>">
        <br><br>
        
        <label>Usuario de Login:</label> <br>
        <input type="text" name="user" placeholder="Usuario" value="<?= htmlspecialchars($row['usuario_login']) ?>">
        <br><br>
        
        <label>Teléfono:</label> <br>
        <input type="text" name="phone_number" placeholder="Número (opcional)" value="<?= htmlspecialchars($row['telefono'] ?? '') ?>">
        <br><br>
        
        <label>Departamento:</label> <br>
        <select name="dpto">
            <?php while ($depto = $query_deptos->fetch_assoc()): ?>
                <option value="<?= $depto['id_departamento'] ?>" <?= $depto['id_departamento'] == $row['id_departamento'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($depto['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>
        
        <label>Rol de Acceso:</label> <br>
        <select name="role">
            <?php while ($rol = $query_roles->fetch_assoc()): ?>
                <option value="<?= $rol['id_rol'] ?>" <?= $rol['id_rol'] == $row['id_rol'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($rol['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>
        
        <label>Estado de Cuenta:</label> <br>
        <select name="state">
            <option value="activo" <?= $row['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= $row['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            <option value="reposo" <?= $row['estado'] == 'reposo' ? 'selected' : '' ?>>Reposo</option>
            <option value="pasante" <?= $row['estado'] == 'pasante' ? 'selected' : '' ?>>Pasante</option>
        </select>
        <br><br>

        <input type="submit" value="Actualizar información">

    </form>
    
</body>
</html>
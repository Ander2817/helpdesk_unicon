<?php

include('../../config/conexion.php');

$query = $conexion->query("SELECT u.id_usuario, u.nombres, u.apellidos, u.correo, u.usuario_login, u.contraseña, u.telefono, u.estado, d.nombre AS nombre_departamento, r.nombre AS nombre_rol FROM usuarios u INNER JOIN departamentos d ON u.id_departamento = d.id_departamento INNER JOIN roles r ON u.id_rol = r.id_rol");


/*
EXPLICACIÓN DE LA CONSULTA SQL:
1. $conexion->query(...) -> Ejecuta la consulta SQL en la base de datos usando la conexión activa.
2. u. / d. / r.          -> Son "Alias de Tabla" (apodos). Le dicen a MySQL de qué tabla viene cada
                            columna (u = usuarios, d = departamentos, r = roles). Esto evita errores
                            cuando dos tablas tienen columnas con el mismo nombre (como "nombre").
3. AS                    -> Es un "Alias de Columna". Renombra una columna en el resultado final para
                            que PHP la reciba con un nombre único y limpio (ej. "nombre_departamento").
4. INNER JOIN            -> Es la instrucción que "funde" o cruza una tabla con otra en base a una
                            relación común. Aquí une usuarios con departamentos y también con roles.
5. ON                    -> Define el "puente" o la condición del cruce. Le especifica a MySQL qué
                            columnas deben coincidir exactamente para poder amarrar los registros
                            (ej. donde el 'id_departamento' del usuario sea igual al de la tabla de depto).
*/


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de usuarios</title>
</head>
<body>


    <div>
        <h2>Usuarios Registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>Correo</th>
                    <th>Usuario</th>
                    <th>Telefono</th>
                    <th>Departamento</th>
                    <th>Rol</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_array($query)) :?> 
                    <th> <?= $row['id_usuario']?> </th>
                    <th> <?= htmlspecialchars($row['nombres'])?> </th>
                    <th> <?= htmlspecialchars($row['apellidos'])?> </th>
                    <th> <?= htmlspecialchars($row['correo'])?> </th>
                    <th> <?= htmlspecialchars($row['usuario_login']) ?> </th>
                    <th> <?= htmlspecialchars($row['telefono'] ?? 'N/A') ?> </th>
                    <th> <?= htmlspecialchars($row['nombre_departamento'])?> </th>
                    <th> <?= htmlspecialchars($row['nombre_rol'])?> </th>
                    <th> <?= htmlspecialchars($row['estado'])?> </th>

                    <th> <a href="../procesos/actualizar.php?id=<?= $row['id_usuario']?>">Editar</a></th>
                    <th> <a href="../procesos/eliminar.php?id=<?= $row['id_usuario']?>">Eliminar</a></th>
                </tr>
                <?php endwhile ?>
            </tbody>
        </table>
    </div>
    


</body>
</html>
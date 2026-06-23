<?php
// Verificar sesión - ajusta la ruta según donde esté el archivo
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) {
    header("Location: ../../index.php"); exit();
}
include('../../config/conexion.php');

$query = $conexion->query("
    SELECT u.id_usuario, u.nombres, u.apellidos, u.correo, 
           u.usuario_login, u.telefono, u.estado, 
           d.nombre AS nombre_departamento, r.nombre AS nombre_rol 
    FROM usuarios u 
    INNER JOIN departamentos d ON u.id_departamento = d.id_departamento 
    INNER JOIN roles r ON u.id_rol = r.id_rol
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios — HelpDesk Unicon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        /* Como está en subcarpeta de admin, el sidebar no aplica aquí */
        /* Esta página es standalone con el mismo estilo */
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .page-wrapper { max-width: 1200px; margin: 0 auto; padding: 24px 20px; }
    </style>
</head>
<body>

<div class="page-wrapper">

    <!-- ENCABEZADO -->
    <div class="hd-page-header" style="margin-bottom:16px;">
        <div>
            <div class="hd-page-title">
                <i class="fas fa-users" style="color:var(--naranja);margin-right:6px;"></i>
                Gestión de Usuarios
            </div>
            <div class="hd-page-sub">Administración de cuentas del sistema</div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="../dashboard.php" class="hd-btn hd-btn-outline hd-btn-sm">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
            <a href="crear_usuario.php" class="hd-btn hd-btn-naranja hd-btn-sm">
                <i class="fas fa-plus"></i> Nuevo Usuario
            </a>
        </div>
    </div>

    <!-- TABLA -->
    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-list"></i> Usuarios Registrados</h5>
            <span style="font-size:0.72rem;color:var(--muted);"><?= $query->num_rows ?> registros</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Usuario</th>
                        <th>Teléfono</th>
                        <th>Departamento</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_array($query)): ?>
                    <tr>
                        <td><span style="font-family:monospace;color:var(--muted);font-size:0.7rem;">#<?= $row['id_usuario'] ?></span></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div class="hd-avatar" style="width:26px;height:26px;font-size:0.65rem;flex-shrink:0;">
                                    <?= strtoupper(substr($row['nombres'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight:500;font-size:0.8rem;"><?= htmlspecialchars($row['nombres'] . ' ' . $row['apellidos']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($row['correo']) ?></td>
                        <td><code style="font-size:0.72rem;background:#f1f5f9;padding:2px 6px;border-radius:4px;"><?= htmlspecialchars($row['usuario_login']) ?></code></td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($row['telefono'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($row['nombre_departamento']) ?></td>
                        <td>
                            <?php
                            $rol_color = match($row['nombre_rol']) {
                                'Administrador' => 'rojo',
                                'Técnico'       => 'naranja',
                                default         => 'azul'
                            };
                            ?>
                            <span class="hd-tag hd-tag-<?= $rol_color ?>"><?= htmlspecialchars($row['nombre_rol']) ?></span>
                        </td>
                        <td>
                            <?php
                            $estado_color = match($row['estado']) {
                                'activo'   => 'verde',
                                'inactivo' => 'rojo',
                                'reposo'   => 'amarillo',
                                'pasante'  => 'azul',
                                default    => 'gris'
                            };
                            ?>
                            <span class="hd-tag hd-tag-<?= $estado_color ?>"><?= htmlspecialchars($row['estado']) ?></span>
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;">
                                <a href="actualizar.php?id=<?= $row['id_usuario'] ?>" class="hd-btn hd-btn-outline hd-btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="../procesos/eliminar.php?id=<?= $row['id_usuario'] ?>" 
                                   class="hd-btn hd-btn-sm" 
                                   style="background:#fef2f2;color:var(--danger);border:1px solid #fecaca;"
                                   title="Eliminar"
                                   onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
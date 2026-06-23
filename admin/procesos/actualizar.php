<?php
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) {
    header("Location: ../../index.php"); exit();
}
include('../../config/conexion.php');

$id = $_GET['id'] ?? null;
if (!$id) die("ID no especificado.");

$query_deptos = $conexion->query("SELECT id_departamento, nombre FROM departamentos WHERE estado = 'activo'");
$query_roles  = $conexion->query("SELECT id_rol, nombre FROM roles");
$query_user   = $conexion->query("SELECT * FROM usuarios WHERE id_usuario = " . (int)$id);
$row          = $query_user->fetch_assoc();
if (!$row) die("Usuario no encontrado.");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario — HelpDesk Unicon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .page-wrapper { max-width: 700px; margin: 0 auto; padding: 24px 20px; }
        .hd-form-group { margin-bottom: 14px; }
        .hd-label { font-size: 0.78rem; font-weight: 600; color: var(--azul); margin-bottom: 5px; display: block; }
        .hd-input {
            width: 100%; padding: 8px 12px;
            border: 1px solid var(--borde); border-radius: 6px;
            font-family: 'Poppins', sans-serif; font-size: 0.82rem;
            color: var(--texto); background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .hd-input:focus { border-color: var(--naranja); box-shadow: 0 0 0 3px rgba(232,119,34,0.1); }
        .hd-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; }
        .hd-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    </style>
</head>
<body>

<div class="page-wrapper">

    <!-- HEADER -->
    <div class="hd-page-header" style="margin-bottom:16px;">
        <div>
            <div class="hd-page-title"><i class="fas fa-user-edit" style="color:var(--naranja);margin-right:6px;"></i>Editar Usuario</div>
            <div class="hd-page-sub">Modificando: <strong><?= htmlspecialchars($row['nombres'] . ' ' . $row['apellidos']) ?></strong></div>
        </div>
        <a href="usuarios.php" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <!-- FORMULARIO -->
    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-edit"></i> Información del Usuario</h5>
            <span style="font-size:0.7rem;color:var(--muted);">ID #<?= $row['id_usuario'] ?></span>
        </div>
        <div class="hd-card-body">
            <form id="Form" method="POST">
                <input type="hidden" name="id" value="<?= $row['id_usuario'] ?>">

                <div class="hd-form-row">
                    <div class="hd-form-group">
                        <label class="hd-label">Nombres</label>
                        <input type="text" name="name" class="hd-input" value="<?= htmlspecialchars($row['nombres']) ?>" required>
                    </div>
                    <div class="hd-form-group">
                        <label class="hd-label">Apellidos</label>
                        <input type="text" name="lastname" class="hd-input" value="<?= htmlspecialchars($row['apellidos']) ?>" required>
                    </div>
                </div>

                <div class="hd-form-row">
                    <div class="hd-form-group">
                        <label class="hd-label">Correo</label>
                        <input type="email" name="email" class="hd-input" value="<?= htmlspecialchars($row['correo']) ?>" required>
                    </div>
                    <div class="hd-form-group">
                        <label class="hd-label">Usuario de Login</label>
                        <input type="text" name="user" class="hd-input bg-light" value="<?= htmlspecialchars($row['usuario_login']) ?>" readonly>
                    </div>
                </div>

                <div class="hd-form-group">
                    <label class="hd-label">Teléfono <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                    <input type="text" name="phone_number" class="hd-input" value="<?= htmlspecialchars($row['telefono'] ?? '') ?>">
                </div>

                <div class="hd-form-row">
                    <div class="hd-form-group">
                        <label class="hd-label">Departamento</label>
                        <select name="dpto" class="hd-input hd-select" required>
                            <?php while ($depto = $query_deptos->fetch_assoc()): ?>
                            <option value="<?= $depto['id_departamento'] ?>" <?= $depto['id_departamento'] == $row['id_departamento'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($depto['nombre']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="hd-form-group">
                        <label class="hd-label">Rol de Acceso</label>
                        <select name="role" class="hd-input hd-select" required>
                            <?php while ($rol = $query_roles->fetch_assoc()): ?>
                            <option value="<?= $rol['id_rol'] ?>" <?= $rol['id_rol'] == $row['id_rol'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rol['nombre']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="hd-form-group">
                    <label class="hd-label">Estado de Cuenta</label>
                    <select name="state" class="hd-input hd-select">
                        <option value="activo"   <?= $row['estado']=='activo'   ? 'selected':'' ?>>Activo</option>
                        <option value="inactivo" <?= $row['estado']=='inactivo' ? 'selected':'' ?>>Inactivo</option>
                        <option value="reposo"   <?= $row['estado']=='reposo'   ? 'selected':'' ?>>Reposo</option>
                        <option value="pasante"  <?= $row['estado']=='pasante'  ? 'selected':'' ?>>Pasante</option>
                        <option value="vacaciones" <?= $row['estado']=='vacaciones' ? 'selected':'' ?>>Vacaciones</option>
                        <option value="suspendido" <?= $row['estado']=='suspendido' ? 'selected':'' ?>>Suspendido</option>
                    </select>
                </div>

                <!-- BOTONES -->
                <div id="resultado" class="mt-3"></div>

                <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px;padding-top:14px;border-top:1px solid var(--borde);">
                    <a href="usuarios.php" class="hd-btn hd-btn-outline">Cancelar</a>
                    <button type="submit" id="send" class="hd-btn hd-btn-naranja">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Capturamos el envío del formulario usando su ID correcto
$("#Form").on("submit", function(e) {
    // Evitamos que la página se recargue automáticamente
    e.preventDefault(); 

    var btn = $("#send");
    // Deshabilitamos el botón para evitar que el usuario haga click muchas veces seguidas
    btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url : "../procesos/edit_user.php", // La ruta hacia tu archivo PHP de procesamiento
        type : "POST",
        data : $("#Form").serialize(), // Convierte todos los campos del formulario en una cadena de datos
        success: function(resultado){
            // Inyectamos la alerta HTML que devuelve el PHP dentro de nuestro div
            $("#resultado").html(resultado);
            
            // Reactivamos el botón por si quieren volver a editar algo
            btn.prop("disabled", false).html('<i class="fas fa-save"></i> Guardar Cambios');
        },
        error: function() {
            // En caso de que falle la conexión o el servidor tire un error 500
            $("#resultado").html('<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Error crítico de conexión con el servidor.</div>');
            btn.prop("disabled", false).html('<i class="fas fa-save"></i> Guardar Cambios');
        }
    });
});
</script>
</body>
</html>
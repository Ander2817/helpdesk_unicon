<?php
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) die('Acceso denegado.');
include('../../../config/conexion.php');

$id = (int)($_GET['id'] ?? 0);
if (!$id) die('<div class="alert alert-danger">ID no válido.</div>');

$query_deptos = $conexion->query("SELECT id_departamento, nombre FROM departamentos WHERE estado = 'activo'");
$query_roles  = $conexion->query("SELECT id_rol, nombre FROM roles ORDER BY nivel_acceso ASC");
$result       = $conexion->query("SELECT * FROM usuarios WHERE id_usuario = $id");
$row          = $result->fetch_assoc();
if (!$row) die('<div class="alert alert-danger">Usuario no encontrado.</div>');
?>
<form id="form-editar">
    <input type="hidden" name="id" value="<?= $row['id_usuario'] ?>">
    
    <div class="hd-modal-section">Información Personal</div>
    <div class="hd-modal-grid">
        <div class="hd-field">
            <label>Nombres</label>
            <input type="text" name="name" class="hd-input" value="<?= htmlspecialchars($row['nombres']) ?>" required>
        </div>
        <div class="hd-field">
            <label>Apellidos</label>
            <input type="text" name="lastname" class="hd-input" value="<?= htmlspecialchars($row['apellidos']) ?>" required>
        </div>
    </div>
    
    <div class="hd-field">
        <label>Teléfono <span>(opcional)</span></label>
        <input type="text" name="phone_number" class="hd-input" value="<?= htmlspecialchars($row['telefono'] ?? '') ?>" placeholder="Ej. 04121234567">
    </div>

    <div class="hd-modal-section">Acceso al Sistema</div>
    <div class="hd-modal-grid">
        <div class="hd-field">
            <label>Correo Electrónico</label>
            <input type="email" name="email" class="hd-input" value="<?= htmlspecialchars($row['correo']) ?>" required>
        </div>
        <div class="hd-field">
            <label>Usuario de Login</label>
            <input type="text" name="user" class="hd-input" value="<?= htmlspecialchars($row['usuario_login']) ?>" readonly>
        </div>
    </div>

    <div class="hd-modal-section">Rol y Departamento</div>
    <div class="hd-modal-grid">
        <div class="hd-field">
            <label>Departamento</label>
            <select name="dpto" class="hd-input hd-select" required>
                <?php while ($d = $query_deptos->fetch_assoc()): ?>
                <option value="<?= $d['id_departamento'] ?>" <?= $d['id_departamento'] == $row['id_departamento'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['nombre']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="hd-field">
            <label>Rol de Acceso</label>
            <select name="role" class="hd-input hd-select" required>
                <?php while ($r = $query_roles->fetch_assoc()): ?>
                <option value="<?= $r['id_rol'] ?>" <?= $r['id_rol'] == $row['id_rol'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['nombre']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <div class="hd-field">
        <label>Estado</label>
        <select name="state" class="hd-input hd-select">
            <option value="activo"     <?= $row['estado']=='activo'     ? 'selected':'' ?>>Activo</option>
            <option value="inactivo"   <?= $row['estado']=='inactivo'   ? 'selected':'' ?>>Inactivo</option>
            <option value="reposo"     <?= $row['estado']=='reposo'     ? 'selected':'' ?>>Reposo</option>
            <option value="pasante"    <?= $row['estado']=='pasante'    ? 'selected':'' ?>>Pasante</option>
            <option value="vacaciones" <?= $row['estado']=='vacaciones' ? 'selected':'' ?>>Vacaciones</option>
            <option value="suspendido" <?= $row['estado']=='suspendido' ? 'selected':'' ?>>Suspendido</option>
        </select>
    </div>

    <div id="resultado-editar" style="margin-top:12px;"></div>

    <div class="hd-modal-footer" style="margin-top: 20px; padding: 14px 0 0 0; border-top: 1px solid #e2e8f0;">
        <button type="button" onclick="cerrarModal('modal-editar')" class="hd-btn hd-btn-outline">Cancelar</button>
        <button type="submit" id="btn-editar" class="hd-btn hd-btn-naranja">
            <i class="fas fa-save"></i> Guardar Cambios
        </button>
    </div>
</form>

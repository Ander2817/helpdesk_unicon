<?php
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) {
    die('<div class="alert alert-danger">Acceso denegado.</div>');
}
include('../../../config/conexion.php');

$id = (int)($_GET['id'] ?? 0);
$stmt = $conexion->prepare("SELECT * FROM departamentos WHERE id_departamento = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()):
?>
<div id="resultado-editar"></div>

<form id="form-editar">
    <input type="hidden" name="id_departamento" value="<?= $row['id_departamento'] ?>">
    
    <div class="hd-field">
        <label>Nombre del Departamento</label>
        <input type="text" name="nombre" class="hd-input" value="<?= htmlspecialchars($row['nombre']) ?>" required>
    </div>
    
    <div class="hd-field">
        <label>Descripción <span>(opcional)</span></label>
        <textarea name="descripcion" class="hd-textarea" rows="3"><?= htmlspecialchars($row['descripcion'] ?? '') ?></textarea>
    </div>

    <div class="hd-field">
        <label>Estado</label>
        <select name="estado" class="hd-input hd-select-input">
            <option value="activo" <?= $row['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= $row['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            <option value="clausurado" <?= $row['estado'] == 'clausurado' ? 'selected' : '' ?>>Clausurado</option>
        </select>
    </div>

    <div class="hd-modal-footer" style="padding: 14px 0 0 0; border-top: 1px solid #e2e8f0; margin-top:15px;">
        <button type="button" onclick="cerrarModal('modal-editar')" class="hd-btn hd-btn-outline">Cancelar</button>
        <button type="submit" class="hd-btn hd-btn-naranja"><i class="fas fa-save"></i> Guardar Cambios</button>
    </div>
</form>

<script>
$("#form-editar").on("submit", function(e) {
    e.preventDefault();
    $.ajax({
        url: 'actualizar_departamento.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(htmlResponse) {
            $("#resultado-editar").html(htmlResponse);
        }
    });
});
</script>
<?php 
endif;
$stmt->close();
$conexion->close();
?>
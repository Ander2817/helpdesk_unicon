<?php
// Verificar sesión
session_start();
if (!isset($_SESSION['usser']) || $_SESSION['id_rol'] != 3) {
    header("Location: ../../index.php"); exit();
}
include('../../config/conexion.php');

$query = $conexion->query("SELECT id_departamento, nombre, descripcion, estado FROM departamentos");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Departamentos — HelpDesk Unicon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .page-wrapper { max-width: 1200px; margin: 0 auto; padding: 24px 20px; }
        
        /* ===== MODALES ===== */
        .hd-modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);
            z-index: 9999; align-items: center; justify-content: center; padding: 20px;
        }
        .hd-modal-overlay.show { display: flex; animation: fadeOverlay 0.2s ease; }
        @keyframes fadeOverlay { from { opacity: 0; } to { opacity: 1; } }

        .hd-modal {
            background: #fff; border-radius: 14px; width: 100%; max-width: 550px;
            max-height: 90vh; overflow-y: auto; box-shadow: 0 24px 60px rgba(0,0,0,0.2);
            animation: slideModal 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes slideModal {
            from { opacity: 0; transform: translateY(-20px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .hd-modal-header {
            padding: 18px 22px; border-bottom: 1px solid #e2e8f0; display: flex;
            align-items: center; justify-content: space-between; position: sticky;
            top: 0; background: #fff; z-index: 1; border-radius: 14px 14px 0 0;
        }
        .hd-modal-header h5 { margin: 0; font-size: 0.92rem; font-weight: 700; color: #1a2a4a; display: flex; align-items: center; gap: 8px; }
        .hd-modal-header h5 i { color: #E87722; }

        .hd-modal-close {
            width: 28px; height: 28px; border-radius: 6px; border: none;
            background: #f1f5f9; color: #64748b; font-size: 1rem; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: all 0.15s;
        }
        .hd-modal-close:hover { background: #fef2f2; color: #dc2626; }
        .hd-modal-body { padding: 22px; }

        .hd-modal-footer {
            padding: 14px 22px; border-top: 1px solid #e2e8f0; display: flex; gap: 8px;
            justify-content: flex-end; position: sticky; bottom: 0; background: #fff; border-radius: 0 0 14px 14px;
        }

        .hd-field { margin-bottom: 14px; }
        .hd-field label { display: block; font-size: 0.75rem; font-weight: 600; color: #1a2a4a; margin-bottom: 5px; letter-spacing: 0.2px; }
        
        .hd-field .hd-input, .hd-field .hd-textarea, .hd-field .hd-select-input {
            width: 100%; padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 7px;
            font-family: 'Poppins', sans-serif; font-size: 0.82rem; color: #1e293b; background: #fff; outline: none;
        }
        .hd-field .hd-input:focus, .hd-field .hd-textarea:focus, .hd-field .hd-select-input:focus {
            border-color: #E87722; box-shadow: 0 0 0 3px rgba(232,119,34,0.1);
        }
        .hd-modal-section { font-size: 0.68rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #94a3b8; margin: 16px 0 10px; padding-bottom: 6px; border-bottom: 1px solid #f1f5f9; }
    </style>
</head>
<body>

<div class="hd-modal-overlay" id="modal-crear">
    <div class="hd-modal">
        <div class="hd-modal-header">
            <h5><i class="fas fa-building"></i> Nuevo Departamento</h5>
            <button class="hd-modal-close" onclick="cerrarModal('modal-crear')"><i class="fas fa-times"></i></button>
        </div>
        <div class="hd-modal-body">
            <div class="resultado-alerta" id="resultado-crear"></div>

            <form id="form-crear">
                <div class="hd-modal-section">Datos del Departamento</div>
                <div class="hd-field">
                    <label>Nombre del Departamento</label>
                    <input type="text" name="nombre" class="hd-input" placeholder="Ej. Soporte Técnico" required>
                </div>
                <div class="hd-field">
                    <label>Descripción <span>(opcional)</span></label>
                    <textarea name="descripcion" class="hd-textarea" rows="3" placeholder="Breve descripción..."></textarea>
                </div>
                <div class="hd-field">
                    <label>Estado</label>
                    <select name="estado" class="hd-input hd-select-input">
                        <option value="activo" selected>Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="clausurado">Clausurado</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="hd-modal-footer">
            <button type="button" onclick="cerrarModal('modal-crear')" class="hd-btn hd-btn-outline">Cancelar</button>
            <button type="submit" form="form-crear" class="hd-btn hd-btn-naranja"><i class="fas fa-plus"></i> Crear Departamento</button>
        </div>
    </div>
</div>

<div class="hd-modal-overlay" id="modal-editar">
    <div class="hd-modal">
        <div class="hd-modal-header">
            <h5><i class="fas fa-edit"></i> Editar Departamento</h5>
            <button class="hd-modal-close" onclick="cerrarModal('modal-editar')"><i class="fas fa-times"></i></button>
        </div>
        <div class="hd-modal-body">
            <div id="modal-editar-contenido">
                <div style="text-align:center;padding:40px;color:#94a3b8;">
                    <i class="fas fa-spinner fa-spin" style="font-size:1.8rem;"></i>
                    <p style="margin-top:10px;font-size:0.82rem;">Cargando datos...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="hd-page-header" style="margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div class="hd-page-title"><i class="fas fa-building" style="color:#E87722;margin-right:6px;"></i> Gestión de Departamentos</div>
            <div class="hd-page-sub">Administración de áreas</div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="../dashboard.php" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
            <button onclick="abrirModalCrear()" class="hd-btn hd-btn-naranja hd-btn-sm"><i class="fas fa-plus"></i> Nuevo Área</button>
        </div>
    </div>

    <div id="resultado-global" class="mb-3"></div>

    <div class="hd-card">
        <div style="overflow-x:auto;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $query->fetch_assoc()): ?>
                    <tr id="depto-row-<?= $row['id_departamento'] ?>">
                        <td><span style="font-family:monospace;color:#64748b;">#<?= $row['id_departamento'] ?></span></td>
                        <td><strong><?= htmlspecialchars($row['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($row['descripcion'] ?? '—') ?></td>
                        <td>
                            <?php $c = match($row['estado']){ 'activo'=>'verde','inactivo'=>'rojo',default=>'gris' }; ?>
                            <span class="hd-tag hd-tag-<?= $c ?>"><?= htmlspecialchars($row['estado']) ?></span>
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;">
                                <button onclick="abrirModalEditar(<?= $row['id_departamento'] ?>)" class="hd-btn hd-btn-outline hd-btn-sm"><i class="fas fa-edit"></i></button>
                                <button type="button" class="hd-btn hd-btn-sm btn-eliminar" data-id="<?= $row['id_departamento'] ?>" style="background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function abrirModalCrear() {
    $("#resultado-crear").html('');
    document.getElementById('modal-crear').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function cerrarModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = '';
}

// Envío Crear
$("#form-crear").on("submit", function(e) {
    e.preventDefault();
    $.ajax({
        url: 'departamentos/crear_departamento.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(htmlResponse) {
            $("#resultado-crear").html(htmlResponse);
        }
    });
});

// Abrir Editar
function abrirModalEditar(id) {
    document.getElementById('modal-editar').classList.add('show');
    document.body.style.overflow = 'hidden';
    $("#modal-editar-contenido").html('<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:1.8rem;color:#94a3b8;"></i></div>');
    
    $.ajax({
        url: 'departamentos/get_departamento.php',
        type: 'GET',
        data: { id: id },
        success: function(html) {
            $("#modal-editar-contenido").html(html);
        }
    });
}

// Eliminar
$(document).on('click', '.btn-eliminar', function() {
    if(confirm("¿Estás seguro de eliminar este departamento?")) {
        const id = $(this).data('id');
        $.ajax({
            url: 'departamentos/eliminar_departamento.php',
            type: 'POST',
            data: { id: id },
            success: function(htmlResponse) {
                $("#resultado-global").html(htmlResponse);
            }
        });
    }
});

// Cierres de modal accesorios (Esc y clicks externos)
document.addEventListener('keydown', function(e) { if(e.key === 'Escape') { cerrarModal('modal-crear'); cerrarModal('modal-editar'); } });
document.addEventListener('click', function(e) { ['modal-crear','modal-editar'].forEach(id => { if(e.target === document.getElementById(id)) cerrarModal(id); }); });
</script>
</body>
</html>
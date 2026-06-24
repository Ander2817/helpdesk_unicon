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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .page-wrapper { max-width: 1200px; margin: 0 auto; padding: 24px 20px; }
        /* ===== MODALES ===== */
.hd-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.hd-modal-overlay.show {
    display: flex;
    animation: fadeOverlay 0.2s ease;
}

@keyframes fadeOverlay {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.hd-modal {
    background: #fff;
    border-radius: 14px;
    width: 100%;
    max-width: 640px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 24px 60px rgba(0,0,0,0.2);
    animation: slideModal 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    scrollbar-width: thin;
}

@keyframes slideModal {
    from { opacity: 0; transform: translateY(-20px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.hd-modal-header {
    padding: 18px 22px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    background: #fff;
    z-index: 1;
    border-radius: 14px 14px 0 0;
}

.hd-modal-header h5 {
    margin: 0;
    font-size: 0.92rem;
    font-weight: 700;
    color: #1a2a4a;
    display: flex;
    align-items: center;
    gap: 8px;
}

.hd-modal-header h5 i { color: #E87722; }

.hd-modal-close {
    width: 28px; height: 28px;
    border-radius: 6px;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    font-size: 1rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.15s;
}
.hd-modal-close:hover { background: #fef2f2; color: #dc2626; }

.hd-modal-body { padding: 22px; }

.hd-modal-footer {
    padding: 14px 22px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    position: sticky;
    bottom: 0;
    background: #fff;
    border-radius: 0 0 14px 14px;
}

/* Sección de campo con label arriba */
.hd-field { margin-bottom: 14px; }
.hd-field label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #1a2a4a;
    margin-bottom: 5px;
    letter-spacing: 0.2px;
}
.hd-field label span { color: #64748b; font-weight: 400; }

.hd-field .hd-input,
.hd-field .hd-select-input {
    width: 100%;
    padding: 8px 12px;
    border: 1.5px solid #e2e8f0;
    border-radius: 7px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.82rem;
    color: #1e293b;
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
}
.hd-field .hd-input:focus,
.hd-field .hd-select-input:focus {
    border-color: #E87722;
    box-shadow: 0 0 0 3px rgba(232,119,34,0.1);
}
.hd-field .hd-input[readonly] { background: #f8fafc; color: #94a3b8; }

.hd-field .pass-wrap { position: relative; }
.hd-field .pass-wrap .hd-input { padding-right: 38px; }
.hd-field .pass-wrap i {
    position: absolute; right: 11px; top: 50%;
    transform: translateY(-50%);
    cursor: pointer; color: #94a3b8; font-size: 0.85rem;
    transition: color 0.15s;
}
.hd-field .pass-wrap i:hover { color: #E87722; }

.hd-modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.hd-modal-section {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #94a3b8;
    margin: 16px 0 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid #f1f5f9;
}
.hd-modal-section:first-child { margin-top: 0; }
    </style>
</head>
<body>

<!-- ===== MODAL CREAR ===== -->
<div class="hd-modal-overlay" id="modal-crear">
    <div class="hd-modal">
        <div class="hd-modal-header">
            <h5><i class="fas fa-user-plus"></i> Nuevo Usuario</h5>
            <button class="hd-modal-close" onclick="cerrarModal('modal-crear')"><i class="fas fa-times"></i></button>
        </div>
        <div class="hd-modal-body">
            <form id="form-crear">

                <div class="hd-modal-section">Información Personal</div>
                <div class="hd-modal-grid">
                    <div class="hd-field">
                        <label>Nombres</label>
                        <input type="text" name="name" class="hd-input" placeholder="Ej. Juan" required>
                    </div>
                    <div class="hd-field">
                        <label>Apellidos</label>
                        <input type="text" name="lastname" class="hd-input" placeholder="Ej. Pérez" required>
                    </div>
                </div>
                <div class="hd-field">
                    <label>Teléfono <span>(opcional)</span></label>
                    <input type="text" name="phone_number" class="hd-input" placeholder="Ej. 04121234567">
                </div>

                <div class="hd-modal-section">Acceso al Sistema</div>
                <div class="hd-modal-grid">
                    <div class="hd-field">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" class="hd-input" placeholder="juan@unicon.com" required>
                    </div>
                    <div class="hd-field">
                        <label>Usuario de Login</label>
                        <input type="text" name="user" class="hd-input" placeholder="Ej. jperez" required>
                    </div>
                </div>
                <div class="hd-modal-grid">
                    <div class="hd-field">
                        <label>Contraseña</label>
                        <div class="pass-wrap">
                            <input type="password" id="pass-crear" name="password" class="hd-input" placeholder="Mín. 12 caracteres" required>
                            <i class="fas fa-eye" onclick="togglePass('pass-crear', this)"></i>
                        </div>
                    </div>
                    <div class="hd-field">
                        <label>Confirmar Contraseña</label>
                        <div class="pass-wrap">
                            <input type="password" id="pass-crear-confirm" name="confirm_password" class="hd-input" placeholder="Repite la contraseña" required>
                            <i class="fas fa-eye" onclick="togglePass('pass-crear-confirm', this)"></i>
                        </div>
                    </div>
                </div>

                <div class="hd-modal-section">Rol y Departamento</div>
                <div class="hd-modal-grid">
                    <div class="hd-field">
                        <label>Departamento</label>
                        <select name="dpto" class="hd-input hd-select" required>
                            <option value="" disabled selected>Selecciona</option>
                            <?php
                            $deptos = $conexion->query("SELECT id_departamento, nombre FROM departamentos WHERE estado = 'activo'");
                            while ($d = $deptos->fetch_assoc()):
                            ?>
                            <option value="<?= $d['id_departamento'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="hd-field">
                        <label>Rol de Acceso</label>
                        <select name="role" class="hd-input hd-select" required>
                            <option value="" disabled selected>Selecciona</option>
                            <?php
                            $roles = $conexion->query("SELECT id_rol, nombre FROM roles");
                            while ($r = $roles->fetch_assoc()):
                            ?>
                            <option value="<?= $r['id_rol'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="hd-field">
                    <label>Estado Inicial</label>
                    <select name="state" class="hd-input hd-select">
                        <option value="activo" selected>Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="reposo">Reposo</option>
                        <option value="pasante">Pasante</option>
                    </select>
                </div>

                <div id="resultado-crear" style="margin-top:8px;"></div>
            </form>
        </div>
        <div class="hd-modal-footer">
            <button type="button" onclick="cerrarModal('modal-crear')" class="hd-btn hd-btn-outline">Cancelar</button>
            <button type="submit" form="form-crear" id="btn-crear" class="hd-btn hd-btn-naranja">
                <i class="fas fa-plus"></i> Crear Usuario
            </button>
        </div>
    </div>
</div>

<!-- ===== MODAL EDITAR ===== -->
<div class="hd-modal-overlay" id="modal-editar">
    <div class="hd-modal">
        <div class="hd-modal-header">
            <h5><i class="fas fa-user-edit"></i> Editar Usuario</h5>
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
            <button onclick="abrirModalCrear()" class="hd-btn hd-btn-naranja hd-btn-sm">
                <i class="fas fa-plus"></i> Nuevo Usuario
            </button>
        </div>
    </div>

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
                    <tr id="usuario-row-<?= $row['id_usuario'] ?>">
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
                                <button onclick="abrirModalEditar(<?= $row['id_usuario'] ?>)" class="hd-btn hd-btn-outline hd-btn-sm">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" 
                                        class="hd-btn hd-btn-sm btn-eliminar" 
                                        data-id="<?= $row['id_usuario'] ?>" 
                                        style="background:#fef2f2;color:var(--danger);border:1px solid #fecaca;"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function abrirModalCrear() {
    document.getElementById('modal-crear').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function abrirModalEditar(id) {
    const overlay = document.getElementById('modal-editar');
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
    document.getElementById('modal-editar-contenido').innerHTML =
        '<div style="text-align:center;padding:40px;color:#94a3b8;"><i class="fas fa-spinner fa-spin" style="font-size:1.8rem;"></i><p style="margin-top:10px;font-size:0.82rem;">Cargando datos...</p></div>';

    $.ajax({
        url: 'get_usuario.php',
        type: 'GET',
        data: { id: id },
        success: function(html) {
            document.getElementById('modal-editar-contenido').innerHTML = html;
        },
        error: function() {
            document.getElementById('modal-editar-contenido').innerHTML =
                '<div class="alert alert-danger">Error al cargar los datos.</div>';
        }
    });
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = '';
}

// Cerrar al hacer clic fuera
document.addEventListener('click', function(e) {
    ['modal-crear','modal-editar'].forEach(function(id) {
        const modal = document.getElementById(id);
        if (e.target === modal) cerrarModal(id);
    });
});

// Cerrar con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModal('modal-crear');
        cerrarModal('modal-editar');
    }
});

// ==========================================
// NUEVO: Lógica para que el botón Eliminar funcione
// ==========================================
$(document).on('click', '.btn-eliminar', function() {
    const idUsuario = $(this).data('id');
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E87722', // Color naranja de tu UI
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'eliminar_usuario.php', // Ajusta la ruta a tu backend de eliminación
                type: 'POST',
                data: { id: idUsuario },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire(
                            '¡Eliminado!',
                            'El usuario ha sido eliminado correctamente.',
                            'success'
                        );
                        // Elimina la fila de la tabla visualmente sin recargar
                        $(`#usuario-row-${idUsuario}`).fadeOut(400, function() {
                            $(this).remove();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo eliminar el usuario.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Hubo un problema de red o del servidor.', 'error');
                }
            });
        }
    });
});
</script>
</body>
</html>
<?php
// tecnico/inventario.php — Inventario tecnológico en SOLO LECTURA para el técnico.
// (Crear/editar/eliminar equipos sigue siendo exclusivo del administrador.)
session_start();
if (!isset($_SESSION['usser']) || (int)($_SESSION['id_rol'] ?? 0) !== 2) {
    header("Location: ../index.php"); exit();
}
require_once '../includes/constants.php';
if (!isset($conexion)) require_once __DIR__ . '/../config/conexion.php';
require_once '../includes/permisos.php';
if (!rol_tiene_permiso($conexion, ROL_TECNICO, 'inventario.ver')) { header('Location: dashboard.php'); exit(); }

$inventario = $conexion->query("
    SELECT i.codigo_equipo, i.tipo_equipo, i.marca, i.modelo, i.serial, i.estado, i.ubicacion,
           d.nombre AS departamento_nombre
    FROM inventario_tecnologico i
    LEFT JOIN departamentos d ON i.id_departamento = d.id_departamento
    ORDER BY i.codigo_equipo ASC
");

include_once '../includes/header2.php';
include_once '../includes/sidebar.php';
?>

<style>
    .hd-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; }
    .hd-badge.activo        { background: rgba(22,163,74,0.12);  color: var(--success); }
    .hd-badge.inactivo      { background: rgba(100,116,139,0.12); color: var(--muted); }
    .hd-badge.mantenimiento { background: rgba(217,119,6,0.12);  color: #d97706; }
    .hd-filtros { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; align-items: center; }
    .hd-filtro-btn {
        padding: 6px 14px; border-radius: 20px; border: 1.5px solid var(--borde);
        background: #fff; font-size: 0.76rem; font-weight: 500; color: var(--muted);
        cursor: pointer; transition: all 0.15s;
    }
    .hd-filtro-btn.activo { border-color: var(--naranja); color: var(--naranja); background: rgba(232,119,34,0.06); }
    .hd-buscar { margin-left: auto; padding: 6px 12px; border: 1.5px solid var(--borde); border-radius: 20px; font-size: 0.78rem; min-width: 220px; }
    .hd-buscar:focus { border-color: var(--naranja); outline: none; }
</style>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <div class="hd-page-title"><i class="fas fa-server" style="color:var(--naranja);margin-right:6px;"></i> Inventario Tecnológico</div>
            <div class="hd-page-sub">Equipos de la empresa — consulta (solo lectura)</div>
        </div>
    </div>

    <div class="hd-filtros">
        <button class="hd-filtro-btn activo" data-filtro="todos">Todos</button>
        <button class="hd-filtro-btn" data-filtro="activo">Activos</button>
        <button class="hd-filtro-btn" data-filtro="mantenimiento">En Mantenimiento</button>
        <button class="hd-filtro-btn" data-filtro="inactivo">Inactivos</button>
        <input type="search" id="buscar" class="hd-buscar" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Buscar código, marca, serial, ubicación…">
    </div>

    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-list"></i> Listado de Equipos</h5>
        </div>
        <div class="hd-card-body" style="padding:0;">
            <table class="hd-table">
                <thead>
                    <tr>
                        <th>Código</th><th>Tipo</th><th>Marca / Modelo</th><th>Serial</th>
                        <th>Departamento</th><th>Ubicación</th><th>Estado</th><th></th>
                    </tr>
                </thead>
                <tbody id="tbody-inventario">
                    <?php if ($inventario->num_rows === 0): ?>
                    <tr><td colspan="8" style="text-align:center;padding:20px;color:var(--muted);">Aún no hay equipos registrados.</td></tr>
                    <?php endif; ?>
                    <?php while ($eq = $inventario->fetch_assoc()): ?>
                    <tr data-estado="<?= htmlspecialchars($eq['estado']) ?>">
                        <td style="font-weight:600;color:var(--azul);"><?= htmlspecialchars($eq['codigo_equipo']) ?></td>
                        <td><?= htmlspecialchars($eq['tipo_equipo']) ?></td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($eq['marca']) ?> <?= htmlspecialchars($eq['modelo']) ?></td>
                        <td style="color:var(--muted);font-family:monospace;font-size:0.78rem;"><?= htmlspecialchars($eq['serial']) ?></td>
                        <td><?= htmlspecialchars($eq['departamento_nombre'] ?? '—') ?></td>
                        <td style="color:var(--muted);"><?= htmlspecialchars($eq['ubicacion'] ?? '—') ?></td>
                        <td><span class="hd-badge <?= htmlspecialchars($eq['estado']) ?>"><?= ucfirst(htmlspecialchars($eq['estado'])) ?></span></td>
                        <td><a href="inventario_historial.php?id=<?= (int)$eq['id_inventario'] ?>" class="hd-btn hd-btn-outline hd-btn-sm" title="Historial de mantenimiento"><i class="fas fa-tools"></i></a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
let filtroEstado = 'todos';
function aplicar() {
    const q = document.getElementById('buscar').value.trim().toLowerCase();
    document.querySelectorAll('#tbody-inventario tr[data-estado]').forEach(fila => {
        const okEstado = filtroEstado === 'todos' || fila.dataset.estado === filtroEstado;
        const okTexto  = q === '' || fila.textContent.toLowerCase().includes(q);
        fila.style.display = (okEstado && okTexto) ? '' : 'none';
    });
}
document.querySelectorAll('.hd-filtro-btn').forEach(btn => btn.addEventListener('click', () => {
    document.querySelectorAll('.hd-filtro-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    filtroEstado = btn.dataset.filtro;
    aplicar();
}));
document.getElementById('buscar').addEventListener('input', aplicar);
aplicar();   // aplica el ?q= que llega del buscador de la barra superior
</script>

<?php include_once '../includes/footer2.php'; ?>

<?php
/**
 * includes/ticket_form.php
 * Formulario "Crear Ticket" para administrador y técnico (el usuario tiene su propio ticket_nuevo.php).
 * Se incluye DESPUÉS de header2.php + sidebar.php y ANTES de footer2.php.
 * El ticket se crea a nombre de quien está en sesión y entra a la cola "sin asignar":
 * es el administrador quien decide qué técnico lo atiende.
 */
if (!defined('NOMBRE_SISTEMA')) require_once '../includes/constants.php';

// Categorías activas, con la prioridad que se asignará automáticamente
$categorias = $conexion->query("
    SELECT c.id_categoria, c.nombre, p.nombre AS prioridad_nombre, p.nivel AS prioridad_nivel
    FROM categoria_incidencias c
    LEFT JOIN prioridades p ON c.id_prioridad_default = p.id_prioridad
    WHERE c.estado = 'activo'
    ORDER BY c.nombre ASC
");

// Equipos activos del departamento de quien crea el ticket
$stmt_inv = $conexion->prepare("
    SELECT i.id_inventario, i.codigo_equipo, i.tipo_equipo, i.marca, i.modelo
    FROM inventario_tecnologico i
    INNER JOIN usuarios u ON i.id_departamento = u.id_departamento
    WHERE u.usuario_login = ? AND i.estado = 'activo'
");
$stmt_inv->bind_param("s", $usuario_activo);
$stmt_inv->execute();
$inventario = $stmt_inv->get_result();
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<style>
    .hd-form-wrap { max-width: 820px; }
    .hd-form-group { margin-bottom: 16px; }
    .hd-label { font-size: 0.78rem; font-weight: 600; color: var(--azul); margin-bottom: 5px; display: block; }
    .hd-label span { color: var(--muted); font-weight: 400; }
    .hd-input {
        width: 100%; padding: 9px 12px; border: 1.5px solid var(--borde); border-radius: 7px;
        font-family: 'Poppins', sans-serif; font-size: 0.83rem; color: var(--texto); background: #fff;
        transition: border-color 0.2s, box-shadow 0.2s; outline: none;
    }
    .hd-input:focus { border-color: var(--naranja); box-shadow: 0 0 0 3px rgba(232,119,34,0.1); }
    .hd-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px;
    }
    .hd-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 700px) { .hd-form-row { grid-template-columns: 1fr; } }
    .hd-section-label {
        font-size: 0.68rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
        color: var(--muted); margin: 20px 0 12px; padding-bottom: 6px; border-bottom: 1px solid var(--borde);
    }
    .hd-section-label:first-child { margin-top: 0; }
    .prioridad-auto {
        display: flex; align-items: center; gap: 8px; padding: 9px 14px; border-radius: 7px;
        border: 1.5px dashed var(--borde); font-size: 0.8rem; font-weight: 500; color: var(--muted);
        background: #f8fafc; min-height: 38px;
    }
    .prioridad-auto .dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; background: #cbd5e1; }
    .prioridad-auto.activa { border-style: solid; }
    .inventario-toggle {
        display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px;
        background: #f8fafc; border: 1.5px solid var(--borde); cursor: pointer; font-size: 0.82rem;
        color: var(--muted); transition: all 0.2s; margin-bottom: 10px;
    }
    .inventario-toggle:hover { border-color: var(--naranja); color: var(--naranja); }
    .inventario-toggle input { accent-color: var(--naranja); width: 16px; height: 16px; }
    .char-counter { font-size: 0.68rem; color: var(--muted); text-align: right; margin-top: 3px; }
    .char-counter.warn { color: var(--warning); }
    .char-counter.danger { color: var(--danger); }
    textarea.hd-input { resize: vertical; min-height: 100px; }
</style>

<main class="hd-main">
<div class="hd-form-wrap">

    <div class="hd-page-header" style="margin-bottom:20px;">
        <div>
            <div class="hd-page-title">
                <i class="fas fa-plus-circle" style="color:var(--naranja);margin-right:6px;"></i> Crear Ticket
            </div>
            <div class="hd-page-sub">Describe el problema con el mayor detalle posible</div>
        </div>
        <a href="mis_tickets.php" class="hd-btn hd-btn-outline hd-btn-sm">
            <i class="fas fa-list"></i> Mis Solicitudes
        </a>
    </div>

    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-ticket-alt"></i> Información del Ticket</h5>
        </div>
        <div class="hd-card-body" style="padding:22px;">
            <form id="form-ticket">

                <div class="hd-section-label">Descripción del Problema</div>

                <div class="hd-form-group">
                    <label class="hd-label">Asunto <span>*</span></label>
                    <input type="text" name="asunto" id="asunto" class="hd-input"
                           placeholder="Ej. PC no enciende en el área de contabilidad" maxlength="200" required>
                    <div class="char-counter"><span id="cnt-asunto">0</span>/200</div>
                </div>

                <div class="hd-form-group">
                    <label class="hd-label">Descripción detallada <span>*</span></label>
                    <textarea name="descripcion" id="descripcion" class="hd-input"
                              placeholder="Describe el problema con detalle: ¿qué pasó?, ¿cuándo empezó?, ¿qué intentaste hacer?"
                              maxlength="2000" required></textarea>
                    <div class="char-counter"><span id="cnt-desc">0</span>/2000</div>
                </div>

                <div class="hd-section-label">Clasificación</div>

                <div class="hd-form-row">
                    <div class="hd-form-group">
                        <label class="hd-label">Categoría <span>*</span></label>
                        <select name="id_categoria" id="select-categoria" class="hd-input hd-select" required onchange="actualizarPrioridad(this)">
                            <option value="" disabled selected>Selecciona una categoría</option>
                            <?php while ($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?= $cat['id_categoria'] ?>"
                                data-prioridad-nombre="<?= htmlspecialchars($cat['prioridad_nombre'] ?? '') ?>"
                                data-prioridad-nivel="<?= (int)($cat['prioridad_nivel'] ?? 0) ?>">
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="hd-form-group">
                        <label class="hd-label">Prioridad</label>
                        <div class="prioridad-auto" id="prioridad-auto">
                            <span class="dot" id="prioridad-dot"></span>
                            <span id="prioridad-texto">Se asigna según la categoría</span>
                        </div>
                        <div style="font-size:0.68rem;color:var(--muted);margin-top:3px;">
                            Se asigna automáticamente según la categoría.
                        </div>
                    </div>
                </div>

                <div class="hd-section-label">Equipo Relacionado <span style="font-weight:400;text-transform:none;letter-spacing:0;">(opcional)</span></div>

                <label class="inventario-toggle">
                    <input type="checkbox" id="toggle-inventario" onchange="toggleInventario(this)">
                    <i class="fas fa-server"></i>
                    <span>¿El problema está relacionado con un equipo del inventario?</span>
                </label>

                <div id="inventario-wrap" style="display:none;">
                    <div class="hd-form-group">
                        <label class="hd-label">Selecciona el equipo</label>
                        <select name="id_inventario" id="select-inventario" class="hd-input hd-select">
                            <option value="">— Sin equipo específico —</option>
                            <?php while ($eq = $inventario->fetch_assoc()): ?>
                            <option value="<?= $eq['id_inventario'] ?>">
                                [<?= htmlspecialchars($eq['codigo_equipo']) ?>]
                                <?= htmlspecialchars($eq['tipo_equipo']) ?> —
                                <?= htmlspecialchars($eq['marca']) ?>
                                <?= htmlspecialchars($eq['modelo']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <?php include __DIR__ . '/adjuntos_widget.php'; ?>

                <div id="resultado" style="margin-top:8px;"></div>
            </form>
        </div>
        <div style="padding:14px 22px;border-top:1px solid var(--borde);display:flex;gap:8px;justify-content:flex-end;background:#fafbfc;border-radius:0 0 8px 8px;">
            <a href="dashboard.php" class="hd-btn hd-btn-outline">Cancelar</a>
            <button type="submit" form="form-ticket" id="btn-enviar" class="hd-btn hd-btn-naranja">
                <i class="fas fa-paper-plane"></i> Enviar Ticket
            </button>
        </div>
    </div>

    <div class="hd-card" style="margin-top:14px;">
        <div class="hd-card-body" style="padding:14px 18px;">
            <div style="display:flex;gap:10px;align-items:flex-start;">
                <div class="hd-stat-icon azul" style="width:32px;height:32px;font-size:0.8rem;flex-shrink:0;"><i class="fas fa-user-cog"></i></div>
                <div>
                    <div style="font-size:0.78rem;font-weight:600;color:var(--azul);">¿Quién lo atiende?</div>
                    <div style="font-size:0.72rem;color:var(--muted);line-height:1.4;">El ticket queda registrado a tu nombre y un administrador lo asigna a un técnico.</div>
                </div>
            </div>
        </div>
    </div>

</div>
</main>

<script>
function initCounter(inputId, counterId, max) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    input.addEventListener('input', function() {
        const len = this.value.length;
        counter.textContent = len;
        const parent = counter.closest('.char-counter');
        parent.classList.remove('warn', 'danger');
        if (len > max * 0.9) parent.classList.add('danger');
        else if (len > max * 0.7) parent.classList.add('warn');
    });
}
initCounter('asunto', 'cnt-asunto', 200);
initCounter('descripcion', 'cnt-desc', 2000);

// Muestra qué prioridad recibirá el ticket según la categoría elegida
const coloresNivel = { 1: '#0284c7', 2: '#d97706', 3: '#E87722', 4: '#dc2626' };
function actualizarPrioridad(select) {
    const opt = select.options[select.selectedIndex];
    const nombre = opt.dataset.prioridadNombre;
    const nivel = parseInt(opt.dataset.prioridadNivel) || 0;
    const wrap = document.getElementById('prioridad-auto');
    const dot = document.getElementById('prioridad-dot');
    const texto = document.getElementById('prioridad-texto');

    if (nombre) {
        const color = coloresNivel[nivel] || '#94a3b8';
        dot.style.background = color;
        texto.textContent = nombre;
        texto.style.color = color;
        wrap.classList.add('activa');
        wrap.style.borderColor = color;
    } else {
        dot.style.background = '#cbd5e1';
        texto.textContent = 'Esta categoría no tiene prioridad configurada — se usará Media';
        texto.style.color = 'var(--muted)';
        wrap.classList.remove('activa');
        wrap.style.borderColor = '';
    }
}

function toggleInventario(cb) {
    document.getElementById('inventario-wrap').style.display = cb.checked ? 'block' : 'none';
    if (!cb.checked) document.getElementById('select-inventario').value = '';
}

$('#form-ticket').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btn-enviar');
    const resultado = $('#resultado');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

    $.ajax({
        url: '../procesos/create_ticket.php',
        type: 'POST',
        data: new FormData(this),      // FormData (no serialize) para que viajen los archivos
        processData: false,
        contentType: false,
        success: function(r) {
            resultado.html(r);
            if (r.includes('alert-success')) {
                setTimeout(function() { window.location.href = 'mis_tickets.php'; }, 1800);
            } else {
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar Ticket');
            }
        },
        error: function() {
            resultado.html('<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Error de conexión. Intenta de nuevo.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar Ticket');
        }
    });
});
</script>

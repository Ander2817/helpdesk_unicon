<?php
session_start();
if (!isset($_SESSION['usser']) || !isset($_SESSION['id_rol'])) {
    header("Location: ../index.php"); exit();
}
include('../config/conexion.php');

// Datos para los selects
$categorias = $conexion->query("SELECT id_categoria, nombre FROM categoria_incidencias WHERE estado = 'activo'");
$prioridades = $conexion->query("SELECT id_prioridad, nombre, nivel FROM prioridades ORDER BY nivel ASC");
$inventario  = $conexion->query("
    SELECT i.id_inventario, i.codigo_equipo, i.tipo_equipo, i.marca, i.modelo 
    FROM inventario_tecnologico i
    INNER JOIN usuarios u ON i.id_departamento = u.id_departamento
    WHERE u.usuario_login = '{$_SESSION['usser']}' AND i.estado = 'activo'
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Ticket — HelpDesk Unicon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard2.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .page-wrapper { max-width: 820px; margin: 0 auto; padding: 24px 20px; }
        .hd-form-group { margin-bottom: 16px; }
        .hd-label {
            font-size: 0.78rem; font-weight: 600;
            color: var(--azul); margin-bottom: 5px; display: block;
        }
        .hd-label span { color: var(--muted); font-weight: 400; }
        .hd-input {
            width: 100%; padding: 9px 12px;
            border: 1.5px solid var(--borde); border-radius: 7px;
            font-family: 'Poppins', sans-serif; font-size: 0.83rem;
            color: var(--texto); background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .hd-input:focus { border-color: var(--naranja); box-shadow: 0 0 0 3px rgba(232,119,34,0.1); }
        .hd-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }
        .hd-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .hd-section-label {
            font-size: 0.68rem; font-weight: 700; letter-spacing: 1.5px;
            text-transform: uppercase; color: var(--muted);
            margin: 20px 0 12px; padding-bottom: 6px;
            border-bottom: 1px solid var(--borde);
        }
        .hd-section-label:first-child { margin-top: 0; }

        /* Prioridad visual */
        .prioridad-options { display: flex; gap: 8px; flex-wrap: wrap; }
        .prioridad-opt input[type="radio"] { display: none; }
        .prioridad-opt label {
            display: flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 7px;
            border: 1.5px solid var(--borde);
            font-size: 0.78rem; font-weight: 500;
            cursor: pointer; transition: all 0.15s;
            color: var(--muted);
        }
        .prioridad-opt input:checked + label {
            border-color: currentColor;
            font-weight: 600;
        }
        .prioridad-opt.baja   label { --c: #0284c7; }
        .prioridad-opt.media  label { --c: #d97706; }
        .prioridad-opt.alta   label { --c: #E87722; }
        .prioridad-opt.critica label { --c: #dc2626; }
        .prioridad-opt input:checked + label { color: var(--c); background: color-mix(in srgb, var(--c) 8%, white); border-color: var(--c); }

        /* Inventario toggle */
        .inventario-toggle {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: 8px;
            background: #f8fafc; border: 1.5px solid var(--borde);
            cursor: pointer; font-size: 0.82rem; color: var(--muted);
            transition: all 0.2s; margin-bottom: 10px;
        }
        .inventario-toggle:hover { border-color: var(--naranja); color: var(--naranja); }
        .inventario-toggle input { accent-color: var(--naranja); width: 16px; height: 16px; }

        /* Char counter */
        .char-counter { font-size: 0.68rem; color: var(--muted); text-align: right; margin-top: 3px; }
        .char-counter.warn { color: var(--warning); }
        .char-counter.danger { color: var(--danger); }

        textarea.hd-input { resize: vertical; min-height: 100px; }
    </style>
</head>
<body>

<div class="page-wrapper">

    <!-- HEADER -->
    <div class="hd-page-header" style="margin-bottom:20px;">
        <div>
            <div class="hd-page-title">
                <i class="fas fa-plus-circle" style="color:var(--naranja);margin-right:6px;"></i>
                Crear Nuevo Ticket
            </div>
            <div class="hd-page-sub">Describe el problema con el mayor detalle posible</div>
        </div>
        <a href="dashboard.php" class="hd-btn hd-btn-outline hd-btn-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="hd-card">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-ticket-alt"></i> Información del Ticket</h5>
        </div>
        <div class="hd-card-body" style="padding:22px;">
            <form id="form-ticket">

                <!-- SECCIÓN 1: DESCRIPCIÓN -->
                <div class="hd-section-label">Descripción del Problema</div>

                <div class="hd-form-group">
                    <label class="hd-label">Asunto <span>*</span></label>
                    <input type="text" name="asunto" id="asunto" class="hd-input"
                           placeholder="Ej. PC no enciende en el área de contabilidad"
                           maxlength="200" required>
                    <div class="char-counter"><span id="cnt-asunto">0</span>/200</div>
                </div>

                <div class="hd-form-group">
                    <label class="hd-label">Descripción detallada <span>*</span></label>
                    <textarea name="descripcion" id="descripcion" class="hd-input"
                              placeholder="Describe el problema con detalle: ¿qué pasó?, ¿cuándo empezó?, ¿qué intentaste hacer?"
                              maxlength="2000" required></textarea>
                    <div class="char-counter"><span id="cnt-desc">0</span>/2000</div>
                </div>

                <!-- SECCIÓN 2: CLASIFICACIÓN -->
                <div class="hd-section-label">Clasificación</div>

                <div class="hd-form-row">
                    <div class="hd-form-group">
                        <label class="hd-label">Categoría <span>*</span></label>
                        <select name="id_categoria" class="hd-input hd-select" required>
                            <option value="" disabled selected>Selecciona una categoría</option>
                            <?php while ($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?= $cat['id_categoria'] ?>">
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="hd-form-group">
                        <label class="hd-label">Prioridad <span>*</span></label>
                        <div class="prioridad-options" id="prioridad-wrap">
                            <?php
                            $prioridades->data_seek(0);
                            $clases = ['baja','media','alta','critica'];
                            $iconos = ['fas fa-arrow-down','fas fa-minus','fas fa-arrow-up','fas fa-fire'];
                            $i = 0;
                            while ($p = $prioridades->fetch_assoc()):
                                $cls = $clases[$i] ?? 'media';
                                $ico = $iconos[$i] ?? 'fas fa-minus';
                                $checked = $p['nivel'] == 2 ? 'checked' : '';
                            ?>
                            <div class="prioridad-opt <?= $cls ?>">
                                <input type="radio" name="id_prioridad"
                                       id="prio-<?= $p['id_prioridad'] ?>"
                                       value="<?= $p['id_prioridad'] ?>" <?= $checked ?>>
                                <label for="prio-<?= $p['id_prioridad'] ?>">
                                    <i class="<?= $ico ?>"></i>
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </label>
                            </div>
                            <?php $i++; endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: EQUIPO (OPCIONAL) -->
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

                <!-- RESULTADO -->
                <div id="resultado" style="margin-top:8px;"></div>

            </form>
        </div>
        <div style="padding:14px 22px;border-top:1px solid var(--borde);display:flex;gap:8px;justify-content:flex-end;background:#fafbfc;border-radius:0 0 8px 8px;">
            <a href="../dashboard.php" class="hd-btn hd-btn-outline">Cancelar</a>
            <button type="submit" form="form-ticket" id="btn-enviar" class="hd-btn hd-btn-naranja">
                <i class="fas fa-paper-plane"></i> Enviar Ticket
            </button>
        </div>
    </div>

    <!-- INFO CARD -->
    <div class="hd-card" style="margin-top:14px;">
        <div class="hd-card-body" style="padding:14px 18px;">
            <div style="display:flex;gap:20px;flex-wrap:wrap;">
                <?php
                $tips = [
                    ['fas fa-clock','naranja','Tiempo de respuesta','Depende de la prioridad asignada. Crítica: 2h, Alta: 8h, Media: 24h, Baja: 48h.'],
                    ['fas fa-bell','azul','Notificaciones','Recibirás actualizaciones cuando el estado de tu ticket cambie.'],
                    ['fas fa-info-circle','verde','Tip','Mientras más detallada sea tu descripción, más rápido podremos resolver tu problema.'],
                ];
                foreach($tips as $t): ?>
                <div style="display:flex;gap:10px;align-items:flex-start;flex:1;min-width:200px;">
                    <div class="hd-stat-icon <?= $t[1] ?>" style="width:32px;height:32px;font-size:0.8rem;flex-shrink:0;">
                        <i class="<?= $t[0] ?>"></i>
                    </div>
                    <div>
                        <div style="font-size:0.78rem;font-weight:600;color:var(--azul);"><?= $t[2] ?></div>
                        <div style="font-size:0.72rem;color:var(--muted);line-height:1.4;"><?= $t[3] ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Contador de caracteres
function initCounter(inputId, counterId, max) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    input.addEventListener('input', function() {
        const len = this.value.length;
        counter.textContent = len;
        const parent = counter.closest('.char-counter');
        parent.classList.remove('warn','danger');
        if (len > max * 0.9) parent.classList.add('danger');
        else if (len > max * 0.7) parent.classList.add('warn');
    });
}
initCounter('asunto', 'cnt-asunto', 200);
initCounter('descripcion', 'cnt-desc', 2000);

function toggleInventario(cb) {
    const wrap = document.getElementById('inventario-wrap');
    const select = document.getElementById('select-inventario');
    wrap.style.display = cb.checked ? 'block' : 'none';
    if (!cb.checked) select.value = '';
}

// Submit con AJAX
$('#form-ticket').on('submit', function(e) {
    e.preventDefault();
    var btn = $('#btn-enviar');
    var resultado = $('#resultado');

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

    $.ajax({
        url: '../procesos/create_ticket.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(r) {
            resultado.html(r);
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar Ticket');
            if (r.includes('alert-success')) {
                setTimeout(function() {
                    window.location.href = '../mis_tickets.php';
                }, 2000);
            }
        },
        error: function() {
            resultado.html('<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Error de conexión. Intenta de nuevo.</div>');
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Enviar Ticket');
        }
    });
});
</script>
</body>
</html>

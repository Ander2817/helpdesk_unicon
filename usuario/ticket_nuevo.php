<?php
// Incluimos la lógica de sesión antes que nada
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usser'])) { header("Location: ../auth/login.php"); exit(); }

include_once '../includes/header.php';
?>

<main class="hd-main">

    <div class="hd-page-header">
        <div>
            <h2><i class="fas fa-plus-circle" style="color:var(--naranja);margin-right:8px;"></i>Nuevo Ticket</h2>
            <p>Describe el problema técnico que estás experimentando.</p>
        </div>
        <a href="dashboard.php" class="hd-btn hd-btn-outline">
            <i class="fas fa-arrow-left"></i> Volver al Panel
        </a>
    </div>

    <div class="hd-card" style="max-width: 800px; margin: 0 auto;">
        <div class="hd-card-header">
            <h5 class="hd-card-title"><i class="fas fa-edit"></i> Detalles de la Incidencia</h5>
        </div>
        <div class="hd-card-body">
            <form action="procesar_ticket.php" method="POST">
                
                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-weight:600; margin-bottom:5px;">Asunto del problema</label>
                    <input type="text" name="asunto" class="hd-input" placeholder="Ej: Falla en la conexión de red..." required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom:5px;">Categoría</label>
                        <select name="categoria" class="hd-input" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                            <option value="Hardware">Hardware</option>
                            <option value="Red e Internet">Red e Internet</option>
                            <option value="Software/SAP">Software / SAP</option>
                            <option value="Impresoras">Impresoras</option>
                            <option value="Correo">Correo Electrónico</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom:5px;">Prioridad</label>
                        <select name="prioridad" class="hd-input" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                            <option value="Baja">Baja</option>
                            <option value="Media">Media</option>
                            <option value="Alta">Alta</option>
                            <option value="Crítica">Crítica</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:600; margin-bottom:5px;">Descripción detallada</label>
                    <textarea name="descripcion" rows="6" class="hd-input" placeholder="Explica con detalle qué está ocurriendo..." required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;"></textarea>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="hd-btn hd-btn-naranja">
                        <i class="fas fa-paper-plane"></i> Enviar Ticket
                    </button>
                </div>

            </form>
        </div>
    </div>

</main>

<?php include_once '../includes/footer.php'; ?>
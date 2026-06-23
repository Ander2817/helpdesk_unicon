<?php $year = date('Y'); ?>
<footer class="hd-footer">
    <div class="hd-footer-grid">
        <div>
            <div class="hd-footer-brand">
                <i class="fas fa-headset"></i>
                Help<span>Desk</span> Unicon
            </div>
            <p class="hd-footer-desc">Plataforma de gestión de incidencias del Dpto. de Tecnología de la Información.</p>
        </div>
        <div>
            <h6 class="hd-footer-title">NAVEGACIÓN</h6>
            <ul class="hd-footer-list">
                <li><a href="dashboard.php">Panel Principal</a></li>
                <li><a href="tickets.php">Tickets</a></li>
                <li><a href="perfil.php">Mi Perfil</a></li>
            </ul>
        </div>
        <div>
            <h6 class="hd-footer-title">EMPRESA</h6>
            <ul class="hd-footer-list">
                <li>Industrias Unicon C.A.</li>
                <li>ArcelorMittal</li>
                <li>Zona Industrial La Chapa</li>
                <li>La Victoria, Aragua</li>
            </ul>
        </div>
        <div>
            <h6 class="hd-footer-title">CONTACTO TI</h6>
            <ul class="hd-footer-list">
                <li><i class="fas fa-envelope"></i> soporte@unicon.com</li>
                <li><i class="fas fa-phone"></i> +58 (0243) 000-0000</li>
                <li><i class="fas fa-clock"></i> Lun–Vie: 8:00am – 5:00pm</li>
            </ul>
        </div>
    </div>
    <div class="hd-footer-bottom">
        <span>© <?= $year ?> <strong>Unicon.</strong> Todos los derechos reservados.</span>
        <span>HelpDesk v1.0 — Dpto. TI</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

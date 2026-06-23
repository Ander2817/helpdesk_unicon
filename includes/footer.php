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
            <h6 class="hd-footer-title">CONTACTO</h6>
            <ul class="hd-footer-list text-white list-unstyled p-0 m-0">
                <li class="mb-2 d-flex align-items-center">
                    <i class="fas fa-paper-plane me-2 naranja" style="width: 18px;"></i>
                    <a href="mailto:SoporteTecnicoSistemas@unicon.com.ve" class="text-decoration-none text-white" style="font-size: 0.9rem; word-break: break-all;">SoporteTecnicoSistemas@unicon.com.ve</a>
                </li>
                <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem;">
                    <i class="fas fa-headset me-2 naranja" style="width: 18px;"></i>
                    <span>+58 (244) 400.4800 &nbsp;Ext 2386</span>
                </li>
                <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem;">
                    <i class="fas fa-earth-americas me-2 naranja" style="width: 18px;"></i>
                    <a href="http://www.unicon.com.ve" target="_blank" class="text-decoration-none naranja fw-bold">www.unicon.com.ve</a>
                </li>
                <li class="mt-3 d-flex align-items-center text-white-50" style="font-size: 0.85rem;">
                    <i class="fas fa-business-time me-2 naranja" style="width: 18px;"></i>
                    <span>Lun - Vie: 8:00am - 4:30pm</span>
                </li>
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
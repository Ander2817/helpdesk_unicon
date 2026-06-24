<?php
// CORRECCIÓN en footer2.php — usar constantes en vez de texto hardcodeado
// Agrega el require al inicio del archivo si no está incluido ya
if (!defined('NOMBRE_SISTEMA')) require_once '../includes/constants.php';
?>
    <!-- FIN MAIN -->
    <footer class="hd-footer">
        <span>© <?= date('Y') ?> <strong><?= EMPRESA_NOMBRE ?></strong> — <?= NOMBRE_SISTEMA ?> v<?= VERSION_SISTEMA ?></span>
        <div class="hd-footer-links">
            <a href="dashboard.php">Panel</a>
            <a href="perfil.php">Mi Perfil</a>
            <span style="color:rgba(255,255,255,0.2);"><?= EMAIL_SOPORTE ?></span>
        </div>
    </footer>
</div><!-- fin hd-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSub(id) {
    const el = document.getElementById(id);
    if (el) el.classList.toggle('open');
}
function toggleUserMenu() {
    document.getElementById('userMenu').classList.toggle('show');
}
document.addEventListener('click', function(e) {
    const btn = document.getElementById('userBtn');
    const menu = document.getElementById('userMenu');
    if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('show');
    }
});
</script>
</body>
</html>

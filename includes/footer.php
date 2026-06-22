<?php
// footer.php
$anio_actual = date('Y');
?>

<footer class="footer-helpdesk mt-auto">
    <div class="container-fluid">
        <div class="footer-inner d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">

            <div class="footer-brand d-flex align-items-center gap-2">
                <i class="fas fa-headset footer-icon"></i>
                <span class="footer-brand-text">Help<span>Desk</span> Unicon</span>
            </div>


            <p class="footer-copy mb-0">
                &copy; <?= $anio_actual ?> Unicon &mdash; Todos los derechos reservados.
            </p>

            <span class="footer-version">v1.0.0</span>

        </div>
    </div>
</footer>

<style>
    .footer-helpdesk {
        background-color: #1a2a4a;
        border-top: 3px solid #E87722;
        padding: 0.85rem 1.25rem;
    }

    .footer-inner {
        min-height: 42px;
    }

    .footer-icon {
        color: #E87722;
        font-size: 1.1rem;
    }

    .footer-brand-text {
        font-size: 1rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: 0.5px;
    }

    .footer-brand-text span {
        color: #E87722;
    }

    .footer-copy {
        color: rgb(255, 255, 255);
        font-size: 0.8rem;
    }

    .footer-version {
        color: rgba(255, 255, 255, 0.35);
        font-size: 0.75rem;
        font-family: monospace;
    }
</style>
<?php
/**
 * includes/adjuntos_widget.php
 * Zona de adjuntos para los formularios de "crear ticket" (usuario, admin y técnico).
 * Se incluye DENTRO del <form>. No necesita jQuery. Los archivos viajan como adjuntos[]
 * junto con el resto del formulario (usar new FormData(form) al enviar).
 */
require_once __DIR__ . '/adjuntos.php';

$adj_limite   = adjuntos_limite_por_archivo();
$adj_max_n    = ADJUNTOS_MAX_POR_TICKET;
$adj_bloq_js  = json_encode(ADJUNTO_EXT_BLOQUEADAS);
?>
<style>
    .adj-zona {
        border: 2px dashed var(--borde); border-radius: 10px; padding: 18px; text-align: center;
        background: #f8fafc; cursor: pointer; transition: all 0.2s; color: var(--muted); font-size: 0.82rem;
    }
    .adj-zona:hover, .adj-zona.arrastrando { border-color: var(--naranja); background: rgba(232,119,34,0.05); color: var(--naranja); }
    .adj-zona i.adj-nube { font-size: 1.6rem; display: block; margin-bottom: 6px; }
    .adj-zona strong { color: var(--azul); }
    .adj-ayuda { font-size: 0.7rem; color: var(--muted); margin-top: 6px; line-height: 1.5; }
    .adj-lista { list-style: none; padding: 0; margin: 10px 0 0; display: flex; flex-direction: column; gap: 6px; }
    .adj-item {
        display: flex; align-items: center; gap: 10px; padding: 7px 10px;
        border: 1px solid var(--borde); border-radius: 8px; background: #fff; font-size: 0.8rem;
    }
    .adj-miniatura {
        width: 38px; height: 38px; border-radius: 6px; flex-shrink: 0; background: #f1f5f9;
        display: flex; align-items: center; justify-content: center; color: var(--muted); overflow: hidden;
    }
    .adj-miniatura img { width: 100%; height: 100%; object-fit: cover; }
    .adj-nombre { flex: 1; min-width: 0; color: var(--texto); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .adj-peso { color: var(--muted); font-size: 0.72rem; flex-shrink: 0; }
    .adj-quitar {
        border: none; background: none; color: var(--muted); cursor: pointer; padding: 4px 6px; border-radius: 6px;
    }
    .adj-quitar:hover { color: var(--danger); background: rgba(220,38,38,0.08); }
    .adj-errores { margin-top: 8px; font-size: 0.76rem; color: var(--danger); }
    .adj-errores div { padding: 2px 0; }
</style>

<div class="hd-section-label">Archivos adjuntos <span style="font-weight:400;text-transform:none;letter-spacing:0;">(opcional)</span></div>

<div class="adj-zona" id="adj-zona" tabindex="0">
    <i class="fas fa-cloud-upload-alt adj-nube"></i>
    <strong>Arrastra archivos aquí</strong>, haz clic para elegirlos
    <br>o pega una captura con <strong>Ctrl + V</strong>
</div>
<input type="file" name="adjuntos[]" id="adj-input" multiple hidden>

<div class="adj-ayuda">
    Hasta <?= $adj_max_n ?> archivos de <?= formato_bytes($adj_limite) ?> cada uno. Se aceptan imágenes, PDF, documentos de Office, texto, logs y comprimidos.
    No se permiten programas ni scripts (<?= adjuntos_extensiones_bloqueadas_texto() ?>).
</div>
<ul class="adj-lista" id="adj-lista"></ul>
<div class="adj-errores" id="adj-errores"></div>

<script>
(function () {
    const MAX_BYTES = <?= (int)$adj_limite ?>;
    const MAX_ARCHIVOS = <?= (int)$adj_max_n ?>;
    const BLOQUEADAS = <?= $adj_bloq_js ?>;

    const zona = document.getElementById('adj-zona');
    const input = document.getElementById('adj-input');
    const lista = document.getElementById('adj-lista');
    const cajaErrores = document.getElementById('adj-errores');
    let seleccion = new DataTransfer();   // archivos que realmente se enviarán
    let temporizadorErrores = null;

    function pesoLegible(b) {
        if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
        if (b >= 1024) return Math.round(b / 1024) + ' KB';
        return b + ' B';
    }
    function extension(nombre) {
        const i = nombre.lastIndexOf('.');
        return i >= 0 ? nombre.slice(i + 1).toLowerCase() : '';
    }
    function esImagen(f) { return f.type.startsWith('image/') && ['png','jpg','jpeg','gif','webp','bmp'].includes(extension(f.name)); }
    function iconoPara(ext) {
        if (['pdf'].includes(ext)) return 'fa-file-pdf';
        if (['doc','docx','rtf','odt'].includes(ext)) return 'fa-file-word';
        if (['xls','xlsx','csv','ods'].includes(ext)) return 'fa-file-excel';
        if (['zip','rar','7z','gz'].includes(ext)) return 'fa-file-archive';
        if (['txt','log','json','xml','ini','cfg'].includes(ext)) return 'fa-file-alt';
        return 'fa-file';
    }
    function mostrarErrores(mensajes) {
        cajaErrores.innerHTML = mensajes.map(m => '<div><i class="fas fa-exclamation-circle"></i> ' + m.replace(/</g, '&lt;') + '</div>').join('');
        clearTimeout(temporizadorErrores);
        temporizadorErrores = setTimeout(() => { cajaErrores.innerHTML = ''; }, 7000);
    }

    function agregar(archivos) {
        const problemas = [];
        for (const f of archivos) {
            const partes = f.name.toLowerCase().split('.').slice(1);
            if (seleccion.files.length >= MAX_ARCHIVOS) { problemas.push('Máximo ' + MAX_ARCHIVOS + ' archivos por ticket.'); break; }
            if (partes.some(p => BLOQUEADAS.includes(p))) { problemas.push('«' + f.name + '»: este tipo de archivo no se permite por seguridad.'); continue; }
            if (f.size === 0) { problemas.push('«' + f.name + '» está vacío.'); continue; }
            if (f.size > MAX_BYTES) { problemas.push('«' + f.name + '» pesa ' + pesoLegible(f.size) + ' y el máximo es ' + pesoLegible(MAX_BYTES) + '.'); continue; }
            const repetido = Array.from(seleccion.files).some(x => x.name === f.name && x.size === f.size);
            if (repetido) continue;
            seleccion.items.add(f);
        }
        sincronizar();
        if (problemas.length) mostrarErrores(problemas);
    }

    function sincronizar() {
        input.files = seleccion.files;   // lo que está en el input es lo que viaja con el formulario
        lista.innerHTML = '';
        Array.from(seleccion.files).forEach((f, i) => {
            const li = document.createElement('li');
            li.className = 'adj-item';

            const mini = document.createElement('div');
            mini.className = 'adj-miniatura';
            if (esImagen(f)) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(f);
                img.onload = () => URL.revokeObjectURL(img.src);
                mini.appendChild(img);
            } else {
                mini.innerHTML = '<i class="fas ' + iconoPara(extension(f.name)) + '"></i>';
            }

            const nombre = document.createElement('span');
            nombre.className = 'adj-nombre';
            nombre.textContent = f.name;
            nombre.title = f.name;

            const peso = document.createElement('span');
            peso.className = 'adj-peso';
            peso.textContent = pesoLegible(f.size);

            const quitar = document.createElement('button');
            quitar.type = 'button';
            quitar.className = 'adj-quitar';
            quitar.title = 'Quitar';
            quitar.innerHTML = '<i class="fas fa-times"></i>';
            quitar.addEventListener('click', () => quitarArchivo(i));

            li.append(mini, nombre, peso, quitar);
            lista.appendChild(li);
        });
    }

    function quitarArchivo(indice) {
        const nuevo = new DataTransfer();
        Array.from(seleccion.files).forEach((f, i) => { if (i !== indice) nuevo.items.add(f); });
        seleccion = nuevo;
        sincronizar();
    }

    // Elegir con el explorador de archivos
    zona.addEventListener('click', () => input.click());
    zona.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });
    input.addEventListener('change', () => {
        const elegidos = Array.from(input.files);   // el input solo trae lo recién elegido
        agregar(elegidos);
    });

    // Arrastrar y soltar
    ['dragenter', 'dragover'].forEach(ev => zona.addEventListener(ev, e => { e.preventDefault(); zona.classList.add('arrastrando'); }));
    ['dragleave', 'drop'].forEach(ev => zona.addEventListener(ev, e => { e.preventDefault(); zona.classList.remove('arrastrando'); }));
    zona.addEventListener('drop', e => agregar(Array.from(e.dataTransfer.files)));

    // Pegar capturas de pantalla con Ctrl+V desde cualquier parte de la página
    document.addEventListener('paste', e => {
        const archivos = Array.from((e.clipboardData && e.clipboardData.files) || []);
        if (!archivos.length) return;     // si es texto normal, no interferimos
        e.preventDefault();
        const marca = new Date().toISOString().replace(/[-:T]/g, '').slice(0, 14);
        agregar(archivos.map((f, i) => (f.name === 'image.png' || !f.name)
            ? new File([f], 'captura-' + marca + (archivos.length > 1 ? '-' + (i + 1) : '') + '.png', { type: f.type })
            : f));
    });
})();
</script>

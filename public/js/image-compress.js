/*
 * TAT Cycloid — Compresor de imágenes client-side
 *
 * Auto-engancha a TODOS los <input type="file" accept="image/*"> en la página.
 * Cuando el usuario selecciona una imagen:
 *   1) redimensiona a máx 1200 px (largo mayor)
 *   2) recompresa a JPEG calidad 0.75
 *   3) reemplaza el archivo original en el input
 *
 * Respeta archivos que no son imágenes (PDFs, etc.) — no los toca.
 * Retrocompatible: si el navegador no soporta DataTransfer, deja el archivo original.
 *
 * Incluir con:  <script src="<?= base_url('js/image-compress.js') ?>" defer></script>
 */
(function() {
    'use strict';

    const MAX_DIM      = 1200;
    const JPEG_QUALITY = 0.75;

    /**
     * Comprime una imagen File y devuelve un nuevo File en formato JPEG.
     */
    async function compressImage(file) {
        return new Promise((resolve) => {
            if (!file || !file.type || !file.type.startsWith('image/')) {
                return resolve(file); // no es imagen, devolver tal cual
            }
            // Si ya es muy pequeña (<300KB), no gastar CPU
            if (file.size < 300 * 1024) {
                return resolve(file);
            }

            const reader = new FileReader();
            reader.onerror = () => resolve(file);
            reader.onload = (e) => {
                const img = new Image();
                img.onerror = () => resolve(file);
                img.onload = () => {
                    let w = img.naturalWidth;
                    let h = img.naturalHeight;

                    // Escalar si excede MAX_DIM
                    if (w > MAX_DIM || h > MAX_DIM) {
                        const ratio = Math.min(MAX_DIM / w, MAX_DIM / h);
                        w = Math.round(w * ratio);
                        h = Math.round(h * ratio);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    // Fondo blanco (cuando el origen es PNG transparente, JPEG la vuelve negra si no)
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, w, h);
                    ctx.drawImage(img, 0, 0, w, h);

                    canvas.toBlob((blob) => {
                        if (!blob) return resolve(file);
                        const newName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                        const out = new File([blob], newName, {
                            type: 'image/jpeg',
                            lastModified: Date.now(),
                        });
                        // Si la salida no reduce, devolver la original
                        if (out.size >= file.size) resolve(file);
                        else resolve(out);
                    }, 'image/jpeg', JPEG_QUALITY);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    /**
     * Sustituye el archivo seleccionado en un <input type=file> por el comprimido.
     * Usa DataTransfer (disponible en todos los navegadores modernos).
     */
    async function handleChange(ev) {
        const input = ev.target;
        if (!input.files || !input.files[0]) return;

        const original = input.files[0];
        if (!original.type.startsWith('image/')) return; // PDFs u otros: no tocar

        const label = document.createElement('span');
        label.textContent = ' · Optimizando...';
        label.style.color = '#6c757d';
        label.style.fontSize = '0.85rem';
        input.parentNode && input.parentNode.appendChild(label);

        try {
            const compressed = await compressImage(original);
            if (compressed !== original) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(compressed);
                    input.files = dt.files;
                    label.textContent = ' · Optimizada ✓ (' +
                        (original.size/1024).toFixed(0) + 'KB → ' +
                        (compressed.size/1024).toFixed(0) + 'KB)';
                    label.style.color = '#198754';
                } catch (e) {
                    // Fallback silencioso: si DataTransfer no funciona, dejar archivo original
                    label.remove();
                }
            } else {
                label.remove();
            }
        } catch (e) {
            label.remove();
        }
    }

    /**
     * Engancha todos los inputs image en la página (y nuevos añadidos dinámicamente).
     */
    function attachToInputs(root) {
        const inputs = (root || document).querySelectorAll('input[type="file"][accept*="image"]');
        inputs.forEach((inp) => {
            if (inp.dataset._imgCompressed) return;
            inp.dataset._imgCompressed = '1';
            inp.addEventListener('change', handleChange);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => attachToInputs(document));
    } else {
        attachToInputs(document);
    }

    // Expuesto globalmente para enganchar inputs generados dinámicamente
    window.TAT_imageCompressAttach = attachToInputs;
})();

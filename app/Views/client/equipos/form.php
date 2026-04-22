<?php
ob_start();
$fechaHoraNow = date('d/m/Y H:i:s');
?>
<div class="page-header">
    <h1><i class="fas fa-tools me-2"></i> Nueva inspección equipos</h1>
    <a href="<?= base_url('client/equipos') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <div class="text-muted small">
            <i class="fas fa-clock me-1"></i> Registro en vivo (servidor): <strong id="fhLive"><?= $fechaHoraNow ?></strong>
        </div>
    </div>
</div>

<form id="formEquipos" action="<?= base_url('client/equipos/guardar') ?>" method="post" enctype="multipart/form-data">

    <?php foreach ($items as $it): ?>
        <div class="card mb-2">
            <div class="card-body py-3">
                <div class="d-flex align-items-center mb-2">
                    <?php if (!empty($it['icono'])): ?>
                        <i class="fas <?= esc($it['icono']) ?> me-2" style="color:#ee6c21; font-size:18px;"></i>
                    <?php endif; ?>
                    <strong><?= esc($it['nombre']) ?></strong>
                </div>
                <?php if (!empty($it['descripcion'])): ?>
                    <div class="small text-muted mb-2"><?= esc($it['descripcion']) ?></div>
                <?php endif; ?>

                <div class="btn-group w-100 mb-2" role="group">
                    <input type="radio" class="btn-check" name="estado[<?= (int)$it['id_item'] ?>]" id="e_<?= $it['id_item'] ?>_f" value="funcional" autocomplete="off">
                    <label class="btn btn-outline-success" for="e_<?= $it['id_item'] ?>_f">
                        <i class="fas fa-check"></i> Funcional
                    </label>

                    <input type="radio" class="btn-check" name="estado[<?= (int)$it['id_item'] ?>]" id="e_<?= $it['id_item'] ?>_d" value="defectuoso" autocomplete="off">
                    <label class="btn btn-outline-danger" for="e_<?= $it['id_item'] ?>_d">
                        <i class="fas fa-times"></i> Defectuoso
                    </label>

                    <input type="radio" class="btn-check" name="estado[<?= (int)$it['id_item'] ?>]" id="e_<?= $it['id_item'] ?>_na" value="no_aplica" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="e_<?= $it['id_item'] ?>_na">
                        <i class="fas fa-ban"></i> No aplica
                    </label>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="small mb-1 foto-label-<?= (int)$it['id_item'] ?>" style="color:#dc3545;">
                            <i class="fas fa-camera me-1"></i> Foto de evidencia <span class="text-danger fw-bold">*</span>
                        </label>
                        <input type="file" name="foto_<?= (int)$it['id_item'] ?>"
                               class="form-control form-control-sm"
                               accept="image/*" capture="environment"
                               data-item-id="<?= (int)$it['id_item'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="small mb-1">Observación breve <span class="text-muted">(opcional)</span></label>
                        <input type="text" name="observaciones[<?= (int)$it['id_item'] ?>]" class="form-control form-control-sm"
                               placeholder="Ej: cuchillas reemplazadas hoy">
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="card mb-3">
        <div class="card-body">
            <label class="form-label">Observaciones generales</label>
            <textarea name="observaciones_generales" rows="3" class="form-control"
                      placeholder="Notas generales, reparaciones pendientes, etc."></textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-lg w-100" style="background:#ee6c21;color:#fff;">
        <i class="fas fa-save me-1"></i> Registrar inspección
    </button>
</form>

<script>
// Reloj en vivo
(function() {
    var el = document.getElementById('fhLive');
    if (!el) return;
    function tick() {
        var d = new Date();
        var p = function(n){ return n < 10 ? '0' + n : n; };
        el.textContent = p(d.getDate()) + '/' + p(d.getMonth()+1) + '/' + d.getFullYear()
                       + ' ' + p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds());
    }
    setInterval(tick, 1000);
})();

// Validación pre-submit
document.getElementById('formEquipos').addEventListener('submit', function(e) {
    var radios = document.querySelectorAll('[name^="estado["]');
    var seenItems = {};
    radios.forEach(function(r) {
        var m = r.name.match(/estado\[(\d+)\]/);
        if (m && r.checked) seenItems[m[1]] = r.value;
    });

    var faltantes = [];
    Object.keys(seenItems).forEach(function(id) {
        if (seenItems[id] === 'no_aplica') return;
        var foto = document.querySelector('input[name="foto_' + id + '"]');
        if (!foto || !foto.files || !foto.files[0]) {
            var label = document.querySelector('.card:has([name="estado[' + id + ']"]) strong');
            faltantes.push(label ? label.textContent.trim() : ('Item #' + id));
        }
    });

    if (faltantes.length) {
        e.preventDefault();
        alert('📸 Foto obligatoria como evidencia.\n\nFalta en:\n' + faltantes.join('\n'));
    }
});

// Toggle label según estado
document.querySelectorAll('[name^="estado["]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        var m = this.name.match(/estado\[(\d+)\]/);
        if (!m) return;
        var id = m[1];
        var label = document.querySelector('.foto-label-' + id);
        if (!label) return;
        if (this.value === 'no_aplica') {
            label.style.color = '#6c757d';
            label.innerHTML = '<i class="fas fa-camera me-1"></i> Foto de evidencia <span class="text-muted">(no aplica)</span>';
        } else {
            label.style.color = '#dc3545';
            label.innerHTML = '<i class="fas fa-camera me-1"></i> Foto de evidencia <span class="text-danger fw-bold">*</span>';
        }
    });
});
</script>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => 'Nueva inspección equipos',
    'content' => $content,
]);

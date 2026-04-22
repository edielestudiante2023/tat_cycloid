<?php
ob_start();
$tipoLabel = \App\Models\NeveraModel::TIPOS[$nevera['tipo']] ?? $nevera['tipo'];
$fechaHoraNow = date('d/m/Y H:i:s');
?>
<div class="page-header">
    <h1><i class="fas fa-thermometer-half me-2"></i> Nueva lectura</h1>
    <a href="<?= base_url('client/neveras/' . $nevera['id_nevera'] . '/historico') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <h5 style="color:#c9541a;">
            <i class="fas fa-snowflake me-1"></i> <?= esc($nevera['nombre']) ?>
        </h5>
        <div class="text-muted small">
            <?= esc($tipoLabel) ?>
            &middot; Rango esperado: <strong><?= number_format((float)$nevera['rango_temp_min'],1) ?>°C a <?= number_format((float)$nevera['rango_temp_max'],1) ?>°C</strong>
            <?php if ((int)$nevera['controla_humedad'] === 1): ?>
                &middot; Humedad: <?= esc($nevera['rango_humedad_min'] ?? '-') ?>% a <?= esc($nevera['rango_humedad_max'] ?? '-') ?>%
            <?php endif; ?>
        </div>
    </div>
</div>

<form action="<?= base_url('client/neveras/' . $nevera['id_nevera'] . '/medir/guardar') ?>"
      method="post" enctype="multipart/form-data">
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">
                        <i class="fas fa-clock me-1"></i> Fecha y hora de registro (automática del servidor)
                    </label>
                    <input type="text" class="form-control bg-light" readonly
                           id="fechaHoraDisplay" value="<?= $fechaHoraNow ?>">
                    <small class="text-muted">Se registra el instante exacto en que presiones "Registrar medición".</small>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Temperatura (°C) <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" name="temperatura" class="form-control form-control-lg" required
                           placeholder="Ej: 4.2" autofocus inputmode="decimal">
                </div>
                <div class="col-md-12">
                    <label class="form-label">
                        <i class="fas fa-camera me-1"></i> Foto del termómetro
                        <span class="text-danger fw-bold">*</span>
                        <small class="text-muted">(evidencia obligatoria)</small>
                    </label>
                    <input type="file" name="foto_temperatura" class="form-control" accept="image/*" capture="environment" required>
                </div>

                <?php if ((int)$nevera['controla_humedad'] === 1): ?>
                <hr>
                <div class="col-md-12">
                    <label class="form-label">Humedad relativa (%)</label>
                    <input type="number" step="0.1" name="humedad_relativa" class="form-control"
                           placeholder="Ej: 55.0" min="0" max="100" inputmode="decimal">
                </div>
                <div class="col-md-12">
                    <label class="form-label">
                        <i class="fas fa-camera me-1"></i> Foto del higrómetro
                        <span class="text-danger fw-bold">*</span>
                        <small class="text-muted">(evidencia obligatoria)</small>
                    </label>
                    <input type="file" name="foto_humedad" class="form-control" accept="image/*" capture="environment" required>
                </div>
                <?php endif; ?>

                <hr>
                <div class="col-12">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" rows="2" class="form-control"
                              placeholder="Novedad observada, acción tomada si estuvo fuera de rango, etc."></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-lg w-100" style="background:#ee6c21;color:#fff;">
                    <i class="fas fa-save me-1"></i> Registrar medición
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// Reloj en vivo para que el usuario vea la hora actual del servidor antes de guardar
(function() {
    var el = document.getElementById('fechaHoraDisplay');
    if (!el) return;
    function tick() {
        var d = new Date();
        var p = function(n){ return n < 10 ? '0' + n : n; };
        el.value = p(d.getDate()) + '/' + p(d.getMonth()+1) + '/' + d.getFullYear()
                 + ' ' + p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds());
    }
    setInterval(tick, 1000);
})();
</script>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => 'Nueva lectura · ' . $nevera['nombre'],
    'content' => $content,
]);

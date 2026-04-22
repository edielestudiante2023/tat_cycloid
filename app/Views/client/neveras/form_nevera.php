<?php
$isEdit = !empty($nevera);
$action = $isEdit
    ? base_url('client/neveras/' . $nevera['id_nevera'] . '/actualizar')
    : base_url('client/neveras/guardar');

ob_start();
?>
<div class="page-header">
    <h1><i class="fas fa-<?= $isEdit ? 'edit' : 'snowflake' ?> me-2"></i>
        <?= $isEdit ? 'Editar nevera' : 'Nueva nevera' ?>
    </h1>
    <a href="<?= base_url('client/neveras') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form action="<?= $action ?>" method="post" enctype="multipart/form-data">
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" required
                           placeholder="Ej: Nevera mostrador, Congelador bodega"
                           value="<?= esc($nevera['nombre'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" id="tipoSel" class="form-select">
                        <?php foreach (\App\Models\NeveraModel::TIPOS as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($nevera['tipo'] ?? 'refrigeracion') === $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted" id="rangoInfo">
                        Rango esperado: <span id="rangoTexto">0°C a 8°C (Res. 2674/2013)</span>
                    </small>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Ubicación</label>
                    <input type="text" name="ubicacion" class="form-control"
                           placeholder="Ej: Zona de ventas, Cocina, Bodega"
                           value="<?= esc($nevera['ubicacion'] ?? '') ?>">
                </div>
                <div class="col-md-12">
                    <label class="form-label">
                        <i class="fas fa-camera me-1"></i> Foto del equipo
                        <small class="text-muted">(nevera + termómetro/higrómetro mostrados)</small>
                    </label>
                    <input type="file" name="foto_equipo" class="form-control" accept="image/*" capture="environment">
                    <?php if ($isEdit && !empty($nevera['foto_equipo'])): ?>
                        <div class="mt-2">
                            <img src="<?= base_url('uploads/' . $nevera['foto_equipo']) ?>"
                                 alt="Foto equipo" style="max-height:120px; border-radius:8px; border:1px solid #dee2e6;">
                            <div class="small text-muted">Foto actual. Subir otra la reemplaza.</div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="controla_humedad" value="1" id="chkHum"
                               <?= !empty($nevera['controla_humedad']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="chkHum">
                            Esta nevera también controla <strong>humedad relativa</strong>
                        </label>
                    </div>
                </div>

                <?php if ($isEdit): ?>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="activo" class="form-select">
                        <option value="1" <?= (int)($nevera['activo'] ?? 1) === 1 ? 'selected' : '' ?>>Activa</option>
                        <option value="0" <?= (int)($nevera['activo'] ?? 1) === 0 ? 'selected' : '' ?>>Inactiva</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div class="alert alert-info mt-3 mb-0" style="font-size:.85rem;">
                <i class="fas fa-info-circle me-1"></i>
                <strong>Nota:</strong> Los rangos de operación se aplican automáticamente según el tipo de nevera
                (Resolución 2674/2013): refrigeración 0-8°C, congelación -25 a -15°C, mixta -18 a 8°C.
                Si controlas humedad, el rango estándar es 40-70% HR.
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn" style="background:#ee6c21;color:#fff;">
                    <i class="fas fa-save me-1"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?>
                </button>
                <a href="<?= base_url('client/neveras') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<script>
// Mostrar el rango esperado según tipo seleccionado
document.getElementById('tipoSel').addEventListener('change', function() {
    var textos = {
        refrigeracion: '0°C a 8°C (Res. 2674/2013)',
        congelacion:   '-25°C a -15°C (Res. 2674/2013)',
        mixta:         '-18°C a 8°C (Res. 2674/2013)',
    };
    document.getElementById('rangoTexto').textContent = textos[this.value] || '';
});
document.getElementById('tipoSel').dispatchEvent(new Event('change'));
</script>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => $isEdit ? 'Editar nevera' : 'Nueva nevera',
    'content' => $content,
]);

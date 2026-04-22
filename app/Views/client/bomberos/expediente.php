<?php
ob_start();

$colorCompletitud = $completitud >= 100 ? '#198754' : ($completitud >= 60 ? '#ee6c21' : '#dc3545');
?>

<div class="page-header">
    <h1><i class="fas fa-folder-open me-2"></i> Expediente <?= esc($solicitud['anio']) ?></h1>
    <a href="<?= base_url('client/bomberos') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('msg') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<!-- Encabezado + completitud + radicación -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <h5 style="color:#c9541a; margin-bottom:4px;">
                    <?= esc($client['nombre_cliente']) ?>
                </h5>
                <div class="text-muted small">
                    Solicitud <?= esc($solicitud['anio']) ?> &middot;
                    Estado: <strong><?= esc(ucfirst($solicitud['estado'])) ?></strong>
                </div>
            </div>
            <div class="text-center" style="min-width:220px;">
                <div style="font-size:32px; font-weight:700; color:<?= $colorCompletitud ?>;">
                    <?= $completitud ?>%
                </div>
                <div class="text-muted small">Completitud · <?= $cumplidos ?>/<?= $totalRequeridos ?> obligatorios</div>
                <div class="progress mt-1" style="height:8px;">
                    <div class="progress-bar" role="progressbar"
                         style="width: <?= $completitud ?>%; background:<?= $colorCompletitud ?>;"></div>
                </div>
            </div>
        </div>

        <?php if ($completitud >= 100): ?>
            <a href="<?= esc($urlPortal) ?>" target="_blank" rel="noopener"
               class="btn w-100" style="background:linear-gradient(135deg,#198754,#20c997); color:#fff;">
                <i class="fas fa-external-link-alt me-1"></i>
                Radicar en portal de la alcaldía (<?= esc($solicitud['municipio']) ?>)
            </a>
        <?php else: ?>
            <button type="button" class="btn w-100" style="background:#ccc; color:#666; cursor:not-allowed;" disabled>
                <i class="fas fa-lock me-1"></i>
                El botón de radicar se habilita al alcanzar 100% de completitud
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Formulario encabezado -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="color:#c9541a;"><i class="fas fa-map-marker-alt me-1"></i> Ubicación y radicación</h6>
        <form method="post" action="<?= base_url('client/bomberos/expediente/' . $solicitud['id'] . '/encabezado') ?>">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small">Departamento</label>
                    <select name="departamento" id="selDept" class="form-select form-select-sm">
                        <?php foreach ($departamentos as $d): ?>
                            <option value="<?= esc($d['departamento']) ?>"
                                <?= $d['departamento'] === $solicitud['departamento'] ? 'selected' : '' ?>>
                                <?= esc($d['departamento']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Municipio</label>
                    <select name="municipio" id="selMuni" class="form-select form-select-sm">
                        <?php foreach ($municipiosDelDept as $m): ?>
                            <option value="<?= esc($m['municipio']) ?>"
                                <?= $m['municipio'] === $solicitud['municipio'] ? 'selected' : '' ?>>
                                <?= esc($m['municipio']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <?php foreach (['borrador','listo','radicado','aprobado','rechazado'] as $e): ?>
                            <option value="<?= $e ?>" <?= $solicitud['estado'] === $e ? 'selected' : '' ?>>
                                <?= ucfirst($e) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Fecha radicación</label>
                    <input type="date" name="fecha_radicacion" class="form-control form-control-sm"
                           value="<?= esc($solicitud['fecha_radicacion'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Número de radicado</label>
                    <input type="text" name="numero_radicado" class="form-control form-control-sm"
                           value="<?= esc($solicitud['numero_radicado'] ?? '') ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm w-100" style="background:#ee6c21;color:#fff;">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
                <div class="col-12">
                    <label class="form-label small">Observaciones</label>
                    <textarea name="observaciones" rows="2" class="form-control form-control-sm"><?= esc($solicitud['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// Helper para renderizar una sección de documento
function renderDocSection(array $tipos, array $docsAgrupados, int $idSolicitud, bool $obligatorio = true, bool $condicional = false) {
    foreach ($tipos as $tipoKey => $etiqueta):
        $items = $docsAgrupados[$tipoKey] ?? [];
        $tieneDoc = !empty($items);
?>
    <div class="card mb-2" style="<?= ($obligatorio && !$tieneDoc) ? 'border-left: 4px solid #dc3545;' : ($tieneDoc ? 'border-left: 4px solid #198754;' : 'border-left: 4px solid #ccc;') ?>">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                <div style="flex:1;">
                    <strong>
                        <?php if ($tieneDoc): ?>
                            <i class="fas fa-check-circle text-success"></i>
                        <?php elseif ($obligatorio): ?>
                            <i class="fas fa-exclamation-circle text-danger"></i>
                        <?php else: ?>
                            <i class="fas fa-circle text-muted"></i>
                        <?php endif; ?>
                        <?= esc($etiqueta) ?>
                        <?php if ($condicional): ?>
                            <small class="text-muted">(requerido por el tipo de establecimiento)</small>
                        <?php endif; ?>
                    </strong>
                    <?php if ($tieneDoc): ?>
                        <div class="small mt-1">
                            <?php foreach ($items as $it): ?>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="<?= base_url('uploads/' . $it['archivo']) ?>" target="_blank" style="color:#ee6c21;">
                                        <i class="fas fa-file-alt me-1"></i> <?= esc($it['archivo']) ?>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2"
                                            data-anular-url="<?= base_url('client/bomberos/expediente/' . $idSolicitud . '/doc/' . $it['id'] . '/eliminar') ?>"
                                            data-anular-titulo="Documento bomberos <?= esc($it['archivo'] ?? '') ?>">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <form method="post" enctype="multipart/form-data"
                  action="<?= base_url('client/bomberos/expediente/' . $idSolicitud . '/doc/subir') ?>"
                  class="mt-2">
                <input type="hidden" name="tipo_doc" value="<?= esc($tipoKey) ?>">
                <div class="row g-1 align-items-end">
                    <div class="col-md-9">
                        <input type="file" name="archivo" class="form-control form-control-sm"
                               accept=".pdf,image/*" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm w-100" style="background:#ee6c21;color:#fff;">
                            <i class="fas fa-upload"></i> Subir
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endforeach;
}
?>

<!-- Documentos obligatorios -->
<h6 style="color:#c9541a; margin-top:24px;">
    <i class="fas fa-list-check me-1"></i> Documentos obligatorios
</h6>
<?php renderDocSection($obligatorios, $docs, $solicitud['id'], true, false); ?>

<!-- Documentos condicionales -->
<?php if ($requiereExtra): ?>
    <h6 style="color:#c9541a; margin-top:24px;">
        <i class="fas fa-clipboard-list me-1"></i> Documentos condicionales
    </h6>
    <?php renderDocSection($condicionales, $docs, $solicitud['id'], true, true); ?>
<?php endif; ?>

<!-- Documentos de respuesta -->
<h6 style="color:#c9541a; margin-top:24px;">
    <i class="fas fa-envelope-open me-1"></i> Documentos emitidos por Bomberos
</h6>
<p class="text-muted small">Cargar aquí el concepto o documentos que devuelva la alcaldía/bomberos después de radicar.</p>
<?php renderDocSection($respuesta, $docs, $solicitud['id'], false, false); ?>

<?= view('client/inspecciones/_modal_anulacion') ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selDept = document.getElementById('selDept');
    var selMuni = document.getElementById('selMuni');
    selDept.addEventListener('change', function() {
        fetch('<?= base_url('client/bomberos/municipios') ?>?departamento=' + encodeURIComponent(this.value))
            .then(r => r.json())
            .then(rows => {
                selMuni.innerHTML = '';
                rows.forEach(r => {
                    var o = document.createElement('option');
                    o.value = r.municipio;
                    o.textContent = r.municipio;
                    selMuni.appendChild(o);
                });
            });
    });
});
</script>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => 'Expediente Bomberos ' . $solicitud['anio'],
    'content' => $content,
]);

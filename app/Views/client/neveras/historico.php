<?php
ob_start();
$tipoLabel = \App\Models\NeveraModel::TIPOS[$nevera['tipo']] ?? $nevera['tipo'];
?>
<div class="page-header">
    <h1><i class="fas fa-history me-2"></i> Histórico</h1>
    <a href="<?= base_url('client/neveras') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('msg') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h5 style="color:#c9541a; margin-bottom:4px;">
                <i class="fas fa-snowflake me-1"></i> <?= esc($nevera['nombre']) ?>
            </h5>
            <div class="text-muted small">
                <?= esc($tipoLabel) ?>
                &middot; Rango: <strong><?= number_format((float)$nevera['rango_temp_min'],1) ?>°C a <?= number_format((float)$nevera['rango_temp_max'],1) ?>°C</strong>
                <?php if ((int)$nevera['controla_humedad'] === 1): ?>
                    &middot; Humedad: <?= esc($nevera['rango_humedad_min'] ?? '-') ?>% a <?= esc($nevera['rango_humedad_max'] ?? '-') ?>%
                <?php endif; ?>
            </div>
        </div>
        <a href="<?= base_url('client/neveras/' . $nevera['id_nevera'] . '/medir') ?>"
           class="btn" style="background:#ee6c21; color:#fff;">
            <i class="fas fa-plus-circle me-1"></i> Nueva lectura
        </a>
    </div>
</div>

<?php if (empty($mediciones)): ?>
    <div class="card p-4 text-center">
        <i class="fas fa-thermometer-empty" style="font-size:48px; color:#ccc;"></i>
        <h5 class="mt-3 text-muted">Sin mediciones aún</h5>
        <p class="text-muted">Registra la primera lectura de temperatura y/o humedad.</p>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped mb-0 align-middle">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Temp.</th>
                        <th>Foto T°</th>
                        <?php if ((int)$nevera['controla_humedad'] === 1): ?>
                            <th>Humedad</th>
                            <th>Foto HR</th>
                        <?php endif; ?>
                        <th>Estado</th>
                        <th>Registrado por</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($mediciones as $m):
                    $dentro = (int)$m['dentro_rango'] === 1;
                ?>
                    <tr>
                        <td class="small"><?= date('d/m/Y H:i', strtotime($m['fecha_hora'])) ?></td>
                        <td>
                            <strong style="color:<?= $dentro ? '#198754' : '#dc3545' ?>;">
                                <?= number_format((float)$m['temperatura'],1) ?>°C
                            </strong>
                        </td>
                        <td>
                            <?php if (!empty($m['foto_temperatura'])): ?>
                                <a href="<?= base_url('uploads/' . $m['foto_temperatura']) ?>" target="_blank">
                                    <img src="<?= base_url('uploads/' . $m['foto_temperatura']) ?>"
                                         alt="termómetro"
                                         style="width:48px; height:48px; object-fit:cover; border-radius:6px; border:1px solid #dee2e6;">
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">sin foto</span>
                            <?php endif; ?>
                        </td>
                        <?php if ((int)$nevera['controla_humedad'] === 1): ?>
                            <td>
                                <?= $m['humedad_relativa'] !== null ? number_format((float)$m['humedad_relativa'],1) . '%' : '—' ?>
                            </td>
                            <td>
                                <?php if (!empty($m['foto_humedad'])): ?>
                                    <a href="<?= base_url('uploads/' . $m['foto_humedad']) ?>" target="_blank">
                                        <img src="<?= base_url('uploads/' . $m['foto_humedad']) ?>"
                                             alt="higrómetro"
                                             style="width:48px; height:48px; object-fit:cover; border-radius:6px; border:1px solid #dee2e6;">
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">sin foto</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td>
                            <?php if ($dentro): ?>
                                <span class="badge bg-success">✓ Dentro</span>
                            <?php else: ?>
                                <span class="badge bg-danger">⚠ Fuera</span>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= esc(ucfirst($m['registrado_por'])) ?></td>
                        <td>
                            <?php if (!empty($m['observaciones'])): ?>
                                <span title="<?= esc($m['observaciones']) ?>" style="cursor:help;">
                                    <i class="fas fa-comment text-muted me-1"></i>
                                </span>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                    data-anular-url="<?= base_url('client/neveras/' . $nevera['id_nevera'] . '/medicion/' . $m['id'] . '/eliminar') ?>"
                                    data-anular-titulo="Medición del <?= esc($m['fecha_hora'] ?? '') ?> — Nevera <?= esc($nevera['nombre'] ?? '') ?>">
                                <i class="fas fa-ban"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?= view('client/inspecciones/_modal_anulacion') ?>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => 'Histórico · ' . $nevera['nombre'],
    'content' => $content,
]);

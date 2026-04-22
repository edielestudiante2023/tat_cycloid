<?php
ob_start();
?>
<div class="page-header">
    <h1><i class="fas fa-snowflake me-2"></i> Control de Neveras</h1>
    <a href="<?= base_url('client/neveras/nueva') ?>" class="btn-back" style="background:rgba(255,255,255,.25);">
        <i class="fas fa-plus me-1"></i> Agregar nevera
    </a>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('msg') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<?php if (empty($neveras)): ?>
    <div class="card p-4 text-center">
        <i class="fas fa-snowflake" style="font-size:48px; color:#ccc;"></i>
        <h5 class="mt-3 text-muted">Aún no hay neveras registradas</h5>
        <p class="text-muted">Registra la primera nevera del local para empezar a tomar lecturas.</p>
        <a href="<?= base_url('client/neveras/nueva') ?>" class="btn mt-2" style="background:#ee6c21; color:#fff;">
            <i class="fas fa-plus me-1"></i> Agregar primera nevera
        </a>
    </div>
<?php else: ?>
    <?php foreach ($neveras as $n):
        $fueraRango = (int)$n['mediciones_fuera_rango'];
        $tipoLabel = \App\Models\NeveraModel::TIPOS[$n['tipo']] ?? $n['tipo'];
    ?>
        <?php $inactiva = (int)$n['activo'] === 0; ?>
        <div class="card mb-3" style="border-left:4px solid <?= $inactiva ? '#6c757d' : ($fueraRango > 0 ? '#dc3545' : '#198754') ?>; <?= $inactiva ? 'opacity:0.55;' : '' ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div style="flex:1; min-width:200px;">
                        <h5 class="mb-1" style="color:#c9541a;">
                            <i class="fas fa-snowflake me-1"></i> <?= esc($n['nombre']) ?>
                            <?php if ($inactiva): ?>
                                <span class="badge bg-secondary ms-2"><i class="fas fa-ban me-1"></i>ANULADA</span>
                            <?php endif; ?>
                        </h5>
                        <div class="text-muted small">
                            <?= esc($tipoLabel) ?>
                            <?php if (!empty($n['ubicacion'])): ?> &middot; <?= esc($n['ubicacion']) ?><?php endif; ?>
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-thermometer-half me-1"></i>
                            Rango: <?= number_format((float)$n['rango_temp_min'],1) ?>°C a <?= number_format((float)$n['rango_temp_max'],1) ?>°C
                            <?php if ((int)$n['controla_humedad'] === 1): ?>
                                &middot; <i class="fas fa-tint me-1"></i> Humedad: <?= esc($n['rango_humedad_min'] ?? '-') ?>% a <?= esc($n['rango_humedad_max'] ?? '-') ?>%
                            <?php endif; ?>
                        </div>
                        <div class="small mt-1">
                            <i class="fas fa-chart-line me-1"></i>
                            <strong><?= (int)$n['total_mediciones'] ?></strong> medición(es)
                            <?php if ($fueraRango > 0): ?>
                                · <span class="text-danger"><strong><?= $fueraRango ?></strong> fuera de rango</span>
                            <?php endif; ?>
                            <?php if (!empty($n['ultima_medicion'])): ?>
                                · última: <?= date('d/m/Y H:i', strtotime($n['ultima_medicion'])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1" style="min-width:140px;">
                        <?php if (!$inactiva): ?>
                            <a href="<?= base_url('client/neveras/' . $n['id_nevera'] . '/medir') ?>"
                               class="btn btn-sm" style="background:#ee6c21; color:#fff;">
                                <i class="fas fa-plus-circle me-1"></i> Tomar lectura
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url('client/neveras/' . $n['id_nevera'] . '/historico') ?>"
                           class="btn btn-sm btn-outline-primary" style="border-color:#ee6c21; color:#ee6c21;">
                            <i class="fas fa-history me-1"></i> Histórico
                        </a>
                        <?php if (!$inactiva): ?>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= base_url('client/neveras/' . $n['id_nevera'] . '/editar') ?>"
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger"
                                        data-anular-url="<?= base_url('client/neveras/' . $n['id_nevera'] . '/eliminar') ?>"
                                        data-anular-titulo="Nevera <?= esc($n['nombre']) ?>">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?= view('client/inspecciones/_modal_anulacion') ?>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => 'Control de Neveras',
    'content' => $content,
]);

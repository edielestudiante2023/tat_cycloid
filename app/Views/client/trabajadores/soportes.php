<?php
ob_start();

$iconos = [
    'datos'                  => 'fa-id-card',
    'afiliacion_salud'       => 'fa-notes-medical',
    'manipulacion_alimentos' => 'fa-utensils',
    'dotacion_epp'           => 'fa-hard-hat',
];
?>

<div class="page-header">
    <h1>
        <i class="fas fa-folder-open me-2"></i> Soportes
    </h1>
    <a href="<?= base_url('client/trabajadores') ?>" class="btn-back">
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
    <div class="card-body">
        <h5 style="color:#c9541a;"><i class="fas fa-user-circle me-1"></i> <?= esc($trabajador['nombre']) ?></h5>
        <div class="text-muted small">
            <?= esc($trabajador['tipo_id']) ?> <?= esc($trabajador['numero_id']) ?>
            <?php if (!empty($trabajador['cargo'])): ?> &middot; <?= esc($trabajador['cargo']) ?><?php endif; ?>
        </div>
    </div>
</div>

<?php foreach ($tipos as $tipoKey => $tipoLabel):
    $soportes = $soportesAgrupados[$tipoKey] ?? [];
    $esManipulacion = ($tipoKey === 'manipulacion_alimentos');
?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h5 style="color:#c9541a;">
                    <i class="fas <?= $iconos[$tipoKey] ?? 'fa-file' ?> me-1"></i>
                    <?= esc($tipoLabel) ?>
                </h5>
                <span class="badge bg-secondary"><?= count($soportes) ?></span>
            </div>

            <?php if (empty($soportes)): ?>
                <p class="text-muted small mb-3">Aún no hay archivos cargados para este soporte.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($soportes as $s): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <a href="<?= base_url('uploads/' . $s['archivo']) ?>" target="_blank" style="color:#ee6c21; font-weight:600;">
                                    <i class="fas fa-file-alt me-1"></i> <?= esc($s['archivo']) ?>
                                </a>
                                <div class="text-muted small">
                                    Cargado: <?= date('d/m/Y', strtotime($s['created_at'])) ?>
                                    <?php if (!empty($s['fecha_expedicion'])): ?>
                                        &middot; Expedido: <?= date('d/m/Y', strtotime($s['fecha_expedicion'])) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($s['fecha_vencimiento'])):
                                        $venc = strtotime($s['fecha_vencimiento']);
                                        $diasRest = (int)(($venc - time()) / 86400);
                                        $clase = $diasRest < 0 ? 'text-danger' : ($diasRest <= 30 ? 'text-warning' : 'text-success');
                                    ?>
                                        &middot; <span class="<?= $clase ?>">Vence: <?= date('d/m/Y', $venc) ?><?php if ($diasRest < 0): ?> (VENCIDO)<?php endif; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-anular-url="<?= base_url('client/trabajadores/' . $trabajador['id_trabajador'] . '/soportes/' . $s['id'] . '/eliminar') ?>"
                                    data-anular-titulo="Soporte de <?= esc($trabajador['nombre_completo'] ?? $trabajador['nombre'] ?? '') ?>">
                                <i class="fas fa-ban"></i>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data"
                  action="<?= base_url('client/trabajadores/' . $trabajador['id_trabajador'] . '/soportes/subir') ?>">
                <input type="hidden" name="tipo_soporte" value="<?= esc($tipoKey) ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small mb-1">Archivo (PDF o imagen)</label>
                        <input type="file" name="archivo" class="form-control form-control-sm"
                               accept=".pdf,image/*" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Fecha de expedición</label>
                        <input type="date" name="fecha_expedicion" class="form-control form-control-sm">
                    </div>
                    <?php if ($esManipulacion): ?>
                        <div class="col-md-3">
                            <label class="form-label small mb-1 text-danger">
                                <i class="fas fa-clock me-1"></i> Vence <span title="Obligatorio para manipulación de alimentos">*</span>
                            </label>
                            <input type="date" name="fecha_vencimiento" class="form-control form-control-sm" required>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-<?= $esManipulacion ? '1' : '4' ?>">
                        <button type="submit" class="btn btn-sm w-100" style="background:#ee6c21; color:#fff;">
                            <i class="fas fa-upload me-1"></i> Subir
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<?= view('client/inspecciones/_modal_anulacion') ?>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => 'Soportes — ' . ($trabajador['nombre'] ?? ''),
    'content' => $content,
]);

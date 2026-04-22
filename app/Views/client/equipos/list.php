<?php
ob_start();
?>
<div class="page-header">
    <h1><i class="fas fa-tools me-2"></i> Equipos y Utensilios</h1>
    <a href="<?= base_url('client/equipos/nueva') ?>" class="btn-back" style="background:rgba(255,255,255,.25);">
        <i class="fas fa-plus me-1"></i> Nueva inspección
    </a>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('msg') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<?php if (empty($inspecciones)): ?>
    <div class="card p-4 text-center">
        <i class="fas fa-tools" style="font-size:48px; color:#ccc;"></i>
        <h5 class="mt-3 text-muted">Sin inspecciones aún</h5>
        <p class="text-muted">Realiza la primera inspección semanal de equipos y utensilios.</p>
        <a href="<?= base_url('client/equipos/nueva') ?>" class="btn mt-2" style="background:#ee6c21; color:#fff;">
            <i class="fas fa-plus me-1"></i> Iniciar inspección
        </a>
    </div>
<?php else: ?>
    <?php foreach ($inspecciones as $i):
        $ok = $i['resultado_general'] === 'ok';
        $borde = $ok ? '#198754' : '#dc3545';
    ?>
        <div class="card mb-3" style="border-left:4px solid <?= $borde ?>;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h6 class="mb-1" style="color:#c9541a;">
                            <i class="fas fa-calendar-check me-1"></i>
                            <?= date('d/m/Y H:i', strtotime($i['fecha_hora'])) ?>
                            <?php if ($ok): ?>
                                <span class="badge bg-success ms-2">✓ Conforme</span>
                            <?php else: ?>
                                <span class="badge bg-danger ms-2">⚠ No conforme</span>
                            <?php endif; ?>
                        </h6>
                        <div class="text-muted small">
                            <i class="fas fa-clipboard-check me-1"></i>
                            <strong><?= (int)$i['total_items'] ?></strong> items
                            <?php if ((int)$i['total_defectuosos'] > 0): ?>
                                · <span class="text-danger"><strong><?= (int)$i['total_defectuosos'] ?></strong> defectuosos</span>
                            <?php endif; ?>
                            · <i class="fas fa-camera me-1"></i><strong><?= (int)$i['total_fotos'] ?></strong> fotos
                            · <?= esc(ucfirst($i['registrado_por'])) ?>
                        </div>
                        <?php if (!empty($i['thumbs'])): ?>
                            <div class="mt-2 d-flex gap-1 flex-wrap">
                                <?php foreach ($i['thumbs'] as $th): ?>
                                    <a href="<?= base_url('uploads/' . $th['foto']) ?>" target="_blank">
                                        <img src="<?= base_url('uploads/' . $th['foto']) ?>" alt="evidencia"
                                             style="width:56px; height:56px; object-fit:cover; border-radius:6px; border:1px solid #dee2e6;">
                                    </a>
                                <?php endforeach; ?>
                                <?php if ((int)$i['total_fotos'] > count($i['thumbs'])): ?>
                                    <span class="small text-muted align-self-end">+<?= (int)$i['total_fotos'] - count($i['thumbs']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="<?= base_url('client/equipos/' . $i['id'] . '/ver') ?>"
                           class="btn btn-sm btn-outline-primary" style="border-color:#ee6c21; color:#ee6c21;">
                            <i class="fas fa-eye me-1"></i> Ver
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                data-anular-url="<?= base_url('client/equipos/' . $i['id'] . '/eliminar') ?>"
                                data-anular-titulo="Inspección de equipos del <?= esc($i['fecha_hora'] ?? $i['creado_en'] ?? '') ?>">
                            <i class="fas fa-ban"></i>
                        </button>
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
    'title'   => 'Equipos y Utensilios',
    'content' => $content,
]);

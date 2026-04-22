<?php
ob_start();
$ok = $header['resultado_general'] === 'ok';
?>
<div class="page-header">
    <h1><i class="fas fa-clipboard-check me-2"></i> Inspección equipos</h1>
    <a href="<?= base_url('client/equipos') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card mb-3" style="border-left:4px solid <?= $ok ? '#198754' : '#dc3545' ?>;">
    <div class="card-body">
        <h5 style="color:#c9541a;">
            <?= date('d/m/Y H:i', strtotime($header['fecha_hora'])) ?>
            <?php if ($ok): ?>
                <span class="badge bg-success ms-2">✓ Conforme</span>
            <?php else: ?>
                <span class="badge bg-danger ms-2">⚠ No conforme</span>
            <?php endif; ?>
        </h5>
        <div class="text-muted small">Registrado por: <?= esc(ucfirst($header['registrado_por'])) ?></div>
        <?php if (!empty($header['observaciones_generales'])): ?>
            <hr>
            <div><strong>Observaciones generales:</strong> <?= nl2br(esc($header['observaciones_generales'])) ?></div>
        <?php endif; ?>
    </div>
</div>

<?php foreach ($detalles as $d):
    $borde = '#ccc'; $bClass = 'bg-secondary'; $bText = 'No aplica';
    if ($d['estado'] === 'funcional') { $borde = '#198754'; $bClass = 'bg-success'; $bText = 'Funcional'; }
    elseif ($d['estado'] === 'defectuoso') { $borde = '#dc3545'; $bClass = 'bg-danger'; $bText = 'Defectuoso'; }
?>
    <div class="card mb-2" style="border-left:4px solid <?= $borde ?>;">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <?php if (!empty($d['item_icono'])): ?>
                        <i class="fas <?= esc($d['item_icono']) ?> me-1" style="color:#ee6c21;"></i>
                    <?php endif; ?>
                    <strong><?= esc($d['item_nombre'] ?? 'Item') ?></strong>
                    <span class="badge <?= $bClass ?> ms-2"><?= $bText ?></span>
                    <?php if (!empty($d['observaciones'])): ?>
                        <div class="small text-muted mt-1"><?= esc($d['observaciones']) ?></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($d['foto'])): ?>
                    <a href="<?= base_url('uploads/' . $d['foto']) ?>" target="_blank">
                        <img src="<?= base_url('uploads/' . $d['foto']) ?>" alt=""
                             style="max-height:60px; border-radius:6px; border:1px solid #dee2e6;">
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => 'Inspección equipos · ' . date('d/m/Y', strtotime($header['fecha_hora'])),
    'content' => $content,
]);

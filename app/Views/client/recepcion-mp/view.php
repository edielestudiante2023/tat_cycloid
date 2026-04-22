<?php
ob_start();
$ok = (int)$recepcion['aceptado'] === 1;
?>
<div class="page-header">
    <h1><i class="fas fa-clipboard-check me-2"></i> Recepción #<?= $recepcion['id'] ?></h1>
    <a href="<?= base_url('client/recepcion-mp') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card mb-3" style="border-left:4px solid <?= $ok ? '#198754' : '#dc3545' ?>;">
    <div class="card-body">
        <h5 style="color:#c9541a;">
            <?= esc($recepcion['producto']) ?>
            <?php if ($ok): ?>
                <span class="badge bg-success ms-2">✓ Aceptado</span>
            <?php else: ?>
                <span class="badge bg-danger ms-2">✗ Rechazado</span>
            <?php endif; ?>
        </h5>
        <div class="text-muted small">
            <i class="fas fa-calendar me-1"></i> <?= date('d/m/Y H:i', strtotime($recepcion['fecha_hora'])) ?>
            · Registrado por: <?= esc(ucfirst($recepcion['registrado_por'])) ?>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h6 style="color:#c9541a;"><i class="fas fa-user me-1"></i> Proveedor</h6>
        <?= esc($recepcion['proveedor_nombre']) ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h6 style="color:#c9541a;"><i class="fas fa-box me-1"></i> Detalles del producto</h6>
        <div class="row g-2 small">
            <div class="col-md-4"><strong>Categoría:</strong> <?= esc($categorias[$recepcion['categoria']] ?? $recepcion['categoria']) ?></div>
            <?php if ($recepcion['cantidad']): ?>
                <div class="col-md-4"><strong>Cantidad:</strong> <?= number_format((float)$recepcion['cantidad'],2) ?> <?= esc($recepcion['unidad']) ?></div>
            <?php endif; ?>
            <?php if ($recepcion['numero_factura']): ?>
                <div class="col-md-4"><strong>Factura:</strong> <?= esc($recepcion['numero_factura']) ?></div>
            <?php endif; ?>
            <?php if ($recepcion['lote']): ?>
                <div class="col-md-4"><strong>Lote:</strong> <?= esc($recepcion['lote']) ?></div>
            <?php endif; ?>
            <?php if ($recepcion['registro_sanitario']): ?>
                <div class="col-md-4"><strong>Reg. sanitario:</strong> <?= esc($recepcion['registro_sanitario']) ?></div>
            <?php endif; ?>
            <?php if ($recepcion['fecha_vencimiento_producto']):
                $venc = strtotime($recepcion['fecha_vencimiento_producto']);
                $dias = (int)(($venc - time()) / 86400);
                $cls = $dias < 0 ? 'text-danger' : ($dias < 7 ? 'text-warning' : 'text-success');
            ?>
                <div class="col-md-4"><strong>Vence:</strong>
                    <span class="<?= $cls ?>"><?= date('d/m/Y', $venc) ?><?php if ($dias < 0): ?> (VENCIDO)<?php endif; ?></span>
                </div>
            <?php endif; ?>
            <?php if ($recepcion['temperatura_recepcion'] !== null): ?>
                <div class="col-md-4"><strong>Temp. recepción:</strong> <?= number_format((float)$recepcion['temperatura_recepcion'],1) ?>°C</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h6 style="color:#c9541a;"><i class="fas fa-clipboard-check me-1"></i> Control sanitario</h6>
        <div>Empaque íntegro: <?= $recepcion['empaque_ok'] ? '<span class="badge bg-success">SÍ</span>' : '<span class="badge bg-danger">NO</span>' ?></div>
        <div>Producto OK: <?= $recepcion['producto_ok'] ? '<span class="badge bg-success">SÍ</span>' : '<span class="badge bg-danger">NO</span>' ?></div>
        <?php if (!$ok && !empty($recepcion['motivo_rechazo'])): ?>
            <div class="alert alert-danger mt-3 mb-0">
                <strong>Motivo rechazo:</strong> <?= esc($recepcion['motivo_rechazo']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($recepcion['foto_producto']) || !empty($recepcion['foto_factura']) || !empty($recepcion['foto_temperatura'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="color:#c9541a;"><i class="fas fa-camera me-1"></i> Evidencia fotográfica</h6>
        <div class="d-flex flex-wrap gap-3">
            <?php foreach (['foto_producto' => 'Producto', 'foto_factura' => 'Factura', 'foto_temperatura' => 'Termómetro'] as $campo => $label):
                if (!empty($recepcion[$campo])): ?>
                <div class="text-center">
                    <a href="<?= base_url('uploads/' . $recepcion[$campo]) ?>" target="_blank">
                        <img src="<?= base_url('uploads/' . $recepcion[$campo]) ?>" alt="<?= $label ?>"
                             style="max-height:150px; border-radius:8px; border:1px solid #dee2e6;">
                    </a>
                    <div class="small text-muted mt-1"><?= $label ?></div>
                </div>
            <?php endif; endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($recepcion['observaciones'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="color:#c9541a;">Observaciones</h6>
        <?= nl2br(esc($recepcion['observaciones'])) ?>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => 'Recepción #' . $recepcion['id'],
    'content' => $content,
]);

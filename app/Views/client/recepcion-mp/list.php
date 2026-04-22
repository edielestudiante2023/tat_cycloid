<?php
ob_start();
$categorias = \App\Models\RecepcionMpModel::CATEGORIAS;
?>
<div class="page-header">
    <h1><i class="fas fa-truck-ramp-box me-2"></i> Recepción de Materias Primas</h1>
    <div class="d-flex gap-1">
        <a href="<?= base_url('client/recepcion-mp/proveedores') ?>" class="btn-back" style="background:rgba(255,255,255,.25);">
            <i class="fas fa-users me-1"></i> Proveedores
        </a>
        <a href="<?= base_url('client/recepcion-mp/nueva') ?>" class="btn-back" style="background:rgba(255,255,255,.25);">
            <i class="fas fa-plus me-1"></i> Nueva recepción
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('msg') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form method="get" class="card mb-3">
    <div class="card-body d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="form-label small mb-1">Desde</label>
            <input type="date" name="desde" class="form-control form-control-sm" value="<?= esc($desde ?? '') ?>">
        </div>
        <div>
            <label class="form-label small mb-1">Hasta</label>
            <input type="date" name="hasta" class="form-control form-control-sm" value="<?= esc($hasta ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-sm" style="background:#6f4f28;color:#fff;">
            <i class="fas fa-filter me-1"></i> Filtrar
        </button>
        <?php if ($desde || $hasta): ?>
            <a href="<?= base_url('client/recepcion-mp') ?>" class="btn btn-sm btn-outline-secondary">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($recepciones)): ?>
    <div class="card p-4 text-center">
        <i class="fas fa-truck-ramp-box" style="font-size:48px; color:#ccc;"></i>
        <h5 class="mt-3 text-muted">Sin recepciones registradas</h5>
        <p class="text-muted">Registra la primera entrega de proveedor.</p>
        <a href="<?= base_url('client/recepcion-mp/nueva') ?>" class="btn mt-2" style="background:#ee6c21; color:#fff;">
            <i class="fas fa-plus me-1"></i> Registrar primera
        </a>
    </div>
<?php else: ?>
    <?php foreach ($recepciones as $r):
        $ok = (int)$r['aceptado'] === 1;
        $borde = $ok ? '#198754' : '#dc3545';
    ?>
        <div class="card mb-3" style="border-left:4px solid <?= $borde ?>;">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div style="flex:1; min-width:200px;">
                        <h6 class="mb-1" style="color:#c9541a;">
                            <i class="fas fa-boxes-stacked me-1"></i> <?= esc($r['producto']) ?>
                            <?php if ($ok): ?>
                                <span class="badge bg-success ms-2">✓ Aceptado</span>
                            <?php else: ?>
                                <span class="badge bg-danger ms-2">✗ Rechazado</span>
                            <?php endif; ?>
                        </h6>
                        <div class="text-muted small">
                            <i class="fas fa-user me-1"></i> <?= esc($r['proveedor_nombre']) ?>
                            &middot; <i class="fas fa-tag me-1"></i> <?= esc($categorias[$r['categoria']] ?? $r['categoria']) ?>
                            <?php if ($r['cantidad']): ?>
                                &middot; <?= number_format((float)$r['cantidad'],2) ?> <?= esc($r['unidad'] ?? '') ?>
                            <?php endif; ?>
                            <?php if ($r['temperatura_recepcion'] !== null): ?>
                                &middot; <i class="fas fa-thermometer-half me-1"></i> <?= number_format((float)$r['temperatura_recepcion'],1) ?>°C
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-calendar me-1"></i> <?= date('d/m/Y H:i', strtotime($r['fecha_hora'])) ?>
                            <?php if ($r['fecha_vencimiento_producto']):
                                $venc = strtotime($r['fecha_vencimiento_producto']);
                                $dias = (int)(($venc - time()) / 86400);
                                $cls = $dias < 0 ? 'text-danger' : ($dias < 7 ? 'text-warning' : 'text-success');
                            ?>
                                &middot; Vence: <span class="<?= $cls ?>"><?= date('d/m/Y', $venc) ?><?php if ($dias < 0): ?> (VENCIDO)<?php elseif ($dias < 7): ?> (<?= $dias ?> días)<?php endif; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!$ok && !empty($r['motivo_rechazo'])): ?>
                            <div class="small text-danger mt-1"><i class="fas fa-exclamation-triangle me-1"></i> <?= esc($r['motivo_rechazo']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($r['foto_producto'])): ?>
                        <a href="<?= base_url('uploads/' . $r['foto_producto']) ?>" target="_blank">
                            <img src="<?= base_url('uploads/' . $r['foto_producto']) ?>" alt="producto"
                                 style="width:72px; height:72px; object-fit:cover; border-radius:8px; border:1px solid #dee2e6;">
                        </a>
                    <?php endif; ?>
                    <div class="d-flex flex-column gap-1" style="min-width:80px;">
                        <a href="<?= base_url('client/recepcion-mp/' . $r['id'] . '/ver') ?>"
                           class="btn btn-sm btn-outline-primary" style="border-color:#ee6c21; color:#ee6c21;">
                            <i class="fas fa-eye me-1"></i> Ver
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                data-anular-url="<?= base_url('client/recepcion-mp/' . $r['id'] . '/eliminar') ?>"
                                data-anular-titulo="Recepción MP del <?= esc($r['fecha_hora'] ?? '') ?>">
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
    'title'   => 'Recepción de Materias Primas',
    'content' => $content,
]);

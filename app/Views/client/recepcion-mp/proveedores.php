<?php
ob_start();
?>
<div class="page-header">
    <h1><i class="fas fa-users me-2"></i> Proveedores</h1>
    <a href="<?= base_url('client/recepcion-mp') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('msg') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<!-- Form agregar -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="color:#c9541a;"><i class="fas fa-plus me-1"></i> Agregar proveedor</h6>
        <form method="post" action="<?= base_url('client/recepcion-mp/proveedores/guardar') ?>">
            <div class="row g-2">
                <div class="col-md-5">
                    <label class="form-label small">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">NIT</label>
                    <input type="text" name="nit" class="form-control form-control-sm">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Categoría principal</label>
                    <select name="categoria_principal" class="form-select form-select-sm">
                        <option value="">--</option>
                        <?php foreach ($categorias as $k => $v): ?>
                            <option value="<?= $k ?>"><?= esc($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Teléfono</label>
                    <input type="text" name="telefono" class="form-control form-control-sm">
                </div>
                <div class="col-md-8">
                    <label class="form-label small">Dirección</label>
                    <input type="text" name="direccion" class="form-control form-control-sm">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-sm" style="background:#ee6c21;color:#fff;">
                        <i class="fas fa-plus me-1"></i> Agregar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista -->
<?php if (empty($proveedores)): ?>
    <div class="card p-4 text-center text-muted">Aún no hay proveedores registrados.</div>
<?php else: ?>
    <?php foreach ($proveedores as $p):
        $activo = (int)$p['activo'] === 1;
    ?>
        <div class="card mb-2" style="<?= !$activo ? 'opacity:.6;' : '' ?>">
            <div class="card-body py-3">
                <form method="post" action="<?= base_url('client/recepcion-mp/proveedores/' . $p['id_proveedor'] . '/actualizar') ?>">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small">
                                Nombre
                                <?php if (!$activo): ?>
                                    <span class="badge bg-secondary ms-1"><i class="fas fa-ban me-1"></i>ANULADO</span>
                                <?php endif; ?>
                            </label>
                            <input type="text" name="nombre" class="form-control form-control-sm" value="<?= esc($p['nombre']) ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">NIT</label>
                            <input type="text" name="nit" class="form-control form-control-sm" value="<?= esc($p['nit']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Categoría</label>
                            <select name="categoria_principal" class="form-select form-select-sm">
                                <option value="">--</option>
                                <?php foreach ($categorias as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= $p['categoria_principal'] === $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Teléfono</label>
                            <input type="text" name="telefono" class="form-control form-control-sm" value="<?= esc($p['telefono']) ?>">
                        </div>
                        <div class="col-md-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="activo" value="1" <?= $activo ? 'checked' : '' ?>>
                                <label class="form-check-label small">Activo</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small">Dirección</label>
                            <input type="text" name="direccion" class="form-control form-control-sm" value="<?= esc($p['direccion']) ?>">
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-outline-primary" style="border-color:#ee6c21;color:#ee6c21;">
                                <i class="fas fa-save"></i> Actualizar
                            </button>
                            <?php if ($activo): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-anular-url="<?= base_url('client/recepcion-mp/proveedores/' . $p['id_proveedor'] . '/eliminar') ?>"
                                    data-anular-titulo="Proveedor: <?= esc($p['nombre'] ?? '') ?>">
                                <i class="fas fa-ban"></i> Desactivar
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?= view('client/inspecciones/_modal_anulacion') ?>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => 'Proveedores',
    'content' => $content,
]);

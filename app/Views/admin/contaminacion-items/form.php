<?php
$isEdit = !empty($item);
$action = $isEdit
    ? base_url('admin/contaminacion-items/' . $item['id_item'] . '/actualizar')
    : base_url('admin/contaminacion-items/guardar');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isEdit ? 'Editar' : 'Nuevo' ?> item — Items — Contaminación Cruzada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; min-height: 100vh; }
        .page-wrap { max-width: 800px; margin: 40px auto; padding: 0 16px; }
        .header-card { background: linear-gradient(135deg,#dc3545,#e74c3c); color:#fff; padding:24px; border-radius:14px; }
    </style>
</head>
<body>
<div class="page-wrap">
    <div class="header-card mb-4">
        <h3 class="mb-0"><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-plus' ?> me-2"></i>
            <?= $isEdit ? 'Editar' : 'Nuevo' ?> item — Contaminación
        </h3>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $action ?>">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" required
                               value="<?= esc($item['nombre'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Orden</label>
                        <input type="number" name="orden" class="form-control" value="<?= esc($item['orden'] ?? 0) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" rows="2" class="form-control"
                                  placeholder="Criterios de verificación"><?= esc($item['descripcion'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Icono Font Awesome</label>
                        <input type="text" name="icono" class="form-control"
                               placeholder="Ej: fa-layer-group, fa-calendar-xmark"
                               value="<?= esc($item['icono'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activo" value="1" id="chkActivo"
                                   <?= (int)($item['activo'] ?? 1) === 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="chkActivo">Activo</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn" style="background:#ee6c21;color:#fff;">
                        <i class="fas fa-save me-1"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?>
                    </button>
                    <a href="<?= base_url('admin/contaminacion-items') ?>" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
</div>
</body>
</html>
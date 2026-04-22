<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catálogo Items de Equipos y Utensilios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; min-height: 100vh; }
        .page-wrap { max-width: 1100px; margin: 40px auto; padding: 0 16px; }
        .header-card { background: linear-gradient(135deg,#6c757d,#495057); color:#fff; padding:24px; border-radius:14px; }
    </style>
</head>
<body>
<div class="page-wrap">
    <div class="header-card mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="mb-0"><i class="fas fa-tools me-2"></i> Catálogo — Items de Equipos y Utensilios</h3>
            <small>Administra la lista que ven los tenderos al inspeccionar equipos.</small>
        </div>
        <div>
            <a href="<?= base_url('admin/equipos-items/nuevo') ?>" class="btn" style="background:#ee6c21;color:#fff;">
                <i class="fas fa-plus me-1"></i> Nuevo item
            </a>
            <a href="<?= base_url('dashboardconsultant') ?>" class="btn btn-outline-light ms-2"><i class="fas fa-arrow-left"></i></a>
        </div>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <label class="small me-1"><i class="fas fa-user-gear me-1"></i> Asignar items a cliente:</label>
        <select class="form-select" style="max-width:320px;"
                onchange="if(this.value) window.location='<?= base_url('admin/equipos-items/asignar/') ?>'+this.value;">
            <option value="">Seleccionar cliente…</option>
            <?php foreach ($clients as $c): ?>
                <option value="<?= $c['id_cliente'] ?>"><?= esc($c['nombre_cliente']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th style="width:80px;">Orden</th>
                        <th style="width:90px;">Estado</th>
                        <th style="width:140px;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $i): ?>
                    <tr style="<?= (int)$i['activo'] === 0 ? 'opacity:0.55;' : '' ?>">
                        <td>
                            <?php if (!empty($i['icono'])): ?>
                                <i class="fas <?= esc($i['icono']) ?>" style="color:#ee6c21;"></i>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= esc($i['nombre']) ?></strong></td>
                        <td class="small text-muted"><?= esc($i['descripcion'] ?? '') ?></td>
                        <td><?= (int)$i['orden'] ?></td>
                        <td>
                            <?php if ((int)$i['activo'] === 1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= base_url('admin/equipos-items/' . $i['id_item'] . '/editar') ?>"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-pen"></i>
                            </a>
                            <?php if ((int)$i['activo'] === 1): ?>
                                <form method="post" action="<?= base_url('admin/equipos-items/' . $i['id_item'] . '/eliminar') ?>"
                                      onsubmit="return confirm('¿Desactivar este item? (el historial se preserva)');" style="display:inline;">
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= base_url('admin/equipos-items/' . $i['id_item'] . '/activar') ?>" style="display:inline;">
                                    <button class="btn btn-sm btn-outline-success"><i class="fas fa-rotate"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                    <tr><td colspan="6" class="text-center text-muted p-4">No hay items.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>

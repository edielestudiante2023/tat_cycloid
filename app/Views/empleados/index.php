<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Empleados — Rutinas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{background:#f4f6f9}
.card-r{border:0;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06)}
.header-r{background:linear-gradient(135deg,#1c2437,#2d3a5e);color:#fff;border-radius:12px 12px 0 0;padding:18px}
.header-r h1{color:#bd9751;margin:0;font-size:1.4rem}
.btn-c{background:#bd9751;color:#1c2437;border:0;font-weight:600}
.btn-c:hover{background:#a88041;color:#fff}
</style>
</head>
<body>
<div class="container py-3 py-md-4">
    <div class="card card-r mb-3">
        <div class="header-r d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1><i class="bi bi-people-fill"></i> Empleados (personal del local)</h1>
            <?php if ($idCliente > 0): ?>
                <a href="<?= base_url('empleados/add?cliente='.$idCliente) ?>" class="btn btn-c btn-sm">
                    <i class="bi bi-plus-lg"></i> Nuevo empleado
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (session('msg')): ?><div class="alert alert-success"><?= esc(session('msg')) ?></div><?php endif; ?>
            <?php if (session('error')): ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>

            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md-8">
                    <label class="form-label small mb-1">Cliente</label>
                    <select name="cliente" class="form-select form-select-sm" <?= session('role') === 'client' ? 'disabled' : '' ?>>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= (int)$c['id_cliente'] ?>" <?= (int)$c['id_cliente'] === $idCliente ? 'selected' : '' ?>>
                                <?= esc($c['nombre_cliente']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <button class="btn btn-dark btn-sm w-100"><i class="bi bi-funnel"></i> Ver empleados</button>
                </div>
            </form>

            <?php if (empty($empleados)): ?>
                <div class="alert alert-info">
                    No hay empleados registrados para este cliente.
                    <?php if ($idCliente > 0): ?>
                        <a href="<?= base_url('empleados/add?cliente='.$idCliente) ?>">Crear el primero</a>.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr><th>Nombre</th><th>Email</th><th>Estado</th><th>Creado</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empleados as $e): ?>
                                <tr>
                                    <td><strong><?= esc($e['nombre_completo']) ?></strong></td>
                                    <td class="small"><?= esc($e['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $e['estado'] === 'activo' ? 'success' : 'secondary' ?>">
                                            <?= esc($e['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= esc(date('d/m/Y', strtotime($e['created_at'] ?? 'now'))) ?></td>
                                    <td class="text-end">
                                        <a href="<?= base_url('empleados/edit/'.$e['id_usuario']) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= base_url('empleados/delete/'.$e['id_usuario']) ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('¿Eliminar empleado? También borrará sus asignaciones y registros.');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="mt-3 d-flex flex-wrap gap-2">
                <a href="<?= base_url('rutinas/asignaciones') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-diagram-3"></i> Asignaciones</a>
                <a href="<?= base_url('rutinas/calendario') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-calendar3"></i> Calendario</a>
                <a href="<?= base_url(session('role') === 'client' ? 'dashboard' : 'admindashboard') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php helper('rutinas'); echo rutinas_floating_back(); ?>
</body>
</html>

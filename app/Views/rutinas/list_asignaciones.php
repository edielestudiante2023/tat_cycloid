<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Asignaciones — Rutinas</title>
<link rel="manifest" href="<?= base_url('manifest_rutinas.json') ?>">
<meta name="theme-color" content="#1c2437">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
body{background:#f4f6f9}
.card-rutinas{border:0;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06)}
.header-rutinas{background:linear-gradient(135deg,#1c2437,#2d3a5e);color:#fff;border-radius:12px 12px 0 0;padding:18px}
.header-rutinas h1{color:#bd9751;margin:0;font-size:1.4rem}
.btn-cycloid{background:#bd9751;color:#1c2437;border:0;font-weight:600}
.btn-cycloid:hover{background:#a88041;color:#fff}
.actividades-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:8px;max-height:340px;overflow-y:auto;padding:10px;border:1px solid #dee2e6;border-radius:8px;background:#fff}
.actividades-grid label{cursor:pointer;margin:0;padding:8px;border-radius:6px}
.actividades-grid label:hover{background:#f6f2e8}
</style>
</head>
<body>
<div class="container py-3 py-md-4">
    <div class="card card-rutinas mb-3">
        <div class="header-rutinas d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1><i class="bi bi-diagram-3"></i> Asignaciones de rutinas</h1>
            <a href="<?= base_url('empleados?cliente='.$idCliente) ?>" class="btn btn-sm btn-light">
                <i class="bi bi-people"></i> Gestionar empleados
            </a>
        </div>
        <div class="card-body">
            <?php if (session('msg')): ?><div class="alert alert-success"><?= esc(session('msg')) ?></div><?php endif; ?>
            <?php if (session('error')): ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>

            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md-10">
                    <label class="form-label small mb-1">Cliente (local)</label>
                    <select name="cliente" class="form-select form-select-sm" <?= session('role') === 'client' ? 'disabled' : '' ?>>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= (int)$c['id_cliente'] ?>" <?= (int)$c['id_cliente'] === $idCliente ? 'selected' : '' ?>>
                                <?= esc($c['nombre_cliente']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-dark btn-sm w-100"><i class="bi bi-funnel"></i> Ver</button>
                </div>
            </form>

            <?php if (empty($empleados)): ?>
                <div class="alert alert-warning">
                    Este local no tiene empleados registrados.
                    <a href="<?= base_url('empleados/add?cliente='.$idCliente) ?>">Crea el primero</a> para poder asignar rutinas.
                </div>
            <?php else: ?>
                <form action="<?= base_url('rutinas/asignaciones/add') ?>" method="post">
                    <input type="hidden" name="id_cliente" value="<?= (int)$idCliente ?>">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Empleado del local</label>
                            <select name="id_usuario" class="form-select" required>
                                <option value="">Seleccione…</option>
                                <?php foreach ($empleados as $e): ?>
                                    <option value="<?= (int)$e['id_usuario'] ?>">
                                        <?= esc($e['nombre_completo']) ?> (<?= esc($e['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Actividades a asignar</label>
                            <div class="actividades-grid">
                                <?php foreach ($actividades as $a): ?>
                                    <label>
                                        <input type="checkbox" name="actividades[]" value="<?= (int)$a['id_actividad'] ?>">
                                        <?= esc($a['nombre']) ?>
                                        <small class="text-muted">(<?= esc($a['frecuencia']) ?>, peso <?= esc($a['peso']) ?>)</small>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-cycloid"><i class="bi bi-plus-lg"></i> Asignar</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($empleados)): ?>
    <div class="card card-rutinas">
        <div class="card-body">
            <h6 class="mb-3">Asignaciones actuales del local</h6>
            <div class="table-responsive">
                <table id="tblAsignaciones" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th class="d-none d-md-table-cell">Email</th>
                            <th>Actividad</th>
                            <th>Frec.</th>
                            <th>Peso</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($asignaciones as $a): ?>
                            <tr>
                                <td><?= esc($a['nombre_completo']) ?></td>
                                <td class="d-none d-md-table-cell small text-muted"><?= esc($a['email']) ?></td>
                                <td><?= esc($a['actividad']) ?></td>
                                <td><span class="badge bg-secondary"><?= esc($a['frecuencia']) ?></span></td>
                                <td><?= esc($a['peso']) ?></td>
                                <td class="text-end">
                                    <a href="<?= base_url('rutinas/asignaciones/delete/'.$a['id_asignacion']) ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('¿Eliminar asignación?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <a href="<?= base_url('rutinas/calendario?cliente='.$idCliente) ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-calendar3"></i> Calendario del local</a>
                <a href="<?= base_url('rutinas/actividades') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-list-check"></i> Actividades</a>
                <a href="<?= base_url('empleados?cliente='.$idCliente) ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-people"></i> Empleados</a>
                <a href="<?= base_url(session('role') === 'client' ? 'dashboard' : 'admindashboard') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
    if ($('#tblAsignaciones').length) {
        $('#tblAsignaciones').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            order: [[0,'asc']], pageLength: 25
        });
    }
});
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= base_url('sw_rutinas.js') ?>').catch(()=>{});
}
</script>
</body>
</html>

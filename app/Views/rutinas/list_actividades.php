<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Actividades — Rutinas</title>
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
@media (max-width: 576px){ .header-rutinas h1{font-size:1.1rem} }
</style>
</head>
<body>
<div class="container py-3 py-md-4">
    <div class="card card-rutinas">
        <div class="header-rutinas d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1><i class="bi bi-list-check"></i> Actividades de rutinas</h1>
            <a href="<?= base_url('rutinas/actividades/add') ?>" class="btn btn-cycloid btn-sm">
                <i class="bi bi-plus-circle"></i> Nueva
            </a>
        </div>
        <div class="card-body">
            <?php if (session('msg')): ?>
                <div class="alert alert-success"><?= esc(session('msg')) ?></div>
            <?php endif; ?>
            <div class="table-responsive">
                <table id="tblActividades" class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th class="d-none d-md-table-cell">Descripción</th>
                            <th>Frec.</th>
                            <th>Peso</th>
                            <th>Activa</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actividades as $a): ?>
                            <tr>
                                <td><strong><?= esc($a['nombre']) ?></strong></td>
                                <td class="d-none d-md-table-cell text-muted small"><?= esc($a['descripcion'] ?? '') ?></td>
                                <td><span class="badge bg-secondary"><?= esc($a['frecuencia']) ?></span></td>
                                <td><?= esc($a['peso']) ?></td>
                                <td>
                                    <?php if ($a['activa']): ?>
                                        <span class="badge bg-success">Sí</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('rutinas/actividades/edit/'.$a['id_actividad']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= base_url('rutinas/actividades/delete/'.$a['id_actividad']) ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('¿Eliminar actividad? Se borrarán también sus asignaciones y registros.');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <a href="<?= base_url('rutinas/calendario') ?>" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-calendar3"></i> Ver calendario
                </a>
                <a href="<?= base_url('rutinas/asignaciones') ?>" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-people"></i> Asignaciones
                </a>
                <a href="<?= base_url('admindashboard') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
    $('#tblActividades').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        order: [[0,'asc']], pageLength: 25
    });
});
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= base_url('sw_rutinas.js') ?>').catch(()=>{});
}
</script>
</body>
</html>

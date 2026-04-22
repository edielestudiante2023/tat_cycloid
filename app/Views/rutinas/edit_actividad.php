<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar actividad — Rutinas</title>
<link rel="manifest" href="<?= base_url('manifest_rutinas.json') ?>">
<meta name="theme-color" content="#1c2437">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{background:#f4f6f9}
.card-rutinas{border:0;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06)}
.header-rutinas{background:linear-gradient(135deg,#1c2437,#2d3a5e);color:#fff;border-radius:12px 12px 0 0;padding:18px}
.header-rutinas h1{color:#bd9751;margin:0;font-size:1.4rem}
.btn-cycloid{background:#bd9751;color:#1c2437;border:0;font-weight:600}
.btn-cycloid:hover{background:#a88041;color:#fff}
</style>
</head>
<body>
<div class="container py-3 py-md-4" style="max-width:720px;">
    <div class="card card-rutinas">
        <div class="header-rutinas"><h1><i class="bi bi-pencil-square"></i> Editar actividad</h1></div>
        <div class="card-body">
            <?php if (session('errors')): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ((array) session('errors') as $e) echo '<li>'.esc($e).'</li>'; ?></ul>
                </div>
            <?php endif; ?>
            <form action="<?= base_url('rutinas/actividades/edit/'.$actividad['id_actividad']) ?>" method="post">
                <div class="mb-3">
                    <label class="form-label">Nombre *</label>
                    <input name="nombre" class="form-control" required value="<?= esc($actividad['nombre']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?= esc($actividad['descripcion'] ?? '') ?></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Frecuencia *</label>
                        <select name="frecuencia" class="form-select" required>
                            <option value="L-V" <?= $actividad['frecuencia']==='L-V'?'selected':'' ?>>Lunes a Viernes</option>
                            <option value="diaria" <?= $actividad['frecuencia']==='diaria'?'selected':'' ?>>Diaria</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label">Peso *</label>
                        <input name="peso" type="number" step="0.01" min="0" class="form-control" value="<?= esc($actividad['peso']) ?>" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label">Activa</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="activa" value="1" id="sw_activa" <?= $actividad['activa']?'checked':'' ?>>
                            <label class="form-check-label" for="sw_activa">Habilitada</label>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-cycloid"><i class="bi bi-check2"></i> Guardar</button>
                    <a href="<?= base_url('rutinas/actividades') ?>" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= base_url('sw_rutinas.js') ?>').catch(()=>{});
}
</script>
<?php helper('rutinas'); echo rutinas_floating_back(); ?>
</body>
</html>

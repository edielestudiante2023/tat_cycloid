<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nuevo empleado</title>
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
<div class="container py-3 py-md-4" style="max-width:640px;">
    <div class="card card-r">
        <div class="header-r"><h1><i class="bi bi-person-plus"></i> Nuevo empleado</h1></div>
        <div class="card-body">
            <div class="alert alert-info small">
                <i class="bi bi-info-circle"></i>
                El empleado podrá entrar con el email y contraseña que crees y verá solo su checklist de rutinas del día.
            </div>

            <?php if (session('errors')): ?>
                <div class="alert alert-danger"><ul class="mb-0">
                    <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
                </ul></div>
            <?php endif; ?>

            <p class="small text-muted">
                Local: <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
            </p>

            <form method="post" action="<?= base_url('empleados/add') ?>">
                <input type="hidden" name="id_cliente" value="<?= (int)$idCliente ?>">
                <div class="mb-3">
                    <label class="form-label">Nombre completo *</label>
                    <input name="nombre_completo" class="form-control" value="<?= esc(old('nombre_completo')) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email (para login) *</label>
                    <input name="email" type="email" class="form-control" value="<?= esc(old('email')) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña provisional *</label>
                    <input name="password" type="text" class="form-control" minlength="6" required>
                    <div class="form-text">Mínimo 6 caracteres. Se la compartes al empleado.</div>
                </div>
                <hr>
                <div class="d-flex gap-2">
                    <button class="btn btn-c"><i class="bi bi-check2"></i> Crear empleado</button>
                    <a href="<?= base_url('empleados?cliente='.$idCliente) ?>" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php helper('rutinas'); echo rutinas_floating_back(); ?>
</body>
</html>

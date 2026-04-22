<?php
ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-fire-extinguisher me-2"></i> Permisos de Bomberos</h1>
    <a href="<?= base_url('client/dashboard') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('msg') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <h5 style="color:#c9541a;">Solicitudes por año</h5>
        <p class="text-muted small mb-3">
            El permiso de bomberos debe renovarse anualmente. Cada año tiene su propio expediente con los documentos requeridos.
        </p>

        <form method="post" action="<?= base_url('client/bomberos/nuevo-anio') ?>" class="d-flex gap-2 align-items-center mb-3">
            <input type="number" name="anio" class="form-control" min="2020" max="2100"
                   value="<?= (int)date('Y') + 1 ?>" style="max-width:120px;" required>
            <button type="submit" class="btn" style="background:#ee6c21; color:#fff;">
                <i class="fas fa-plus me-1"></i> Abrir expediente
            </button>
        </form>

        <ul class="list-group list-group-flush">
            <?php foreach ($anios as $a): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <strong style="font-size:18px;"><?= esc($a['anio']) ?></strong>
                        <span class="badge ms-2
                            <?= $a['estado'] === 'aprobado'  ? 'bg-success'
                              : ($a['estado'] === 'radicado' ? 'bg-primary'
                              : ($a['estado'] === 'rechazado' ? 'bg-danger' : 'bg-warning text-dark')) ?>">
                            <?= esc(ucfirst($a['estado'])) ?>
                        </span>
                        <div class="text-muted small">
                            <?= esc($a['municipio']) ?>, <?= esc($a['departamento']) ?>
                            <?php if (!empty($a['numero_radicado'])): ?>
                                &middot; Radicado: <?= esc($a['numero_radicado']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?= base_url('client/bomberos/expediente/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary" style="border-color:#ee6c21;color:#ee6c21;">
                        <i class="fas fa-folder-open"></i> Ver expediente
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => 'Permisos Bomberos',
    'content' => $content,
]);

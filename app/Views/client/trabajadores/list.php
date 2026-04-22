<?php
$content = '';
ob_start();
?>

<style>
/* En movil, los botones de accion (Soportes/Editar/Eliminar) se apilan vertical para mejor toque con dedo */
@media (max-width: 768px) {
    .btn-group-responsive {
        display: flex !important;
        flex-direction: column;
        gap: 6px;
        width: 100%;
    }
    .btn-group-responsive .btn {
        border-radius: 8px !important;
        margin-left: 0 !important;
        width: 100%;
    }
}
</style>

<div class="page-header">
    <h1><i class="fas fa-users me-2"></i> Trabajadores</h1>
    <a href="<?= base_url('client/trabajadores/nuevo') ?>" class="btn-back" style="background:rgba(255,255,255,.25);">
        <i class="fas fa-user-plus me-1"></i> Agregar
    </a>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('msg') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<?php if (empty($trabajadores)): ?>
    <div class="card p-4 text-center">
        <i class="fas fa-user-slash" style="font-size:48px; color:#ccc;"></i>
        <h5 class="mt-3 text-muted">Aún no hay trabajadores registrados</h5>
        <p class="text-muted">Agrega el primero para poder cargar sus soportes.</p>
        <a href="<?= base_url('client/trabajadores/nuevo') ?>" class="btn btn-primary mt-2" style="background:#ee6c21; border:none;">
            <i class="fas fa-user-plus me-1"></i> Agregar trabajador
        </a>
    </div>
<?php else: ?>
    <?php foreach ($trabajadores as $t): ?>
        <?php $inactivo = (int)$t['activo'] === 0; ?>
        <div class="card mb-3" style="<?= $inactivo ? 'opacity:0.55;' : '' ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1" style="color:#c9541a;">
                            <i class="fas fa-user-circle me-1"></i> <?= esc($t['nombre']) ?>
                            <?php if ($inactivo): ?>
                                <span class="badge bg-secondary ms-2"><i class="fas fa-ban me-1"></i>ANULADO</span>
                            <?php endif; ?>
                            <?php if ((int)$t['manipula_alimentos'] === 1): ?>
                                <span class="badge bg-warning text-dark ms-1"><i class="fas fa-utensils"></i> Manipulador</span>
                            <?php endif; ?>
                        </h5>
                        <div class="text-muted small">
                            <i class="fas fa-id-card me-1"></i> <?= esc($t['tipo_id']) ?> <?= esc($t['numero_id']) ?>
                            <?php if (!empty($t['cargo'])): ?>
                                &middot; <i class="fas fa-briefcase ms-1 me-1"></i> <?= esc($t['cargo']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-paperclip me-1"></i> <?= (int)$t['total_soportes'] ?> soporte(s) cargado(s)
                        </div>
                    </div>
                    <div class="btn-group btn-group-responsive">
                        <a href="<?= base_url('client/trabajadores/' . $t['id_trabajador'] . '/soportes') ?>"
                           class="btn btn-sm btn-outline-primary" style="border-color:#ee6c21; color:#ee6c21;">
                            <i class="fas fa-folder-open"></i> Soportes
                        </a>
                        <?php if (!$inactivo): ?>
                            <a href="<?= base_url('client/trabajadores/' . $t['id_trabajador'] . '/editar') ?>"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-pen"></i> Editar
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-anular-url="<?= base_url('client/trabajadores/' . $t['id_trabajador'] . '/eliminar') ?>"
                                    data-anular-titulo="Trabajador: <?= esc($t['nombre']) ?>">
                                <i class="fas fa-ban"></i>
                            </button>
                        <?php endif; ?>
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
    'title'   => 'Trabajadores',
    'content' => $content,
]);

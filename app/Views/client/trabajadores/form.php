<?php
$isEdit = !empty($trabajador);
$action = $isEdit
    ? base_url('client/trabajadores/' . $trabajador['id_trabajador'] . '/actualizar')
    : base_url('client/trabajadores/guardar');

ob_start();
?>

<div class="page-header">
    <h1>
        <i class="fas fa-<?= $isEdit ? 'user-edit' : 'user-plus' ?> me-2"></i>
        <?= $isEdit ? 'Editar Trabajador' : 'Nuevo Trabajador' ?>
    </h1>
    <a href="<?= base_url('client/trabajadores') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form action="<?= $action ?>" method="post">
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" required
                           value="<?= esc($trabajador['nombre'] ?? old('nombre')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo ID</label>
                    <select name="tipo_id" class="form-select">
                        <?php foreach (['CC','CE','TI','PA','RC'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($trabajador['tipo_id'] ?? 'CC') === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Número de identificación <span class="text-danger">*</span></label>
                    <input type="text" name="numero_id" class="form-control" required
                           value="<?= esc($trabajador['numero_id'] ?? old('numero_id')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cargo</label>
                    <input type="text" name="cargo" class="form-control"
                           placeholder="Ej: Panadero, Cajera, Auxiliar"
                           value="<?= esc($trabajador['cargo'] ?? old('cargo')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control"
                           value="<?= esc($trabajador['telefono'] ?? old('telefono')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo de contrato <small class="text-muted">(puede quedar vacío si es autoempleado)</small></label>
                    <select name="tipo_contrato" class="form-select">
                        <?php $tc = $trabajador['tipo_contrato'] ?? ''; ?>
                        <option value="" <?= $tc === '' ? 'selected' : '' ?>>-- No aplica / sin contrato --</option>
                        <option value="termino_fijo"       <?= $tc === 'termino_fijo' ? 'selected' : '' ?>>Término fijo</option>
                        <option value="termino_indefinido" <?= $tc === 'termino_indefinido' ? 'selected' : '' ?>>Término indefinido</option>
                        <option value="obra_labor"         <?= $tc === 'obra_labor' ? 'selected' : '' ?>>Obra o labor</option>
                        <option value="prestacion_serv"    <?= $tc === 'prestacion_serv' ? 'selected' : '' ?>>Prestación de servicios</option>
                        <option value="aprendizaje"        <?= $tc === 'aprendizaje' ? 'selected' : '' ?>>Contrato de aprendizaje</option>
                        <option value="autoempleado"       <?= $tc === 'autoempleado' ? 'selected' : '' ?>>Autoempleado / propietario</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha de ingreso</label>
                    <input type="date" name="fecha_ingreso" class="form-control"
                           value="<?= esc($trabajador['fecha_ingreso'] ?? old('fecha_ingreso')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha de terminación <small class="text-muted">(si ya no trabaja)</small></label>
                    <input type="date" name="fecha_terminacion" class="form-control"
                           value="<?= esc($trabajador['fecha_terminacion'] ?? '') ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="manipula_alimentos" value="1" id="manAlim"
                               <?= !empty($trabajador['manipula_alimentos']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="manAlim">
                            <strong>Manipula alimentos</strong>
                        </label>
                    </div>
                </div>

                <?php if ($isEdit): ?>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="activo" class="form-select">
                            <option value="1" <?= (int)($trabajador['activo'] ?? 1) === 1 ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= (int)($trabajador['activo'] ?? 1) === 0 ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary" style="background:#ee6c21; border:none;">
                    <i class="fas fa-save me-1"></i> <?= $isEdit ? 'Actualizar' : 'Guardar y continuar' ?>
                </button>
                <a href="<?= base_url('client/trabajadores') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => $isEdit ? 'Editar Trabajador' : 'Nuevo Trabajador',
    'content' => $content,
]);

<?php
ob_start();
?>
<div class="page-header">
    <h1><i class="fas fa-truck-ramp-box me-2"></i> Nueva recepción</h1>
    <a href="<?= base_url('client/recepcion-mp') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form action="<?= base_url('client/recepcion-mp/guardar') ?>" method="post" enctype="multipart/form-data">

    <!-- PROVEEDOR -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 style="color:#c9541a;"><i class="fas fa-user me-1"></i> Proveedor</h6>
            <?php if (empty($proveedores)): ?>
                <div class="alert alert-warning small mb-2">
                    No tienes proveedores registrados.
                    <a href="<?= base_url('client/recepcion-mp/proveedores') ?>">Agregar proveedor primero</a>
                    o escribe el nombre libre abajo.
                </div>
            <?php endif; ?>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small">Proveedor del catálogo</label>
                    <select name="id_proveedor" class="form-select">
                        <option value="">— seleccione o escriba libre abajo —</option>
                        <?php foreach ($proveedores as $p): ?>
                            <option value="<?= $p['id_proveedor'] ?>"
                                    data-nombre="<?= esc($p['nombre']) ?>">
                                <?= esc($p['nombre']) ?>
                                <?php if (!empty($p['nit'])): ?> — NIT <?= esc($p['nit']) ?><?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">O nombre de proveedor no registrado</label>
                    <input type="text" name="proveedor_nombre_libre" class="form-control"
                           placeholder="Ej: Panadería El Molino">
                </div>
            </div>
        </div>
    </div>

    <!-- PRODUCTO -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 style="color:#c9541a;"><i class="fas fa-box me-1"></i> Producto recibido</h6>
            <div class="row g-2">
                <div class="col-md-8">
                    <label class="form-label small">Producto <span class="text-danger">*</span></label>
                    <input type="text" name="producto" class="form-control" required
                           placeholder="Ej: Leche entera, Pan francés, Carne molida">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Categoría</label>
                    <select name="categoria" id="selCategoria" class="form-select" required>
                        <?php foreach ($categorias as $k => $v): ?>
                            <option value="<?= $k ?>"><?= esc($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Cantidad</label>
                    <input type="number" step="0.01" name="cantidad" class="form-control" inputmode="decimal">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Unidad</label>
                    <select name="unidad" class="form-select">
                        <option value="">--</option>
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                        <option value="lt">lt</option>
                        <option value="ml">ml</option>
                        <option value="und">und</option>
                        <option value="caja">caja</option>
                        <option value="paca">paca</option>
                        <option value="bolsa">bolsa</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Lote</label>
                    <input type="text" name="lote" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Reg. sanitario (INVIMA)</label>
                    <input type="text" name="registro_sanitario" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Fecha vencimiento del producto</label>
                    <input type="date" name="fecha_vencimiento_producto" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">N° Factura</label>
                    <input type="text" name="numero_factura" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <!-- CONTROL SANITARIO -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 style="color:#c9541a;"><i class="fas fa-clipboard-check me-1"></i> Control sanitario</h6>
            <div class="row g-2 align-items-end">
                <div class="col-md-6" id="divTemperatura" style="display:none;">
                    <label class="form-label small"><i class="fas fa-thermometer-half me-1"></i> Temperatura de recepción (°C)</label>
                    <input type="number" step="0.1" name="temperatura_recepcion" class="form-control" inputmode="decimal"
                           placeholder="Ej: 4.5 (refrigerado), -18 (congelado)">
                </div>
                <div class="col-md-6" id="divFotoTemp" style="display:none;">
                    <label class="form-label small text-danger">
                        <i class="fas fa-camera me-1"></i> Foto termómetro <span class="fw-bold">*</span>
                        <small class="text-muted">(si registra temperatura)</small>
                    </label>
                    <input type="file" name="foto_temperatura" class="form-control" accept="image/*" capture="environment">
                </div>
            </div>
            <div class="mt-3 d-flex gap-4 flex-wrap">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="empaque_ok" value="1" id="chkEmpaque" checked>
                    <label class="form-check-label" for="chkEmpaque">Empaque íntegro</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="producto_ok" value="1" id="chkProd" checked>
                    <label class="form-check-label" for="chkProd">Producto con apariencia, color y olor adecuados</label>
                </div>
            </div>
        </div>
    </div>

    <!-- FOTOS -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 style="color:#c9541a;"><i class="fas fa-camera me-1"></i> Evidencia fotográfica</h6>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small text-danger">
                        Foto del producto <span class="fw-bold">*</span>
                    </label>
                    <input type="file" name="foto_producto" class="form-control" accept="image/*" capture="environment" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Foto de la factura <span class="text-muted">(opcional)</span></label>
                    <input type="file" name="foto_factura" class="form-control" accept="image/*" capture="environment">
                </div>
            </div>
        </div>
    </div>

    <!-- DECISIÓN -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 style="color:#c9541a;"><i class="fas fa-gavel me-1"></i> Decisión final</h6>
            <div class="btn-group w-100 mb-2" role="group">
                <input type="radio" class="btn-check" name="aceptado" id="acepta_si" value="1" autocomplete="off" checked>
                <label class="btn btn-outline-success" for="acepta_si">
                    <i class="fas fa-check"></i> Aceptar
                </label>
                <input type="radio" class="btn-check" name="aceptado" id="acepta_no" value="0" autocomplete="off">
                <label class="btn btn-outline-danger" for="acepta_no">
                    <i class="fas fa-times"></i> Rechazar
                </label>
            </div>
            <div id="divMotivo" style="display:none;">
                <label class="form-label small text-danger">Motivo del rechazo <span class="fw-bold">*</span></label>
                <textarea name="motivo_rechazo" rows="2" class="form-control"
                          placeholder="Ej: Producto vencido, empaque roto, temperatura fuera de rango"></textarea>
            </div>
            <div class="mt-3">
                <label class="form-label small">Observaciones</label>
                <textarea name="observaciones" rows="2" class="form-control"></textarea>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-lg w-100" style="background:#ee6c21;color:#fff;">
        <i class="fas fa-save me-1"></i> Registrar recepción
    </button>
</form>

<script>
// Categorías que requieren control de temperatura
var CATS_TEMP = <?= json_encode($categoriasConTemp) ?>;

function toggleTemp() {
    var cat = document.getElementById('selCategoria').value;
    var show = CATS_TEMP.indexOf(cat) !== -1;
    document.getElementById('divTemperatura').style.display = show ? 'block' : 'none';
    document.getElementById('divFotoTemp').style.display = show ? 'block' : 'none';
}
document.getElementById('selCategoria').addEventListener('change', toggleTemp);
toggleTemp();

// Toggle motivo rechazo
function toggleMotivo() {
    var no = document.getElementById('acepta_no').checked;
    document.getElementById('divMotivo').style.display = no ? 'block' : 'none';
}
document.getElementById('acepta_si').addEventListener('change', toggleMotivo);
document.getElementById('acepta_no').addEventListener('change', toggleMotivo);

// Sincronizar proveedor catalogado con texto libre
document.querySelector('select[name="id_proveedor"]').addEventListener('change', function() {
    var nombre = this.options[this.selectedIndex].getAttribute('data-nombre');
    var inp = document.querySelector('input[name="proveedor_nombre_libre"]');
    if (this.value && nombre) inp.value = '';
});

// Alerta si marca aceptar con empaque/producto no OK
document.getElementById('acepta_si').addEventListener('change', function() {
    if (this.checked) {
        var empOk = document.getElementById('chkEmpaque').checked;
        var prodOk = document.getElementById('chkProd').checked;
        if (!empOk || !prodOk) {
            alert('⚠️ Atención: marcaste el producto como aceptado, pero el empaque o producto NO están OK. Verifica si realmente es aceptable.');
        }
    }
});
</script>

<?php
$content = ob_get_clean();
echo view('client/inspecciones/layout', [
    'title'   => 'Nueva recepción MP',
    'content' => $content,
]);

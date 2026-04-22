<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asignar <?= esc($titulo) ?> — <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; min-height: 100vh; }
        .page-wrap { max-width: 900px; margin: 30px auto; padding: 0 16px 60px; }
        .header-card { background: <?= esc($headerBg) ?>; color:#fff; padding:24px; border-radius:14px; }
        .item-row { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid #f0f0f0; cursor: pointer; }
        .item-row:hover { background: #fafbfc; }
        .item-row.inactivo { opacity: .55; cursor: default; }
        .item-row input[type=checkbox] { transform: scale(1.3); }
        .item-row .icono { width: 32px; color: #ee6c21; text-align:center; font-size: 18px; }
        .item-row .nombre { font-weight: 600; color: #333; }
        .item-row .descripcion { font-size: 13px; color: #6c757d; }
        .item-row .orden-badge { background:#f0f0f0; color:#666; font-size:11px; padding:2px 8px; border-radius:10px; }
        .sticky-actions {
            position: sticky; bottom: 0; background: #fff; border-top: 2px solid #ee6c21;
            padding: 14px 20px; margin: 0 -16px -16px; border-radius: 0 0 12px 12px;
            box-shadow: 0 -4px 12px rgba(0,0,0,.06);
            display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .total-marcados { font-size: 14px; color: #333; }
        .total-marcados strong { color: #ee6c21; font-size: 18px; }
    </style>
</head>
<body>
<div class="page-wrap">
    <div class="header-card mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="mb-0"><i class="fas <?= esc($icono) ?> me-2"></i> <?= esc($titulo) ?></h3>
            <small>Selecciona los items que verá <strong><?= esc($cliente['nombre_cliente']) ?></strong> en sus inspecciones.</small>
        </div>
        <a href="<?= base_url('admin/' . $slug) ?>" class="btn btn-outline-light">
            <i class="fas fa-arrow-left me-1"></i> Volver al catálogo
        </a>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i> <?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('admin/' . $slug . '/asignar/' . $cliente['id_cliente'] . '/guardar') ?>" id="formAsignar">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex gap-2 align-items-center flex-grow-1">
                    <i class="fas fa-search text-muted"></i>
                    <input type="text" id="buscador" class="form-control form-control-sm" style="max-width:300px;"
                           placeholder="Filtrar por nombre…">
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelAll">
                        <i class="fas fa-check-square me-1"></i> Marcar todo
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnUnsel">
                        <i class="far fa-square me-1"></i> Desmarcar todo
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($items)): ?>
                    <div class="p-4 text-center text-muted">No hay items en el catálogo. Crea items primero en el catálogo maestro.</div>
                <?php endif; ?>
                <?php foreach ($items as $i):
                    $inactivo = (int)$i['activo'] === 0;
                    $marcado  = in_array((int)$i['id_item'], $asignados, true);
                ?>
                    <label class="item-row <?= $inactivo ? 'inactivo' : '' ?>" data-nombre="<?= esc(strtolower($i['nombre'])) ?>">
                        <input type="checkbox" name="id_items[]" value="<?= (int)$i['id_item'] ?>"
                               <?= $marcado ? 'checked' : '' ?>
                               <?= $inactivo ? 'disabled' : '' ?>
                               class="form-check-input checkbox-item">
                        <div class="icono">
                            <?php if (!empty($i['icono'])): ?>
                                <i class="fas <?= esc($i['icono']) ?>"></i>
                            <?php else: ?>
                                <i class="far fa-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div style="flex:1;">
                            <div class="nombre">
                                <?= esc($i['nombre']) ?>
                                <?php if ($inactivo): ?>
                                    <span class="badge bg-secondary ms-1">Inactivo</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($i['descripcion'])): ?>
                                <div class="descripcion"><?= esc($i['descripcion']) ?></div>
                            <?php endif; ?>
                        </div>
                        <span class="orden-badge">Orden <?= (int)$i['orden'] ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="sticky-actions">
                <div class="total-marcados">
                    Seleccionados: <strong id="contador"><?= count($asignados) ?></strong>
                    de <?= count($items) ?> disponibles
                </div>
                <button type="submit" class="btn" style="background:#ee6c21;color:#fff;">
                    <i class="fas fa-save me-1"></i> Guardar asignaciones
                </button>
            </div>
        </div>
    </form>
</div>

<script>
const buscador = document.getElementById('buscador');
const filas = document.querySelectorAll('.item-row');
const contador = document.getElementById('contador');

buscador.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    filas.forEach(f => {
        f.style.display = !q || f.dataset.nombre.includes(q) ? 'flex' : 'none';
    });
});

function actualizarContador() {
    contador.textContent = document.querySelectorAll('.checkbox-item:checked').length;
}
document.querySelectorAll('.checkbox-item').forEach(c => c.addEventListener('change', actualizarContador));

document.getElementById('btnSelAll').addEventListener('click', () => {
    document.querySelectorAll('.checkbox-item:not([disabled])').forEach(c => {
        const row = c.closest('.item-row');
        if (row.style.display !== 'none') c.checked = true;
    });
    actualizarContador();
});
document.getElementById('btnUnsel').addEventListener('click', () => {
    document.querySelectorAll('.checkbox-item:not([disabled])').forEach(c => c.checked = false);
    actualizarContador();
});
</script>
</body>
</html>

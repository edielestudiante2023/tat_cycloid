<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Calendario — Rutinas</title>
<link rel="manifest" href="<?= base_url('manifest_rutinas.json') ?>">
<meta name="theme-color" content="#1c2437">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{background:#f4f6f9}
.card-rutinas{border:0;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06)}
.header-rutinas{background:linear-gradient(135deg,#1c2437,#2d3a5e);color:#fff;border-radius:12px 12px 0 0;padding:18px}
.header-rutinas h1{color:#bd9751;margin:0;font-size:1.4rem}
.matriz{font-size:.75rem}
.matriz th,.matriz td{text-align:center;vertical-align:middle;padding:4px 2px;border:1px solid #e5e5e5}
.matriz th.act,.matriz th.emp{text-align:left;padding-left:8px;min-width:180px;position:sticky;left:0;background:#1c2437;color:#fff;z-index:2}
.matriz td.act,.matriz td.emp{text-align:left;padding-left:8px;position:sticky;left:0;background:#fff;font-weight:500;z-index:1}
.matriz td.cell-done{background:#d4edda !important;color:#155724;font-weight:bold}
.matriz td.cell-miss{background:#f8d7da !important;color:#721c24;font-weight:bold}
.matriz td.cell-na{background:#f0f0f0 !important;color:#999}
.matriz td.pct-g{background:#d4edda !important;color:#155724;font-weight:bold}
.matriz td.pct-y{background:#fff3cd !important;color:#856404;font-weight:bold}
.matriz td.pct-r{background:#f8d7da !important;color:#721c24;font-weight:bold}
.score{font-weight:bold}
.score-g{color:#155724}.score-y{color:#856404}.score-r{color:#721c24}
.legend{font-size:.8rem}
.legend span{display:inline-block;padding:2px 8px;margin-right:5px;border-radius:4px}
.legend .lg-done{background:#d4edda;color:#155724}
.legend .lg-miss{background:#f8d7da;color:#721c24}
.legend .lg-na{background:#f0f0f0;color:#999}
.badge-summary{font-size:1rem;padding:8px 12px}
.badge-universo{background:#1c2437;color:#bd9751;padding:6px 12px;border-radius:6px;font-weight:600;font-size:.85rem}
.emp-card{background:#fff;border-radius:10px;padding:14px;margin-bottom:18px;box-shadow:0 1px 6px rgba(0,0,0,.04)}
.emp-card h6{margin:0;color:#1c2437}
.emp-card .meta{font-size:.8rem;color:#666}
@media (max-width: 576px){
  .header-rutinas h1{font-size:1.1rem}
  .matriz th.act,.matriz th.emp{min-width:140px}
  .matriz{font-size:.7rem}
}
</style>
</head>
<body>
<div class="container-fluid py-3">
    <div class="card card-rutinas mb-3">
        <div class="header-rutinas d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1><i class="bi bi-calendar3"></i> Calendario de rutinas</h1>
            <a href="<?= base_url(session('role') === 'client' ? 'dashboard' : 'admindashboard') ?>" class="btn btn-sm btn-outline-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Mes</label>
                    <select name="mes" class="form-select form-select-sm">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $i === $mes ? 'selected' : '' ?>>
                                <?= ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'][$i-1] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Año</label>
                    <input type="number" name="anio" class="form-control form-control-sm" value="<?= $anio ?>">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small mb-1">Cliente (local)</label>
                    <select name="cliente" class="form-select form-select-sm" <?= session('role') === 'client' ? 'disabled' : '' ?>>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= (int)$c['id_cliente'] ?>" <?= (int)$c['id_cliente'] === $idCliente ? 'selected' : '' ?>>
                                <?= esc($c['nombre_cliente']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">Empleado (opcional)</label>
                    <select name="empleado" class="form-select form-select-sm">
                        <option value="0">— Todos (dueño + empleados) —</option>
                        <?php foreach ($empleados as $e): $isDueno = ($e['tipo_usuario'] ?? '') === 'client'; ?>
                            <option value="<?= (int)$e['id_usuario'] ?>" <?= (int)$e['id_usuario'] === $idEmpleado ? 'selected' : '' ?>>
                                <?= $isDueno ? '👤 ' : '👷 ' ?><?= esc($e['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-1">
                    <button class="btn btn-sm btn-dark w-100"><i class="bi bi-funnel"></i></button>
                </div>
            </form>

            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                <div class="legend">
                    <span class="lg-done">✓ hecho</span>
                    <span class="lg-miss">✗ no hecho</span>
                    <span class="lg-na">— futuro/NA</span>
                </div>
            </div>

            <?php if ($idEmpleado > 0 && !empty($actividades)): /* ─── Modo por empleado ─── */ ?>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-person"></i> <?= esc($empleado['nombre_completo'] ?? '') ?></h5>
                    <span class="badge-universo">Universo = <?= number_format($pesoTotal, 2) ?> pts (100%)</span>
                    <span class="badge badge-summary <?= $puntajeMensual>=90?'bg-success':($puntajeMensual>=60?'bg-warning text-dark':'bg-danger') ?>">
                        <?= $nombreMes ?>: <?= $puntajeMensual ?>%
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="matriz table table-sm table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th class="act">Actividad</th>
                                <?php foreach ($diasHabiles as $d): ?>
                                    <th><?= $d['dia'] ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividades as $a): ?>
                                <tr>
                                    <td class="act"><?= esc($a['nombre']) ?> <span class="text-muted small">(<?= esc($a['peso']) ?>)</span></td>
                                    <?php foreach ($diasHabiles as $d): ?>
                                        <?php
                                        $fecha = $d['fecha'];
                                        $reg   = $registros[$fecha][$a['id_actividad']] ?? null;
                                        if ($fecha > date('Y-m-d')) { $cls='cell-na'; $ico='—'; }
                                        elseif ($reg && (int)$reg['completada'] === 1) { $cls='cell-done'; $ico='✓'; }
                                        else { $cls='cell-miss'; $ico='✗'; }
                                        ?>
                                        <td class="<?= $cls ?>"><?= $ico ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-light">
                                <td class="act"><strong>% Diario</strong></td>
                                <?php foreach ($diasHabiles as $d): $f=$d['fecha']; $p=$puntajeDiario[$f] ?? 0; ?>
                                    <?php if ($f > date('Y-m-d')): ?>
                                        <td class="cell-na">—</td>
                                    <?php else: ?>
                                        <td class="<?= $p>=90?'pct-g':($p>=60?'pct-y':'pct-r') ?>"><?= $p ?>%</td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($puntajeSemanal)): ?>
                    <h6 class="mt-3 mb-2">Resumen semanal (ISO)</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($puntajeSemanal as $sem => $pct): ?>
                            <span class="badge <?= $pct>=90?'bg-success':($pct>=60?'bg-warning text-dark':'bg-danger') ?>">
                                Sem <?= $sem ?>: <?= $pct ?>%
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php elseif ($idEmpleado > 0): ?>
                <div class="alert alert-info">Este empleado no tiene actividades asignadas.</div>

            <?php elseif (!empty($resumenPorEmpleado)): /* ─── Modo por cliente (todos empleados) ─── */ ?>
                <div class="table-responsive">
                    <table class="matriz table table-sm table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th class="emp">Empleado</th>
                                <?php foreach ($diasHabiles as $d): ?>
                                    <th><?= $d['dia'] ?></th>
                                <?php endforeach; ?>
                                <th>% Mes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resumenPorEmpleado as $row): ?>
                                <tr>
                                    <td class="emp">
                                        <a href="<?= base_url('rutinas/calendario?mes='.$mes.'&anio='.$anio.'&cliente='.$idCliente.'&empleado='.(int)$row['empleado']['id_usuario']) ?>" class="text-decoration-none text-dark">
                                            <strong><?= esc($row['empleado']['nombre_completo']) ?></strong>
                                            <br><small class="text-muted"><?= number_format($row['pesoTotal'], 2) ?> pts</small>
                                        </a>
                                    </td>
                                    <?php foreach ($diasHabiles as $d): $f=$d['fecha']; $p=$row['puntajeDiario'][$f] ?? 0; ?>
                                        <?php if ($f > date('Y-m-d')): ?>
                                            <td class="cell-na">—</td>
                                        <?php elseif ((int)$row['pesoTotal'] === 0): ?>
                                            <td class="cell-na">—</td>
                                        <?php else: ?>
                                            <td class="<?= $p>=90?'pct-g':($p>=60?'pct-y':'pct-r') ?>"><?= $p ?>%</td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <td class="<?= $row['puntajeMensual']>=90?'pct-g':($row['puntajeMensual']>=60?'pct-y':'pct-r') ?>">
                                        <?= $row['puntajeMensual'] ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="small text-muted mt-2"><i class="bi bi-info-circle"></i> Haz click sobre el nombre de un empleado para ver su detalle.</p>

            <?php elseif (!empty($empleados)): ?>
                <div class="alert alert-warning">Los empleados de este local no tienen actividades asignadas todavía.
                    <a href="<?= base_url('rutinas/asignaciones?cliente='.$idCliente) ?>">Asignar ahora</a>.
                </div>

            <?php else: ?>
                <div class="alert alert-info">Este cliente no tiene empleados registrados.
                    <a href="<?= base_url('empleados?cliente='.$idCliente) ?>">Crear el primero</a>.
                </div>
            <?php endif; ?>
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

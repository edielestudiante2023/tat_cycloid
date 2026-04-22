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
.matriz th.act{text-align:left;padding-left:8px;min-width:160px;position:sticky;left:0;background:#1c2437;color:#fff;z-index:2}
.matriz td.act{text-align:left;padding-left:8px;position:sticky;left:0;background:#fff;font-weight:500;z-index:1}
.cell-done{background:#d4edda;color:#155724}
.cell-miss{background:#f8d7da;color:#721c24}
.cell-na{background:#f0f0f0;color:#999}
.score{font-weight:bold}
.score-g{color:#155724}
.score-y{color:#856404}
.score-r{color:#721c24}
.legend{font-size:.8rem}
.legend span{display:inline-block;padding:2px 8px;margin-right:5px;border-radius:4px}
.badge-summary{font-size:1rem;padding:8px 12px}
@media (max-width: 576px){
  .header-rutinas h1{font-size:1.1rem}
  .matriz th.act{min-width:130px}
  .matriz{font-size:.7rem}
}
</style>
</head>
<body>
<div class="container-fluid py-3">
    <div class="card card-rutinas mb-3">
        <div class="header-rutinas d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1><i class="bi bi-calendar3"></i> Calendario de rutinas</h1>
            <a href="<?= base_url('admindashboard') ?>" class="btn btn-sm btn-outline-light">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-6 col-md-3">
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
                <div class="col-12 col-md-5">
                    <label class="form-label small mb-1">Usuario</label>
                    <select name="usuario" class="form-select form-select-sm">
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= (int)$u['id_usuario'] ?>" <?= (int)$u['id_usuario'] === $usuarioId ? 'selected' : '' ?>>
                                <?= esc($u['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-sm btn-dark w-100"><i class="bi bi-funnel"></i> Aplicar</button>
                </div>
            </form>

            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                <div class="legend">
                    <span class="cell-done">✓ hecho</span>
                    <span class="cell-miss">✗ no hecho</span>
                    <span class="cell-na">— futuro/NA</span>
                </div>
                <div class="ms-auto">
                    <span class="badge badge-summary <?= $puntajeMensual>=90?'bg-success':($puntajeMensual>=60?'bg-warning text-dark':'bg-danger') ?>">
                        <?= $nombreMes ?> <?= $anio ?>: <?= $puntajeMensual ?>%
                    </span>
                </div>
            </div>

            <?php if (empty($actividades)): ?>
                <div class="alert alert-info">Sin actividades asignadas para este usuario. Asigna alguna en
                    <a href="<?= base_url('rutinas/asignaciones') ?>">Asignaciones</a>.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="matriz table table-sm table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th class="act">Actividad</th>
                                <?php foreach ($diasHabiles as $d): ?>
                                    <th><?= $d['dia'] ?><br><span class="small text-muted">L M X J V</span></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividades as $a): ?>
                                <tr>
                                    <td class="act"><?= esc($a['nombre']) ?></td>
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
                                    <td>
                                        <?php if ($f > date('Y-m-d')): ?>
                                            <span class="text-muted">—</span>
                                        <?php else: ?>
                                            <span class="score <?= $p>=100?'score-g':($p>=60?'score-y':'score-r') ?>"><?= $p ?>%</span>
                                        <?php endif; ?>
                                    </td>
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
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Stats Uploads - Cycloid TAT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; color: #343a40; }
        .stat-card {
            background: #fff; border-radius: 14px; padding: 22px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            border-left: 4px solid #ee6c21;
        }
        .stat-card .value { font-size: 2rem; font-weight: 700; color: #c9541a; }
        .stat-card .label { font-size: .82rem; color: #6c757d; text-transform: uppercase; letter-spacing: 1px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .card-header { background: linear-gradient(135deg, #c9541a 0%, #ee6c21 100%); color: #fff; border-radius: 12px 12px 0 0; font-weight: 600; }
        table th { font-size: .78rem; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; }
        .size-badge { font-variant-numeric: tabular-nums; }
        .path-cell { font-family: monospace; font-size: .82rem; word-break: break-all; }
    </style>
</head>
<body>

<?php
function fmtBytes(int $b): string {
    if ($b >= 1073741824) return sprintf('%.2f GB', $b / 1073741824);
    if ($b >= 1048576)    return sprintf('%.2f MB', $b / 1048576);
    if ($b >= 1024)       return sprintf('%.1f KB', $b / 1024);
    return $b . ' B';
}
?>

<div class="container my-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-images text-warning"></i> Estadísticas de uploads</h2>
            <small class="text-muted">Directorio <code>public/uploads/</code></small>
        </div>
        <a href="<?= base_url('/dashboardadmin') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= esc($error) ?></div>
    <?php else: ?>

    <!-- Totales -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="label">Total archivos</div>
                <div class="value"><?= number_format($totalFiles) ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <div class="label">Peso total</div>
                <div class="value"><?= fmtBytes($totalBytes) ?></div>
            </div>
        </div>
    </div>

    <!-- Top 20 mas pesados -->
    <div class="card mb-4">
        <div class="card-header py-3">
            <i class="fas fa-weight-hanging me-2"></i> Top 20 archivos más pesados
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Ruta</th>
                        <th class="text-end" style="width: 140px;">Tamaño</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = 1; foreach ($topHeavy as $path => $size): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td class="path-cell"><?= esc($path) ?></td>
                        <td class="text-end size-badge">
                            <?php $class = $size > 2 * 1048576 ? 'text-danger fw-bold' : ($size > 1048576 ? 'text-warning fw-bold' : ''); ?>
                            <span class="<?= $class ?>"><?= fmtBytes($size) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($topHeavy)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-3">Sin archivos</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <!-- Crecimiento mensual -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header py-3">
                    <i class="fas fa-chart-line me-2"></i> Crecimiento mensual (últimos 12)
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th class="text-end">Archivos</th>
                                <th class="text-end">Peso</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($monthly as $ym => $info): ?>
                            <tr>
                                <td><?= esc($ym) ?></td>
                                <td class="text-end"><?= number_format($info['count']) ?></td>
                                <td class="text-end size-badge"><?= fmtBytes($info['bytes']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($monthly)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Sin datos</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Por extension -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header py-3">
                    <i class="fas fa-file me-2"></i> Por extensión
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Ext.</th>
                                <th class="text-end">Archivos</th>
                                <th class="text-end">Peso</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($byExt as $ext => $info): ?>
                            <tr>
                                <td><code>.<?= esc($ext) ?></code></td>
                                <td class="text-end"><?= number_format($info['count']) ?></td>
                                <td class="text-end size-badge"><?= fmtBytes($info['bytes']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 p-3 bg-light rounded">
        <small class="text-muted">
            <i class="fas fa-info-circle"></i>
            Para limpieza retroactiva ejecutar
            <code>php app/SQL/cleanup_uploads_huge.php --apply</code>
            desde la raíz del proyecto.
        </small>
    </div>

    <?php endif; ?>

</div>

</body>
</html>

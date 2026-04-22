<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Solicitud — TAT Cycloid</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { background: #f5f7fa; min-height: 100vh; }
    .page-wrap { max-width: 700px; margin: 80px auto; padding: 0 16px; text-align:center; }
    .card-center { padding: 48px 32px; border-radius: 14px; }
    .big-icon { font-size: 72px; margin-bottom: 16px; }
</style>
</head>
<body>
<div class="page-wrap">
    <?php if (!empty($noEncontrada)): ?>
        <div class="card card-center bg-white">
            <i class="fas fa-question-circle big-icon text-secondary"></i>
            <h3>Solicitud no encontrada</h3>
            <p class="text-muted">El enlace que usaste no corresponde a ninguna solicitud activa.</p>
        </div>
    <?php else: ?>
        <?php
            $estado = $solicitud['estado'];
            $color = $estado === 'aprobada' ? 'success' : 'danger';
            $icon  = $estado === 'aprobada' ? 'fa-check-circle' : 'fa-ban';
            $titulo = $estado === 'aprobada' ? 'Anulación aprobada' : 'Solicitud rechazada';
        ?>
        <div class="card card-center bg-white">
            <i class="fas <?= $icon ?> big-icon text-<?= $color ?>"></i>
            <h3 class="text-<?= $color ?>"><?= $titulo ?></h3>
            <p class="text-muted"><?= esc($etiqueta) ?></p>
            <p>La respuesta fue registrada el <?= date('d/m/Y H:i', strtotime($solicitud['fecha_respuesta'])) ?>.</p>
            <p class="small text-muted">El cliente recibirá una notificación por correo con el resultado.</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Solicitud de anulación — <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { background: #f5f7fa; min-height: 100vh; }
    .page-wrap { max-width: 900px; margin: 40px auto; padding: 0 16px; }
    .header-card { background: linear-gradient(135deg,#ee6c21,#c9541a); color:#fff; padding:28px; border-radius:14px; }
    .just-box { background: #fff3cd; border:1px solid #ffeaa7; padding:18px; border-radius:8px; margin: 18px 0; }
    .foto-thumb { width: 150px; height: 150px; object-fit: cover; border-radius: 6px; border: 2px solid #dee2e6; cursor: pointer; }
    .estado-pendiente { background: #ffc107; color: #000; }
    .estado-aprobada  { background: #198754; color: #fff; }
    .estado-rechazada { background: #dc3545; color: #fff; }
</style>
</head>
<body>
<div class="page-wrap">
    <div class="header-card mb-4">
        <h3 class="mb-1"><i class="fas fa-ban me-2"></i> Solicitud de anulación</h3>
        <small>TAT Cycloid — SG-SST</small>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Datos de la solicitud</strong>
            <?php
                $claseEstado = 'estado-' . $solicitud['estado'];
            ?>
            <span class="badge <?= $claseEstado ?>"><?= strtoupper($solicitud['estado']) ?></span>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-2">
                <div class="col-md-6">
                    <small class="text-muted">Cliente</small>
                    <div><strong><?= esc($cliente['nombre_cliente'] ?? '—') ?></strong></div>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Consultor asignado</small>
                    <div><?= esc($consultor['nombre_consultor'] ?? '—') ?></div>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Tipo de registro</small>
                    <div><strong><?= esc($etiqueta) ?></strong></div>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Fecha de la solicitud</small>
                    <div><?= date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])) ?></div>
                </div>
            </div>

            <hr>

            <h6 class="mb-2"><i class="fas fa-file-alt me-1"></i> Registro que se desea anular</h6>
            <div class="mb-2"><strong><?= esc($detalle['descripcion']) ?></strong></div>
            <?php if (!empty($detalle['campos'])): ?>
                <table class="table table-sm">
                    <tbody>
                    <?php foreach ($detalle['campos'] as $label => $val): ?>
                        <tr>
                            <td class="text-muted" style="width:35%;"><?= esc($label) ?></td>
                            <td><?= nl2br(esc((string)$val)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($detalle['fotos'])): ?>
                <h6 class="mt-3 mb-2"><i class="fas fa-camera me-1"></i> Evidencias fotográficas</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($detalle['fotos'] as $f): ?>
                        <a href="<?= base_url('uploads/' . $f) ?>" target="_blank">
                            <img src="<?= base_url('uploads/' . $f) ?>" class="foto-thumb" alt="Evidencia">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="just-box mt-3">
                <strong><i class="fas fa-comment-dots me-1"></i> Justificación del cliente</strong>
                <div class="mt-2"><?= nl2br(esc($solicitud['justificacion'])) ?></div>
            </div>

            <?php if ($solicitud['estado'] === 'pendiente'): ?>
                <hr>
                <h6 class="mb-2"><i class="fas fa-gavel me-1"></i> Decisión</h6>
                <form method="post" action="<?= base_url('anular/' . $solicitud['token'] . '/aprobar') ?>"
                      onsubmit="return confirm('¿Confirma la APROBACIÓN? Esta acción eliminará el registro y no se puede deshacer.');"
                      style="display:inline;">
                    <button class="btn btn-success"><i class="fas fa-check me-1"></i> Aprobar anulación</button>
                </form>
                <button class="btn btn-outline-danger ms-2" onclick="document.getElementById('formRechazar').style.display='block';this.style.display='none';">
                    <i class="fas fa-times me-1"></i> Rechazar
                </button>

                <form method="post" action="<?= base_url('anular/' . $solicitud['token'] . '/rechazar') ?>"
                      id="formRechazar" style="display:none;margin-top:12px;">
                    <label class="form-label">Nota (opcional) — motivo del rechazo:</label>
                    <textarea name="nota_respuesta" class="form-control mb-2" rows="3" placeholder="Explique al cliente por qué no aprueba la anulación…"></textarea>
                    <button class="btn btn-danger"><i class="fas fa-ban me-1"></i> Confirmar rechazo</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info mt-3 mb-0">
                    <strong>Esta solicitud ya fue <?= $solicitud['estado'] ?></strong>
                    el <?= date('d/m/Y H:i', strtotime($solicitud['fecha_respuesta'])) ?>.
                    <?php if (!empty($solicitud['nota_respuesta'])): ?>
                        <br><em>Nota:</em> <?= esc($solicitud['nota_respuesta']) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

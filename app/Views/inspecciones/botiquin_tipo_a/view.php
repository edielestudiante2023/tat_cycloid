<?php
// Agrupar elementos por grupo
$grupos = [];
foreach ($elementos as $clave => $config) {
    $grupos[$config['grupo']][$clave] = $config;
}

function estadoColorTipoA(string $estado): string {
    $map = [
        'BUEN ESTADO'     => 'text-success',
        'ESTADO REGULAR'  => 'text-warning',
        'MAL ESTADO'      => 'text-danger',
        'SIN EXISTENCIAS' => 'text-danger',
        'VENCIDO'         => 'text-danger',
        'NO APLICA'       => 'text-muted',
    ];
    return $map[$estado] ?? 'text-dark';
}
?>

<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion de Botiquin Tipo A</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <!-- Datos generales -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha inspeccion</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
                <?php if (!empty($inspeccion['ubicacion_botiquin'])): ?>
                <tr><td class="text-muted">Ubicacion</td><td><?= esc($inspeccion['ubicacion_botiquin']) ?></td></tr>
                <?php endif; ?>
                <tr><td class="text-muted">Tipo botiquin</td><td><?= esc($inspeccion['tipo_botiquin'] ?? 'LONA') ?></td></tr>
                <tr><td class="text-muted">Estado botiquin</td><td><strong class="<?= estadoColorTipoA($inspeccion['estado_botiquin'] ?? 'BUEN ESTADO') ?>"><?= esc($inspeccion['estado_botiquin'] ?? 'BUEN ESTADO') ?></strong></td></tr>
            </table>
        </div>
    </div>

    <!-- Fotos del botiquín -->
    <?php
    $todasFotos = ['foto_1', 'foto_2'];
    $labelsFotos = ['foto_1' => 'Foto 1', 'foto_2' => 'Foto 2'];
    $hayFotos = false;
    foreach ($todasFotos as $c) { if (!empty($inspeccion[$c])) { $hayFotos = true; break; } }
    ?>
    <?php if ($hayFotos): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">FOTOS DEL BOTIQUIN</h6>
            <div class="row g-2">
                <?php foreach ($todasFotos as $campo): ?>
                    <?php if (!empty($inspeccion[$campo])): ?>
                    <div class="col-6">
                        <small class="text-muted d-block" style="font-size:11px;"><?= $labelsFotos[$campo] ?></small>
                        <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded"
                             style="max-height:120px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                             onclick="openPhoto(this.src)">
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Preguntas SI/NO -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CONDICIONES GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <?php
                $preguntas = [
                    'instalado_pared'  => 'Instalado en la pared',
                    'libre_obstaculos' => 'Libre de obstaculos',
                    'lugar_visible'    => 'Localizado en lugar visible',
                    'con_senalizacion' => 'Con senalizacion',
                ];
                foreach ($preguntas as $campo => $label):
                    $valor = $inspeccion[$campo] ?? 'SI';
                    $color = $valor === 'SI' ? 'text-success' : 'text-danger';
                ?>
                <tr>
                    <td class="text-muted" style="width:65%;"><?= $label ?></td>
                    <td><strong class="<?= $color ?>"><?= esc($valor) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- Elementos por grupo -->
    <div class="accordion mb-3" id="accordionViewBot">
        <?php $secNum = 0; foreach ($grupos as $grupoNombre => $items): $secNum++; ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button <?= $secNum > 1 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#vbot_<?= $secNum ?>">
                    <?= esc($grupoNombre) ?> (<?= count($items) ?>)
                </button>
            </h2>
            <div id="vbot_<?= $secNum ?>" class="accordion-collapse collapse <?= $secNum === 1 ? 'show' : '' ?>" data-bs-parent="#accordionViewBot">
                <div class="accordion-body p-2">
                    <table class="table table-sm mb-0" style="font-size:12px;">
                        <thead>
                            <tr style="background:#f8f9fa;">
                                <th>Elemento</th>
                                <th style="width:50px;">Cant.</th>
                                <th style="width:50px;">Min.</th>
                                <th>Estado</th>
                                <?php if ($grupoNombre === 'Antisepticos y soluciones'): ?>
                                <th>Vencimiento</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $clave => $config):
                                $data = $elementosData[$clave] ?? null;
                                $cantidad = (int)($data['cantidad'] ?? 0);
                                $estado = $data['estado'] ?? 'SIN EXISTENCIAS';
                                $cantColor = $cantidad >= $config['min'] ? 'text-success' : 'text-danger';
                            ?>
                            <tr>
                                <td><?= esc($config['label']) ?></td>
                                <td class="<?= $cantColor ?>"><strong><?= $cantidad ?></strong></td>
                                <td class="text-muted"><?= $config['min'] ?></td>
                                <td><span class="<?= estadoColorTipoA($estado) ?>"><?= esc($estado) ?></span></td>
                                <?php if ($config['venc']): ?>
                                <td>
                                    <?php if (!empty($data['fecha_vencimiento'])): ?>
                                        <?php
                                        $fv = $data['fecha_vencimiento'];
                                        $vencido = $fv < date('Y-m-d');
                                        ?>
                                        <span class="<?= $vencido ? 'text-danger fw-bold' : '' ?>"><?= date('d/m/Y', strtotime($fv)) ?></span>
                                        <?php if ($vencido): ?> <i class="fas fa-exclamation-triangle text-danger" style="font-size:10px;"></i><?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php elseif ($grupoNombre === 'Antisepticos y soluciones'): ?>
                                <td>-</td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recomendaciones -->
    <?php if (!empty($inspeccion['recomendaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">RECOMENDACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['recomendaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pendientes generados -->
    <?php
    $pend = null;
    if (!empty($inspeccion['pendientes_generados'])) {
        $decoded = json_decode($inspeccion['pendientes_generados'], true);
        $pend = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
    }
    ?>
    <?php if ($pend !== null): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">PENDIENTES GENERADOS</h6>
            <?php if ($pend['sin_pendientes']): ?>
                <p class="text-success mb-0" style="font-size:13px;"><i class="fas fa-check-circle"></i> Botiquin completo — sin pendientes</p>
            <?php else: ?>
                <div class="table-responsive">
                <table class="table table-sm table-bordered mb-1" style="font-size:12px;">
                    <thead style="background:#f8f9fa;">
                        <tr><th>Elemento</th><th style="width:40px; text-align:center;">Cant.</th><th style="width:40px; text-align:center;">Min.</th><th>Observacion</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pend['items'] as $p): ?>
                        <tr>
                            <td><?= esc($p['elemento']) ?></td>
                            <td class="text-center <?= ($p['cantidad'] !== null && $p['cantidad'] < $p['min']) ? 'text-danger fw-bold' : '' ?>"><?= $p['cantidad'] !== null ? $p['cantidad'] : '—' ?></td>
                            <td class="text-center text-muted"><?= $p['min'] !== null ? $p['min'] : '—' ?></td>
                            <td class="text-danger"><?= esc($p['detalle']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal foto -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-body p-1 text-center">
                    <img id="photoFull" src="" class="img-fluid" style="max-height:80vh;">
                </div>
            </div>
        </div>
    </div>
    <script>
    function openPhoto(src) {
        document.getElementById('photoFull').src = src;
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }
    </script>

    <!-- Acciones -->
    <div class="mb-4">
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf'])): ?>
        <a href="<?= base_url('/inspecciones/botiquin-tipo-a/pdf/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <?php if ($inspeccion['estado'] === 'completo'): ?>
        <a href="<?= base_url('/inspecciones/botiquin-tipo-a/regenerar/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF con la plantilla actual?')">
            <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
        </a>
        <a href="<?= base_url('/inspecciones/botiquin-tipo-a/enviar-email/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Enviar el PDF por email al cliente, consultor y consultor externo?')">
            <i class="fas fa-envelope me-2"></i>Enviar por Email
        </a>
        <?php endif; ?>

        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="<?= base_url('/inspecciones/botiquin-tipo-a/edit/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>

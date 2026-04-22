<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 100px 70px 80px 90px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.3;
            padding: 15px 20px;
        }

        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 10px; }
        .header-table td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
        .header-logo { width: 100px; text-align: center; font-size: 8px; }
        .header-logo img { max-width: 85px; max-height: 50px; }
        .header-title { text-align: center; font-weight: bold; font-size: 9px; }
        .header-code { width: 120px; font-size: 8px; }

        .main-title { text-align: center; font-size: 11px; font-weight: bold; margin: 8px 0 6px; color: #c9541a; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 140px; background: #f7f7f7; }

        .section-title { background: #c9541a; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 3px 4px; font-size: 8px; text-align: center; }
        .data-table td { border: 1px solid #ccc; padding: 2px 4px; font-size: 8px; text-align: center; vertical-align: middle; }

        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }
        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 4px; text-align: justify; }
        .intro-subtitle { font-weight: bold; font-size: 8px; margin: 3px 0 2px; }

        .val-bueno { color: #155724; }
        .val-regular { color: #856404; }
        .val-malo { color: #721c24; }
        .val-na { color: #6c757d; }

        .foto-inline { max-width: 140px; max-height: 100px; border: 1px solid #ccc; }

        .pregunta-si { color: #155724; font-weight: bold; }
        .pregunta-no { color: #721c24; font-weight: bold; }

        .pendientes-box { border: 1px solid #ccc; padding: 6px 8px; font-size: 8px; line-height: 1.5; background: #fefefe; }
    </style>
</head>
<body>

    <!-- HEADER CORPORATIVO -->
    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong style="font-size:7px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Codigo: FT-SST-207<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">FORMATO INSPECCION DE BOTIQUIN TIPO A</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">INSPECCION DE BOTIQUIN TIPO A<br><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION: Resolución 0705 -->
    <div class="section-title">FUNDAMENTACION</div>
    <p class="intro-text">
        De acuerdo con lo contemplado en la Resolucion 0705, el botiquin tipo A se define para establecimientos comerciales con una superficie menor a los 2.000 metros cuadrados y debe contener 14 elementos. El tipo de botiquin puede ser portatil de maletin en lona o fijos.
    </p>
    <p class="intro-subtitle">Fundamentacion de la obligatoriedad:</p>
    <p class="intro-text">
        <strong>Salud publica:</strong> La atencion oportuna y eficaz de lesiones y urgencias medicas reduce la morbilidad y mortalidad. Un botiquin tipo A bien dotado permite prevenir complicaciones derivadas de lesiones menores en el ambiente del establecimiento de comercio.<br>
        <strong>Seguridad y bienestar:</strong> Minimiza los riesgos asociados a accidentes y lesiones en los establecimientos comerciales, protegiendo la integridad fisica de los trabajadores, clientes y visitantes.<br>
        <strong>Aspectos legales:</strong> La Resolucion 0705 establece la obligatoriedad del botiquin tipo A en establecimientos comerciales con superficie menor a 2.000 m2 como requisito minimo de seguridad.<br>
        <strong>Fundamentacion tecnica:</strong> La Norma NTC 4198 establece los requisitos de dotacion y funcionamiento de los botiquines de primeros auxilios, garantizando su calidad, eficacia y seguridad.
    </p>

    <!-- DATOS GENERALES -->
    <div class="section-title">DATOS DE LA INSPECCION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="info-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">CONSULTOR:</td>
            <td><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
            <td class="info-label">UBICACION:</td>
            <td><?= esc($inspeccion['ubicacion_botiquin'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="info-label">TIPO BOTIQUIN:</td>
            <td><?= esc($inspeccion['tipo_botiquin'] ?? 'LONA') ?></td>
            <td class="info-label">ESTADO BOTIQUIN:</td>
            <td><?= esc($inspeccion['estado_botiquin'] ?? 'BUEN ESTADO') ?></td>
        </tr>
    </table>

    <!-- FOTOS DEL BOTIQUÍN -->
    <?php if (!empty($fotosBase64['foto_1']) || !empty($fotosBase64['foto_2'])): ?>
    <table style="width:100%; margin-bottom:8px;">
        <tr>
            <?php if (!empty($fotosBase64['foto_1'])): ?>
            <td style="text-align:center; width:50%;">
                <img src="<?= $fotosBase64['foto_1'] ?>" class="foto-inline"><br>
                <span style="font-size:7px; color:#888;">Foto 1</span>
            </td>
            <?php endif; ?>
            <?php if (!empty($fotosBase64['foto_2'])): ?>
            <td style="text-align:center; width:50%;">
                <img src="<?= $fotosBase64['foto_2'] ?>" class="foto-inline"><br>
                <span style="font-size:7px; color:#888;">Foto 2</span>
            </td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- CONDICIONES GENERALES (4 preguntas SI/NO) -->
    <div class="section-title">CONDICIONES GENERALES</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Instalado en la pared?</td>
            <td class="<?= ($inspeccion['instalado_pared'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['instalado_pared'] ?? 'SI') ?></td>
            <td class="info-label">Libre de obstaculos?</td>
            <td class="<?= ($inspeccion['libre_obstaculos'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['libre_obstaculos'] ?? 'SI') ?></td>
        </tr>
        <tr>
            <td class="info-label">Localizado visible?</td>
            <td class="<?= ($inspeccion['lugar_visible'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['lugar_visible'] ?? 'SI') ?></td>
            <td class="info-label">Con senalizacion?</td>
            <td class="<?= ($inspeccion['con_senalizacion'] ?? 'SI') === 'SI' ? 'pregunta-si' : 'pregunta-no' ?>"><?= esc($inspeccion['con_senalizacion'] ?? 'SI') ?></td>
        </tr>
    </table>

    <!-- TABLA DE ELEMENTOS -->
    <div class="section-title">ELEMENTOS DEL BOTIQUIN TIPO A (14 unidades - Resolucion 0705)</div>

    <?php
    $hoy = date('Y-m-d');

    $gruposPdf = [];
    foreach ($elementos as $clave => $config) {
        $gruposPdf[$config['grupo']][$clave] = $config;
    }

    $colorEstado = function($estado) {
        if ($estado === 'BUEN ESTADO') return 'val-bueno';
        if ($estado === 'ESTADO REGULAR') return 'val-regular';
        if (in_array($estado, ['MAL ESTADO', 'SIN EXISTENCIAS', 'VENCIDO'])) return 'val-malo';
        return 'val-na';
    };
    ?>

    <?php foreach ($gruposPdf as $grupoNombre => $items): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th colspan="<?= ($grupoNombre === 'Antisepticos y soluciones') ? 5 : 4 ?>" style="text-align:left; font-size:9px; background:#d1d5db;">
                    <?= esc($grupoNombre) ?>
                </th>
            </tr>
            <tr>
                <th style="width:45%; text-align:left;">Elemento</th>
                <th style="width:10%;">Cant.</th>
                <th style="width:10%;">Min.</th>
                <th style="width:20%;">Estado</th>
                <?php if ($grupoNombre === 'Antisepticos y soluciones'): ?>
                <th style="width:15%;">Vencimiento</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $clave => $config):
                $data = $elementosData[$clave] ?? null;
                $cantidad = (int)($data['cantidad'] ?? 0);
                $estado = $data['estado'] ?? 'SIN EXISTENCIAS';
                $cantOk = $cantidad >= $config['min'];
            ?>
            <tr>
                <td style="text-align:left;"><?= esc($config['label']) ?></td>
                <td style="font-weight:bold; <?= $cantOk ? 'color:#155724;' : 'color:#721c24;' ?>"><?= $cantidad ?></td>
                <td style="color:#888;"><?= $config['min'] ?></td>
                <td class="<?= $colorEstado($estado) ?>"><?= esc($estado) ?></td>
                <?php if ($config['venc']): ?>
                <td>
                    <?php if (!empty($data['fecha_vencimiento'])): ?>
                        <?php $vencido = $data['fecha_vencimiento'] < $hoy; ?>
                        <span style="<?= $vencido ? 'color:#721c24; font-weight:bold;' : '' ?>"><?= date('d/m/Y', strtotime($data['fecha_vencimiento'])) ?></span>
                    <?php else: ?>
                        <span style="color:#888;">-</span>
                    <?php endif; ?>
                </td>
                <?php elseif ($grupoNombre === 'Antisepticos y soluciones'): ?>
                <td>-</td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endforeach; ?>

    <!-- RECOMENDACIONES -->
    <?php if (!empty($inspeccion['recomendaciones'])): ?>
    <div class="section-title">RECOMENDACIONES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['recomendaciones'])) ?></p>
    <?php endif; ?>

    <!-- PENDIENTES GENERADOS -->
    <?php
    $pend = null;
    if (!empty($inspeccion['pendientes_generados'])) {
        $decoded = json_decode($inspeccion['pendientes_generados'], true);
        $pend = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
    }
    ?>
    <?php if ($pend !== null): ?>
    <div class="section-title">COMPRA DE ELEMENTOS REQUERIDOS / PENDIENTES</div>
    <?php if ($pend['sin_pendientes']): ?>
    <p style="font-size:9px; color:#155724;">Botiquin Tipo A completo — sin pendientes.</p>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th style="text-align:left; width:45%;">Elemento</th>
                <th style="width:10%;">Cant.</th>
                <th style="width:10%;">Min.</th>
                <th style="text-align:left;">Observacion</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pend['items'] as $p): ?>
            <tr>
                <td style="text-align:left;"><?= esc($p['elemento']) ?></td>
                <td style="color:<?= ($p['cantidad'] !== null && $p['cantidad'] < $p['min']) ? '#721c24' : '#333' ?>; font-weight:bold;"><?= $p['cantidad'] !== null ? $p['cantidad'] : '—' ?></td>
                <td style="color:#888;"><?= $p['min'] !== null ? $p['min'] : '—' ?></td>
                <td style="text-align:left; color:#721c24;"><?= esc($p['detalle']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <?php elseif (!empty($inspeccion['pendientes_generados'])): ?>
    <div class="section-title">COMPRA DE ELEMENTOS REQUERIDOS / PENDIENTES</div>
    <div class="pendientes-box">
        <?= nl2br(esc($inspeccion['pendientes_generados'])) ?>
    </div>
    <?php endif; ?>

</body>
</html>

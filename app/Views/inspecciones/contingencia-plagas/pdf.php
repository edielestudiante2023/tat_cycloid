<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 80px 50px 60px 60px; }
    body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; font-size: 10px; color: #333; line-height: 1.5; padding: 0; margin: 0; }
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .header-table td { border: 2px solid #333; padding: 6px 8px; vertical-align: middle; }
    .header-table .logo-cell { width: 100px; text-align: center; }
    .header-table .logo-cell img { max-width: 90px; max-height: 55px; }
    .header-table .title-cell { text-align: center; font-weight: bold; font-size: 10px; }
    .header-table .code-cell { width: 120px; font-size: 9px; }
    .main-title { text-align: center; font-weight: bold; font-size: 13px; color: #c9541a; margin: 20px 0 5px; }
    .subtitle { text-align: center; font-weight: bold; font-size: 11px; color: #333; margin-bottom: 15px; }
    .section-title { font-weight: bold; font-size: 11px; color: #c9541a; margin-top: 18px; margin-bottom: 6px; border-bottom: 1px solid #c9541a; padding-bottom: 3px; }
    .subsection-title { font-weight: bold; font-size: 10px; color: #c9541a; margin-top: 12px; margin-bottom: 4px; }
    .data-table { width: 100%; border-collapse: collapse; margin: 8px 0 12px; font-size: 9px; }
    .data-table th { background: #c9541a; color: white; padding: 5px 7px; text-align: center; font-weight: bold; border: 1px solid #c9541a; }
    .data-table td { border: 1px solid #aaa; padding: 4px 6px; vertical-align: top; }
    .data-table tr:nth-child(even) td { background: #f5f5f5; }
    .alert-box { background: #fff3cd; border: 1.5px solid #e6a800; border-radius: 4px; padding: 7px 10px; margin: 8px 0; font-size: 9.5px; }
    .step-box { border: 1px solid #c9541a; border-radius: 3px; padding: 6px 10px; margin: 5px 0; font-size: 9.5px; }
    .step-num { font-weight: bold; color: #c9541a; }
    p { margin: 5px 0 8px; font-size: 10px; }
    ul, ol { margin: 4px 0 8px 18px; font-size: 10px; }
    li { margin-bottom: 2px; }
    .nota { font-size: 8.5px; color: #555; font-style: italic; margin: 4px 0; }
</style>
</head>
<body>

<!-- ENCABEZADO -->
<table class="header-table">
    <tr>
        <td class="logo-cell">
            <?php if (!empty($logoBase64)): ?>
            <img src="<?= $logoBase64 ?>" alt="Logo">
            <?php else: ?>
            <span style="font-size:8px; color:#999;">LOGO</span>
            <?php endif; ?>
        </td>
        <td class="title-cell">
            PLAN DE CONTINGENCIAS INFESTACIÓN DE PLAGAS<br>
            <span style="font-weight:normal;"><?= esc($cliente['nombre_cliente'] ?? '') ?></span>
        </td>
        <td class="code-cell">
            <strong>Código:</strong> FT-SST-233<br>
            <strong>Versión:</strong> 001<br>
            <strong>Fecha:</strong> <?= !empty($inspeccion['fecha_programa']) ? date('d/m/Y', strtotime($inspeccion['fecha_programa'])) : date('d/m/Y') ?><br>
            <strong>Responsable:</strong> <?= esc($inspeccion['nombre_responsable'] ?? 'Administrador(a)') ?>
        </td>
    </tr>
</table>

<div class="main-title">PLAN DE CONTINGENCIAS INFESTACIÓN DE PLAGAS</div>
<div class="subtitle">Tienda a Tienda — Salud y Seguridad en el Trabajo</div>

<!-- 1. OBJETIVO -->
<div class="section-title">1. OBJETIVO</div>
<p>Establecer las acciones de prevención, control y respuesta ante un evento de infestación de plagas (cucarachas, ratas, mosquitos, palomas, hormigas u otros vectores) en las instalaciones del establecimiento, con el fin de proteger la salud de clientes, trabajadores y visitantes, y minimizar los daños a la infraestructura.</p>

<!-- 2. ALCANCE -->
<div class="section-title">2. ALCANCE</div>
<p>Este plan aplica a todas las áreas del establecimiento y zonas de servicio de <strong><?= esc($cliente['nombre_cliente'] ?? 'el establecimiento comercial') ?></strong>, incluyendo: sótanos, cuartos de residuos, cuartos de bombas, zonas húmedas, cocinas comunales, jardines, parqueaderos y cualquier área donde se detecte presencia o riesgo de infestación de plagas.</p>

<!-- 3. MARCO LEGAL -->
<div class="section-title">3. MARCO LEGAL</div>
<ul>
    <li><strong>Ley 9 de 1979</strong> — Código Sanitario Nacional. Obligatoriedad de control de vectores y plagas.</li>
    <li><strong>Decreto 1843 de 1991</strong> — Reglamenta el uso y manejo de plaguicidas.</li>
    <li><strong>Resolución 2827 de 2006</strong> — Manual de actividades de control de vectores y zoonosis.</li>
    <li><strong>Decreto 1072 de 2015</strong> — Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST).</li>
    <li><strong>Resolución 0312 de 2019</strong> — Estándares mínimos del SG-SST.</li>
    <li><strong>Resolución 2674 de 2013</strong> — Requisitos sanitarios y BPM; Art. 26 sobre control integrado de plagas en establecimientos de alimentos.</li>
    <li><strong>Ley 232 de 1995</strong> — Requisitos de funcionamiento de los establecimientos de comercio.</li>
</ul>

<!-- 4. DEFINICIONES -->
<div class="section-title">4. DEFINICIONES</div>
<table class="data-table">
    <tr><th style="width:30%;">TÉRMINO</th><th>DEFINICIÓN</th></tr>
    <tr><td><strong>Plaga</strong></td><td>Organismo animal o vegetal que causa daño a la salud humana, a los bienes materiales o al medioambiente.</td></tr>
    <tr><td><strong>Vector</strong></td><td>Organismo vivo que transmite un agente infeccioso de un animal infectado a un ser humano u otro animal.</td></tr>
    <tr><td><strong>Fumigación</strong></td><td>Aplicación de productos químicos o biológicos para eliminar o controlar la presencia de plagas.</td></tr>
    <tr><td><strong>Empresa certificada</strong></td><td>Empresa de control de plagas autorizada por la Secretaría de Salud y registrada ante el ICA.</td></tr>
    <tr><td><strong>EPP</strong></td><td>Elemento de Protección Personal requerido durante operaciones de fumigación.</td></tr>
</table>

<!-- 5. IDENTIFICACIÓN DE PLAGAS COMUNES -->
<div class="section-title">5. IDENTIFICACIÓN DE PLAGAS COMUNES EN ESTABLECIMIENTOS DE COMERCIO</div>
<table class="data-table">
    <tr><th>PLAGA</th><th>ÁREAS DE MAYOR RIESGO</th><th>SEÑALES DE ALERTA</th><th>RIESGO PARA LA SALUD</th></tr>
    <tr><td>Cucarachas</td><td>Cuarto de residuos, cocinas, sótano</td><td>Heces, olor, avistamiento nocturno</td><td>Salmonelosis, alergias, asma</td></tr>
    <tr><td>Ratas/ratones</td><td>Sótano, cuarto de bombas, jardines</td><td>Huellas, roeduras, nidos, heces</td><td>Leptospirosis, hantavirus, daños eléctricos</td></tr>
    <tr><td>Mosquitos</td><td>Zonas húmedas, tanques, sumideros</td><td>Charcos estancados, larvas en agua</td><td>Dengue, zika, chikungunya, malaria</td></tr>
    <tr><td>Palomas</td><td>Terrazas, ventanas, antenas</td><td>Excrementos en fachadas y cubiertas</td><td>Histoplasmosis, criptococosis</td></tr>
    <tr><td>Hormigas</td><td>Cocinas, jardines, muros</td><td>Columnas de hormigas, rastros de azúcar</td><td>Contaminación de alimentos</td></tr>
</table>

<!-- 6. EMPRESA DE CONTROL DE PLAGAS -->
<div class="section-title">6. EMPRESA DE CONTROL DE PLAGAS CONTRATADA</div>
<?php if (!empty($inspeccion['empresa_fumigadora'])): ?>
<table style="width:100%; border:1.5px solid #c9541a; border-collapse:collapse;">
    <tr>
        <td style="background:#c9541a; color:white; padding:6px 10px; font-weight:bold; width:35%;">EMPRESA FUMIGADORA</td>
        <td style="padding:7px 10px;"><?= nl2br(esc($inspeccion['empresa_fumigadora'])) ?></td>
    </tr>
</table>
<?php else: ?>
<p class="nota">Empresa fumigadora: por definir — completar antes de activar el plan.</p>
<?php endif; ?>
<p style="margin-top:8px;">La empresa contratada debe contar con:</p>
<ul>
    <li>Registro Sanitario vigente ante la Secretaría de Salud Departamental.</li>
    <li>Registro ICA para uso de plaguicidas.</li>
    <li>Hojas de Seguridad (MSDS) de todos los productos utilizados.</li>
    <li>Seguro de responsabilidad civil vigente.</li>
    <li>Personal certificado en manejo de plaguicidas y uso de EPP.</li>
</ul>

<!-- 7. PROTOCOLO DE ACTUACIÓN -->
<div class="section-title">7. PROTOCOLO DE ACTUACIÓN ANTE INFESTACIÓN</div>

<div class="subsection-title">7.1 Detección y reporte</div>
<div class="step-box"><span class="step-num">PASO 1:</span> Cualquier cliente o trabajador, empleado o visitante que detecte indicios de plaga debe reportar de inmediato a la administración indicando: tipo de plaga detectada, área afectada, fecha y hora de avistamiento.</div>
<div class="step-box"><span class="step-num">PASO 2:</span> La administración registra el reporte en la bitácora de incidentes sanitarios y realiza una verificación visual del área reportada dentro de las siguientes 2 horas.</div>

<div class="subsection-title">7.2 Evaluación y clasificación</div>
<table class="data-table">
    <tr><th>NIVEL</th><th>DESCRIPCIÓN</th><th>ACCIÓN</th><th>TIEMPO RESPUESTA</th></tr>
    <tr><td><strong>Nivel 1 — Avistamiento puntual</strong></td><td>1-3 individuos en área aislada</td><td>Trampas y medidas preventivas internas</td><td>24 horas</td></tr>
    <tr><td><strong>Nivel 2 — Presencia moderada</strong></td><td>Avistamientos repetidos, más de 3 individuos</td><td>Contactar empresa fumigadora, aplicar tratamiento localizado</td><td>48 horas</td></tr>
    <tr><td><strong>Nivel 3 — Infestación activa</strong></td><td>Proliferación extendida, múltiples áreas</td><td>Fumigación general, evacuación temporal de áreas afectadas</td><td>Inmediato — máx. 24h</td></tr>
</table>

<div class="subsection-title">7.3 Fumigación y tratamiento</div>
<ol>
    <li>Notificar a todos los clientes y trabajadores con mínimo <strong>48 horas de anticipación</strong> mediante circular escrita o mensaje en el sistema de comunicación del conjunto.</li>
    <li>Indicar a los clientes y trabajadores que cubran o retiren alimentos, vajilla y elementos de cocina expuestos.</li>
    <li>Coordinar el horario de fumigación evitando horas pico de tráfico (preferiblemente madrugada o días no hábiles).</li>
    <li>Establecer el tiempo de permanencia fuera de las áreas fumigadas según las indicaciones de la empresa (mínimo 2 horas, generalmente 4-6 horas).</li>
    <li>Ventilar las áreas durante al menos 30 minutos antes de permitir el reingreso.</li>
    <li>Conservar las fichas técnicas y hojas de seguridad de los productos aplicados.</li>
</ol>

<div class="subsection-title">7.4 Seguimiento post-tratamiento</div>
<ul>
    <li>Inspección visual de las áreas tratadas a las 72 horas.</li>
    <li>Segunda fumigación si persisten indicios de plaga (a los 15 días).</li>
    <li>Registro fotográfico antes y después del tratamiento.</li>
    <li>Informe escrito de la empresa fumigadora con: productos utilizados, áreas tratadas, dosis aplicada y recomendaciones.</li>
</ul>

<!-- 8. MEDIDAS PREVENTIVAS PERMANENTES -->
<div class="section-title">8. MEDIDAS PREVENTIVAS PERMANENTES</div>
<table class="data-table">
    <tr><th>MEDIDA</th><th>ÁREA</th><th>FRECUENCIA</th><th>RESPONSABLE</th></tr>
    <tr><td>Sellado de grietas y orificios en muros y pisos</td><td>Sótano, cuartos técnicos</td><td>Semestral o al detectar</td><td>Mantenimiento</td></tr>
    <tr><td>Limpieza y desinfección del cuarto de residuos</td><td>Cuarto de basuras</td><td>Diaria</td><td>Recuperador / Aseo</td></tr>
    <tr><td>Eliminación de agua estancada</td><td>Terrazas, jardines, sótano</td><td>Semanal</td><td>Mantenimiento</td></tr>
    <tr><td>Revisión de tapas de alcantarillas y sifones</td><td>Zonas húmedas</td><td>Mensual</td><td>Mantenimiento</td></tr>
    <tr><td>Fumigación preventiva contratada</td><td>Todas las áreas del establecimiento</td><td>Trimestral</td><td>Administración</td></tr>
    <tr><td>Control de palomas (mallas o pinchos)</td><td>Terrazas, balcones comunes</td><td>Semestral</td><td>Administración</td></tr>
    <tr><td>Capacitación a empleados sobre manejo de residuos</td><td>Todo el personal</td><td>Anual</td><td>Consultor SST</td></tr>
</table>

<!-- 9. COMUNICACIÓN -->
<div class="section-title">9. COMUNICACIÓN CON CLIENTES Y TRABAJADORES Y AUTORIDADES</div>
<p>Cuando la infestación requiera fumigación general:</p>
<ul>
    <li><strong>Comunicación interna:</strong> Circular a todos los clientes y trabajadores indicando: plaga detectada, áreas afectadas, fecha y hora de fumigación, indicaciones de seguridad y tiempo de reingreso.</li>
    <li><strong>Comunicación a Secretaría de Salud:</strong> En casos de infestación severa (Nivel 3), notificar a la Secretaría de Salud Municipal para apoyo técnico y verificación.</li>
    <li><strong>Registro de aprobación:</strong> Documentar la situación y las acciones tomadas en el registro interno, con firma de aprobación del propietario del establecimiento.</li>
</ul>

<div class="alert-box">
    <strong>⚠ ATENCIÓN:</strong> Queda estrictamente prohibido que clientes y trabajadores o empleados apliquen plaguicidas por cuenta propia en áreas del establecimiento. Todo tratamiento debe ser realizado por la empresa certificada contratada.
</div>

<!-- 10. RESPONSABLES -->
<div class="section-title">10. RESPONSABLES</div>
<table class="data-table">
    <tr><th>ROL</th><th>RESPONSABILIDAD</th></tr>
    <tr><td><strong>Administrador(a)</strong></td><td>Activar el plan, contratar la empresa fumigadora, notificar a clientes y trabajadores y autoridades, gestionar el registro documental.</td></tr>
    <tr><td><strong>Consultor SST</strong></td><td>Asesorar el plan, verificar condiciones legales de la empresa contratada, capacitar al personal.</td></tr>
    <tr><td><strong>Personal de mantenimiento</strong></td><td>Ejecutar medidas preventivas permanentes, reportar indicios de plaga.</td></tr>
    <tr><td><strong>Empresa fumigadora</strong></td><td>Evaluar, tratar y hacer seguimiento. Entregar informe técnico con productos y dosis aplicadas.</td></tr>
    <tr><td><strong>Propietario del establecimiento</strong></td><td>Aprobar contrataciones y presupuesto para fumigaciones preventivas y correctivas.</td></tr>
</table>

<!-- 11. REGISTROS -->
<div class="section-title">11. REGISTROS Y DOCUMENTACIÓN</div>
<ul>
    <li>Bitácora de reporte de plagas (quién reporta, qué plaga, dónde, cuándo).</li>
    <li>Certificado de fumigación emitido por la empresa contratada.</li>
    <li>Fichas técnicas y hojas de seguridad de los plaguicidas utilizados.</li>
    <li>Registro fotográfico del antes y después del tratamiento.</li>
    <li>Circular de notificación a clientes y trabajadores.</li>
    <li>Registro de aprobación del propietario del establecimiento con soporte de la contratación.</li>
</ul>


</body>
</html>

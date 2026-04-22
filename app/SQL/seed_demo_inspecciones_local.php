<?php
/**
 * SEED DEMO — solo LOCAL.
 *
 * Puebla el cliente id=1 (Tienda Don Pepe - Prueba) con >=3 filas por cada
 * módulo de inspección, usando imágenes existentes en public/uploads.
 *
 * Idempotente: borra primero las filas marcadas con [SEED_DEMO] y vuelve a
 * insertarlas. No toca datos reales (los que no tengan el marcador).
 *
 * NUNCA correr en producción. El script valida el host.
 */
if (php_sapi_name() !== 'cli') die('CLI only');

$config = ['host'=>'127.0.0.1','port'=>3306,'user'=>'root','password'=>'','database'=>'tat_cycloid'];

if ($config['host'] !== '127.0.0.1' && $config['host'] !== 'localhost') {
    die("ABORT: seed solo para LOCAL.\n");
}

$m = mysqli_init();
if (!@$m->real_connect($config['host'],$config['user'],$config['password'],$config['database'],$config['port'])) {
    die("ERR conexión: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

$ID_CLIENTE  = 1;
$ID_CONSULTOR = 15;
$MARK = '[SEED_DEMO]';

// Verificar cliente
$r = $m->query("SELECT id_cliente, nombre_cliente FROM tbl_clientes WHERE id_cliente={$ID_CLIENTE}");
if (!$r || $r->num_rows === 0) die("Cliente id={$ID_CLIENTE} no existe.\n");
$cli = $r->fetch_assoc();
echo "Cliente: {$cli['nombre_cliente']} (id={$cli['id_cliente']})\n\n";

// ---- Pool de imágenes disponibles ----
$uploads = __DIR__ . '/../../public/uploads';
$imagenes = [];
if (is_dir($uploads)) {
    foreach (scandir($uploads) as $f) {
        if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $f)) $imagenes[] = $f;
    }
}
if (count($imagenes) < 10) die("Pocas imágenes en uploads/ ({" . count($imagenes) . "})\n");
shuffle($imagenes);
$imagenes = array_slice($imagenes, 0, 40);
$fotoIdx = 0;
function nextFoto() { global $imagenes, $fotoIdx; $f = $imagenes[$fotoIdx % count($imagenes)]; $fotoIdx++; return $f; }

// ============================================================
// 1. NEVERAS — mediciones
// ============================================================
echo "=== Neveras: mediciones ===\n";
$m->query("DELETE FROM tbl_inspeccion_nevera WHERE id_cliente={$ID_CLIENTE} AND observaciones LIKE '%{$MARK}%'");
$rs = $m->query("SELECT id_nevera, nombre, rango_temp_min, rango_temp_max FROM tbl_nevera WHERE id_cliente={$ID_CLIENTE} AND activo=1");
while ($n = $rs->fetch_assoc()) {
    for ($i = 0; $i < 3; $i++) {
        $fecha = date('Y-m-d H:i:s', strtotime("-{$i} days -" . (rand(0,12)) . " hours"));
        $temp = rand((int)($n['rango_temp_min']*10), (int)($n['rango_temp_max']*10)) / 10;
        $hum  = rand(30, 70);
        $f1 = nextFoto(); $f2 = nextFoto();
        $obs = "{$MARK} Medición de prueba #" . ($i+1);
        $sql = "INSERT INTO tbl_inspeccion_nevera (id_cliente, id_nevera, fecha_hora, temperatura, humedad_relativa, foto_temperatura, foto_humedad, observaciones, dentro_rango, registrado_por)
                VALUES ({$ID_CLIENTE}, {$n['id_nevera']}, '{$fecha}', {$temp}, {$hum}, '{$f1}', '{$f2}', '{$obs}', 1, 'cliente')";
        if (!$m->query($sql)) echo "  ERR: " . $m->error . "\n";
    }
    echo "  + Nevera {$n['nombre']}: 3 mediciones\n";
}

// ============================================================
// 2. LIMPIEZA — 3 inspecciones + detalles
// ============================================================
echo "\n=== Limpieza del local ===\n";
$m->query("DELETE d FROM tbl_inspeccion_limpieza_detalle d
           INNER JOIN tbl_inspeccion_limpieza_local h ON h.id=d.id_inspeccion
           WHERE h.id_cliente={$ID_CLIENTE} AND h.observaciones_generales LIKE '%{$MARK}%'");
$m->query("DELETE FROM tbl_inspeccion_limpieza_local WHERE id_cliente={$ID_CLIENTE} AND observaciones_generales LIKE '%{$MARK}%'");

$items = [];
$rs = $m->query("SELECT i.id_item FROM tbl_limpieza_item i INNER JOIN tbl_limpieza_item_cliente p ON p.id_item=i.id_item WHERE p.id_cliente={$ID_CLIENTE} AND i.activo=1 LIMIT 8");
while ($row = $rs->fetch_assoc()) $items[] = (int)$row['id_item'];

for ($insp = 0; $insp < 3; $insp++) {
    $fecha = date('Y-m-d H:i:s', strtotime("-" . (3+$insp*2) . " days"));
    $obs = "{$MARK} Inspección demo #" . ($insp+1);
    $m->query("INSERT INTO tbl_inspeccion_limpieza_local (id_cliente, fecha_hora, observaciones_generales, resultado_general, registrado_por) VALUES ({$ID_CLIENTE}, '{$fecha}', '{$obs}', 'ok', 'cliente')");
    $idInsp = $m->insert_id;
    $estados = ['limpio','limpio','sucio','limpio','no_aplica'];
    foreach (array_slice($items, 0, 5) as $idx => $idItem) {
        $estado = $estados[$idx];
        $foto = $estado === 'no_aplica' ? 'NULL' : "'" . nextFoto() . "'";
        $m->query("INSERT INTO tbl_inspeccion_limpieza_detalle (id_inspeccion, id_item, estado, foto, observaciones) VALUES ({$idInsp}, {$idItem}, '{$estado}', {$foto}, 'Detalle demo')");
    }
    echo "  + Inspección #" . ($insp+1) . " (id={$idInsp}) con 5 detalles\n";
}

// ============================================================
// 3. EQUIPOS Y UTENSILIOS
// ============================================================
echo "\n=== Equipos y utensilios ===\n";
$m->query("DELETE d FROM tbl_inspeccion_equipos_detalle d
           INNER JOIN tbl_inspeccion_equipos h ON h.id=d.id_inspeccion
           WHERE h.id_cliente={$ID_CLIENTE} AND h.observaciones_generales LIKE '%{$MARK}%'");
$m->query("DELETE FROM tbl_inspeccion_equipos WHERE id_cliente={$ID_CLIENTE} AND observaciones_generales LIKE '%{$MARK}%'");

$items = [];
$rs = $m->query("SELECT i.id_item FROM tbl_equipos_item i INNER JOIN tbl_equipos_item_cliente p ON p.id_item=i.id_item WHERE p.id_cliente={$ID_CLIENTE} AND i.activo=1 LIMIT 8");
while ($row = $rs->fetch_assoc()) $items[] = (int)$row['id_item'];

for ($insp = 0; $insp < 3; $insp++) {
    $fecha = date('Y-m-d H:i:s', strtotime("-" . (2+$insp*2) . " days"));
    $obs = "{$MARK} Inspección equipos demo #" . ($insp+1);
    $m->query("INSERT INTO tbl_inspeccion_equipos (id_cliente, fecha_hora, observaciones_generales, resultado_general, registrado_por) VALUES ({$ID_CLIENTE}, '{$fecha}', '{$obs}', 'ok', 'cliente')");
    $idInsp = $m->insert_id;
    $estados = ['funcional','funcional','defectuoso','funcional','no_aplica'];
    foreach (array_slice($items, 0, 5) as $idx => $idItem) {
        $estado = $estados[$idx];
        $foto = $estado === 'no_aplica' ? 'NULL' : "'" . nextFoto() . "'";
        $m->query("INSERT INTO tbl_inspeccion_equipos_detalle (id_inspeccion, id_item, estado, foto, observaciones) VALUES ({$idInsp}, {$idItem}, '{$estado}', {$foto}, 'Detalle demo')");
    }
    echo "  + Inspección #" . ($insp+1) . " (id={$idInsp}) con 5 detalles\n";
}

// ============================================================
// 4. CONTAMINACIÓN CRUZADA
// ============================================================
echo "\n=== Contaminación cruzada ===\n";
$m->query("DELETE d FROM tbl_inspeccion_contaminacion_detalle d
           INNER JOIN tbl_inspeccion_contaminacion h ON h.id=d.id_inspeccion
           WHERE h.id_cliente={$ID_CLIENTE} AND h.observaciones_generales LIKE '%{$MARK}%'");
$m->query("DELETE FROM tbl_inspeccion_contaminacion WHERE id_cliente={$ID_CLIENTE} AND observaciones_generales LIKE '%{$MARK}%'");

$items = [];
$rs = $m->query("SELECT i.id_item FROM tbl_contaminacion_item i INNER JOIN tbl_contaminacion_item_cliente p ON p.id_item=i.id_item WHERE p.id_cliente={$ID_CLIENTE} AND i.activo=1 LIMIT 8");
while ($row = $rs->fetch_assoc()) $items[] = (int)$row['id_item'];

for ($insp = 0; $insp < 3; $insp++) {
    $fecha = date('Y-m-d H:i:s', strtotime("-" . (1+$insp*2) . " days"));
    $obs = "{$MARK} Contaminación demo #" . ($insp+1);
    $m->query("INSERT INTO tbl_inspeccion_contaminacion (id_cliente, fecha_hora, observaciones_generales, resultado_general, registrado_por) VALUES ({$ID_CLIENTE}, '{$fecha}', '{$obs}', 'ok', 'cliente')");
    $idInsp = $m->insert_id;
    $estados = ['cumple','cumple','no_cumple','cumple','no_aplica'];
    foreach (array_slice($items, 0, 5) as $idx => $idItem) {
        $estado = $estados[$idx];
        $foto = $estado === 'no_aplica' ? 'NULL' : "'" . nextFoto() . "'";
        $m->query("INSERT INTO tbl_inspeccion_contaminacion_detalle (id_inspeccion, id_item, estado, foto, observaciones) VALUES ({$idInsp}, {$idItem}, '{$estado}', {$foto}, 'Detalle demo')");
    }
    echo "  + Inspección #" . ($insp+1) . " (id={$idInsp}) con 5 detalles\n";
}

// ============================================================
// 5. ALMACENAMIENTO
// ============================================================
echo "\n=== Almacenamiento ===\n";
$m->query("DELETE d FROM tbl_inspeccion_almacenamiento_detalle d
           INNER JOIN tbl_inspeccion_almacenamiento h ON h.id=d.id_inspeccion
           WHERE h.id_cliente={$ID_CLIENTE} AND h.observaciones_generales LIKE '%{$MARK}%'");
$m->query("DELETE FROM tbl_inspeccion_almacenamiento WHERE id_cliente={$ID_CLIENTE} AND observaciones_generales LIKE '%{$MARK}%'");

$items = [];
$rs = $m->query("SELECT i.id_item FROM tbl_almacenamiento_item i INNER JOIN tbl_almacenamiento_item_cliente p ON p.id_item=i.id_item WHERE p.id_cliente={$ID_CLIENTE} AND i.activo=1 LIMIT 8");
while ($row = $rs->fetch_assoc()) $items[] = (int)$row['id_item'];

for ($insp = 0; $insp < 3; $insp++) {
    $fecha = date('Y-m-d H:i:s', strtotime("-" . ($insp*2) . " days"));
    $obs = "{$MARK} Almacenamiento demo #" . ($insp+1);
    $m->query("INSERT INTO tbl_inspeccion_almacenamiento (id_cliente, fecha_hora, observaciones_generales, resultado_general, registrado_por) VALUES ({$ID_CLIENTE}, '{$fecha}', '{$obs}', 'ok', 'cliente')");
    $idInsp = $m->insert_id;
    $estados = ['cumple','cumple','cumple','no_cumple','no_aplica'];
    foreach (array_slice($items, 0, 5) as $idx => $idItem) {
        $estado = $estados[$idx];
        $foto = $estado === 'no_aplica' ? 'NULL' : "'" . nextFoto() . "'";
        $m->query("INSERT INTO tbl_inspeccion_almacenamiento_detalle (id_inspeccion, id_item, estado, foto, observaciones) VALUES ({$idInsp}, {$idItem}, '{$estado}', {$foto}, 'Detalle demo')");
    }
    echo "  + Inspección #" . ($insp+1) . " (id={$idInsp}) con 5 detalles\n";
}

// ============================================================
// 6. RECEPCIÓN MP
// ============================================================
echo "\n=== Recepción MP ===\n";
$m->query("DELETE FROM tbl_recepcion_mp WHERE id_cliente={$ID_CLIENTE} AND observaciones LIKE '%{$MARK}%'");

$proveedores = [];
$rs = $m->query("SELECT id_proveedor, nombre FROM tbl_proveedor WHERE id_cliente={$ID_CLIENTE} AND activo=1 LIMIT 5");
while ($row = $rs->fetch_assoc()) $proveedores[] = $row;
if (empty($proveedores)) echo "  ! No hay proveedores; se deja proveedor_nombre manual.\n";

$demo = [
    ['Pollo entero', 'carnicos', 10.5, 'kg', 3.5],
    ['Leche UHT 1L', 'lacteos', 50, 'unidad', 4.0],
    ['Arroz blanco 500g', 'empacados', 100, 'unidad', null],
];
foreach ($demo as $idx => $d) {
    [$prod, $cat, $cant, $uni, $temp] = $d;
    $fecha = date('Y-m-d H:i:s', strtotime("-{$idx} days"));
    $prov = $proveedores[$idx % max(1,count($proveedores))] ?? ['id_proveedor' => 'NULL', 'nombre' => 'Proveedor Genérico'];
    $idProv = $prov['id_proveedor'] === 'NULL' ? 'NULL' : (int)$prov['id_proveedor'];
    $provNom = $m->real_escape_string($prov['nombre']);
    $tempVal = $temp === null ? 'NULL' : $temp;
    $fotoP = nextFoto(); $fotoF = nextFoto(); $fotoT = $temp ? nextFoto() : null;
    $fotoTSql = $fotoT ? "'{$fotoT}'" : 'NULL';
    $obs = "{$MARK} Recepción MP demo";
    $sql = "INSERT INTO tbl_recepcion_mp (id_cliente, id_proveedor, proveedor_nombre, fecha_hora, producto, categoria, cantidad, unidad, temperatura_recepcion, empaque_ok, producto_ok, aceptado, foto_producto, foto_factura, foto_temperatura, observaciones, registrado_por)
            VALUES ({$ID_CLIENTE}, {$idProv}, '{$provNom}', '{$fecha}', '{$prod}', '{$cat}', {$cant}, '{$uni}', {$tempVal}, 1, 1, 1, '{$fotoP}', '{$fotoF}', {$fotoTSql}, '{$obs}', 'cliente')";
    if (!$m->query($sql)) echo "  ERR: " . $m->error . "\n";
    else echo "  + {$prod}\n";
}

// ============================================================
// 7. TRABAJADORES + SOPORTES
// ============================================================
echo "\n=== Trabajadores ===\n";
$m->query("DELETE s FROM tbl_trabajador_soporte s
           INNER JOIN tbl_trabajadores t ON t.id_trabajador=s.id_trabajador
           WHERE t.id_cliente={$ID_CLIENTE} AND t.nombre LIKE '%{$MARK}%'");
$m->query("DELETE FROM tbl_trabajadores WHERE id_cliente={$ID_CLIENTE} AND nombre LIKE '%{$MARK}%'");

$trabajadores = [
    ['nombre' => "Ana María López {$MARK}", 'cargo' => 'Cajera',      'manipula' => 1, 'doc' => '1020304050'],
    ['nombre' => "Carlos Rodríguez {$MARK}", 'cargo' => 'Carnicero',  'manipula' => 1, 'doc' => '1020304051'],
    ['nombre' => "Lucía Fernández {$MARK}",  'cargo' => 'Panadera',   'manipula' => 1, 'doc' => '1020304052'],
];
foreach ($trabajadores as $t) {
    $nombre = $m->real_escape_string($t['nombre']);
    $cargo = $m->real_escape_string($t['cargo']);
    $sql = "INSERT INTO tbl_trabajadores (id_cliente, nombre, tipo_id, numero_id, cargo, fecha_ingreso, manipula_alimentos, activo) VALUES ({$ID_CLIENTE}, '{$nombre}', 'CC', '{$t['doc']}', '{$cargo}', DATE_SUB(CURDATE(), INTERVAL " . rand(30,730) . " DAY), {$t['manipula']}, 1)";
    $m->query($sql);
    $idT = $m->insert_id;
    $tiposSop = ['datos','afiliacion_salud','manipulacion_alimentos'];
    $tipo = $tiposSop[array_rand($tiposSop)];
    $foto = nextFoto();
    $m->query("INSERT INTO tbl_trabajador_soporte (id_trabajador, tipo_soporte, archivo, fecha_expedicion, fecha_vencimiento) VALUES ({$idT}, '{$tipo}', '{$foto}', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 300 DAY))");
    echo "  + {$t['nombre']} (id={$idT}) con soporte {$tipo}\n";
}

// ============================================================
// 8. BOMBEROS — 1 expediente con 3 documentos
// ============================================================
echo "\n=== Bomberos ===\n";
$anio = date('Y');
$m->query("DELETE d FROM tbl_bomberos_documento d
           INNER JOIN tbl_bomberos_solicitud s ON s.id=d.id_solicitud
           WHERE s.id_cliente={$ID_CLIENTE} AND s.anio={$anio}");
$m->query("DELETE FROM tbl_bomberos_solicitud WHERE id_cliente={$ID_CLIENTE} AND anio={$anio}");

$obs = "{$MARK} Expediente demo";
$m->query("INSERT INTO tbl_bomberos_solicitud (id_cliente, anio, departamento, municipio, estado, observaciones) VALUES ({$ID_CLIENTE}, {$anio}, 'Cundinamarca', 'Soacha', 'borrador', '{$obs}')");
$idSol = $m->insert_id;
$docs = ['cedula_rl', 'camara_comercio', 'rut'];
foreach ($docs as $t) {
    $foto = nextFoto();
    $m->query("INSERT INTO tbl_bomberos_documento (id_solicitud, tipo_doc, archivo, observaciones) VALUES ({$idSol}, '{$t}', '{$foto}', 'Demo')");
}
echo "  + Expediente {$anio} (id={$idSol}) con 3 documentos\n";

// ============================================================
// 9. BOTIQUÍN (tipo B) + BOTIQUÍN TIPO A — 3 de cada
// ============================================================
foreach (['tbl_inspeccion_botiquin' => 'Botiquín (Tipo B)', 'tbl_inspeccion_botiquin_tipo_a' => 'Botiquín Tipo A'] as $tabla => $label) {
    echo "\n=== {$label} ===\n";
    $m->query("DELETE FROM {$tabla} WHERE id_cliente={$ID_CLIENTE} AND recomendaciones LIKE '%{$MARK}%'");
    for ($i = 0; $i < 3; $i++) {
        $fecha = date('Y-m-d', strtotime("-" . ($i*15) . " days"));
        $ubic = ['Caja principal','Bodega','Cocina'][$i];
        $f1 = nextFoto(); $f2 = nextFoto();
        $rec = "{$MARK} Botiquín demo #" . ($i+1);
        $sql = "INSERT INTO {$tabla} (id_cliente, id_consultor, fecha_inspeccion, ubicacion_botiquin, foto_1, foto_2, instalado_pared, libre_obstaculos, lugar_visible, con_senalizacion, tipo_botiquin, estado_botiquin, recomendaciones, estado) VALUES ({$ID_CLIENTE}, {$ID_CONSULTOR}, '{$fecha}', '{$ubic}', '{$f1}', '{$f2}', 'SI', 'SI', 'SI', 'SI', 'Tipo " . ($tabla === 'tbl_inspeccion_botiquin' ? 'B' : 'A') . "', 'Bueno', '{$rec}', 'completo')";
        if (!$m->query($sql)) echo "  ERR: " . $m->error . "\n";
    }
    echo "  + 3 inspecciones\n";
}

// ============================================================
// 10. ACTAS DE VISITA
// ============================================================
echo "\n=== Actas de Visita ===\n";
$m->query("DELETE FROM tbl_acta_visita WHERE id_cliente={$ID_CLIENTE} AND observaciones LIKE '%{$MARK}%'");
for ($i = 0; $i < 3; $i++) {
    $fecha = date('Y-m-d', strtotime("-" . ($i*10) . " days"));
    $hora = sprintf('%02d:00:00', 9 + $i);
    $motivo = ['Inspección mensual','Seguimiento SST','Capacitación personal'][$i];
    $obs = "{$MARK} Acta demo #" . ($i+1);
    $sql = "INSERT INTO tbl_acta_visita (id_cliente, id_consultor, fecha_visita, hora_visita, motivo, modalidad, observaciones, pta_confirmado, estado) VALUES ({$ID_CLIENTE}, {$ID_CONSULTOR}, '{$fecha}', '{$hora}', '{$motivo}', 'Presencial', '{$obs}', 1, 'completo')";
    if (!$m->query($sql)) echo "  ERR: " . $m->error . "\n";
    else echo "  + Acta {$fecha} — {$motivo}\n";
}

// ============================================================
// 11. INSPECCIONES LOCATIVAS + HALLAZGOS
// ============================================================
echo "\n=== Inspecciones Locativas ===\n";
$m->query("DELETE h FROM tbl_hallazgo_locativo h
           INNER JOIN tbl_inspeccion_locativa i ON i.id=h.id_inspeccion
           WHERE i.id_cliente={$ID_CLIENTE} AND i.observaciones LIKE '%{$MARK}%'");
$m->query("DELETE FROM tbl_inspeccion_locativa WHERE id_cliente={$ID_CLIENTE} AND observaciones LIKE '%{$MARK}%'");
$hallazgosDemo = [
    'Grieta en muro exterior zona mostrador',
    'Piso con desnivel cerca de la puerta de acceso',
    'Iluminación insuficiente en bodega',
    'Salida de emergencia con obstáculos',
    'Cables eléctricos sueltos en cocina',
];
for ($i = 0; $i < 3; $i++) {
    $fecha = date('Y-m-d', strtotime("-" . ($i*12) . " days"));
    $obs = "{$MARK} Inspección locativa demo #" . ($i+1);
    $m->query("INSERT INTO tbl_inspeccion_locativa (id_cliente, id_consultor, fecha_inspeccion, observaciones, estado) VALUES ({$ID_CLIENTE}, {$ID_CONSULTOR}, '{$fecha}', '{$obs}', 'completo')");
    $idInsp = $m->insert_id;
    for ($h = 0; $h < 3; $h++) {
        $desc = $m->real_escape_string($hallazgosDemo[($i*3 + $h) % count($hallazgosDemo)]);
        $img = nextFoto();
        $estado = $h === 2 ? 'CORREGIDO' : 'ABIERTO';
        $m->query("INSERT INTO tbl_hallazgo_locativo (id_inspeccion, descripcion, imagen, fecha_hallazgo, estado, orden) VALUES ({$idInsp}, '{$desc}', '{$img}', '{$fecha}', '{$estado}', " . ($h+1) . ")");
    }
    echo "  + Inspección locativa {$fecha} con 3 hallazgos\n";
}

// ============================================================
// 12. EXTINTORES + DETALLE POR EXTINTOR
// ============================================================
echo "\n=== Extintores ===\n";
$m->query("DELETE d FROM tbl_extintor_detalle d
           INNER JOIN tbl_inspeccion_extintores i ON i.id=d.id_inspeccion
           WHERE i.id_cliente={$ID_CLIENTE} AND i.recomendaciones_generales LIKE '%{$MARK}%'");
$m->query("DELETE FROM tbl_inspeccion_extintores WHERE id_cliente={$ID_CLIENTE} AND recomendaciones_generales LIKE '%{$MARK}%'");
for ($i = 0; $i < 3; $i++) {
    $fecha = date('Y-m-d', strtotime("-" . ($i*20) . " days"));
    $venc = date('Y-m-d', strtotime("+" . (300 - $i*30) . " days"));
    $total = 3; $abc = 2; $co2 = 1;
    $rec = "{$MARK} Inspección extintores demo #" . ($i+1);
    $m->query("INSERT INTO tbl_inspeccion_extintores (id_cliente, id_consultor, fecha_inspeccion, fecha_vencimiento_global, numero_extintores_totales, cantidad_abc, cantidad_co2, capacidad_libras, recomendaciones_generales, estado) VALUES ({$ID_CLIENTE}, {$ID_CONSULTOR}, '{$fecha}', '{$venc}', {$total}, {$abc}, {$co2}, '10 libras', '{$rec}', 'completo')");
    $idInsp = $m->insert_id;
    for ($e = 0; $e < 3; $e++) {
        $foto = nextFoto();
        $vencE = date('Y-m-d', strtotime("+" . (rand(60, 600)) . " days"));
        $obsExt = 'Extintor #' . ($e+1) . ' revisado, condiciones generales buenas';
        $m->query("INSERT INTO tbl_extintor_detalle (id_inspeccion, pintura_cilindro, golpes_extintor, autoadhesivo, manija_transporte, palanca_accionamiento, presion, manometro, boquilla, manguera, ring_seguridad, senalizacion, soporte, fecha_vencimiento, foto, observaciones, orden) VALUES ({$idInsp}, 'BUENO', 'NO', 'BUENO', 'BUENO', 'BUENO', 'CARGADO', 'BUENO', 'BUENO', 'NO APLICA', 'BUENO', 'BUENO', 'BUENO', '{$vencE}', '{$foto}', '{$obsExt}', " . ($e+1) . ")");
    }
    echo "  + Inspección extintores {$fecha} con 3 extintores detallados\n";
}

// ============================================================
// 13. CARTAS DE VIGÍA
// ============================================================
echo "\n=== Cartas de Vigía ===\n";
$m->query("DELETE FROM tbl_carta_vigia WHERE id_cliente={$ID_CLIENTE} AND nombre_vigia LIKE '%{$MARK}%'");
$vigias = [
    ["María González {$MARK}", '1020304060', 'maria.gonzalez@test.com', '3001234567', 'pendiente_firma'],
    ["Pedro Martínez {$MARK}", '1020304061', 'pedro.martinez@test.com', '3001234568', 'firmado'],
    ["Laura Díaz {$MARK}",     '1020304062', 'laura.diaz@test.com',      '3001234569', 'sin_enviar'],
];
foreach ($vigias as $v) {
    $extraCol = $v[4] === 'firmado' ? ', firma_fecha' : '';
    $extraVal = $v[4] === 'firmado' ? ', NOW()' : '';
    $sql = "INSERT INTO tbl_carta_vigia (id_cliente, id_consultor, nombre_vigia, documento_vigia, email_vigia, telefono_vigia, estado_firma{$extraCol}) VALUES ({$ID_CLIENTE}, {$ID_CONSULTOR}, '{$v[0]}', '{$v[1]}', '{$v[2]}', '{$v[3]}', '{$v[4]}'{$extraVal})";
    $m->query($sql);
    echo "  + {$v[0]} ({$v[4]})\n";
}

// ============================================================
// 14. MANTENIMIENTOS (catálogo + vencimientos por cliente)
// ============================================================
echo "\n=== Mantenimientos ===\n";
$catTypes = [
    'Mantenimiento preventivo equipos de frío',
    'Mantenimiento sistema eléctrico',
    'Mantenimiento extintores',
    'Lavado y desinfección de tanques de agua',
    'Control integrado de plagas',
];
$idsMant = [];
foreach ($catTypes as $t) {
    $esc = $m->real_escape_string($t);
    $r = $m->query("SELECT id_mantenimiento FROM tbl_mantenimientos WHERE detalle_mantenimiento='{$esc}' LIMIT 1");
    if ($r && $r->num_rows) $idsMant[] = $r->fetch_assoc()['id_mantenimiento'];
    else {
        $m->query("INSERT INTO tbl_mantenimientos (detalle_mantenimiento) VALUES ('{$esc}')");
        $idsMant[] = $m->insert_id;
    }
}
$m->query("DELETE FROM tbl_vencimientos_mantenimientos WHERE id_cliente={$ID_CLIENTE} AND observaciones LIKE '%{$MARK}%'");
for ($i = 0; $i < 3; $i++) {
    $idMant = $idsMant[$i];
    $vence = date('Y-m-d', strtotime("+" . (30 + $i*45) . " days"));
    $obs = "{$MARK} Mantenimiento demo";
    $m->query("INSERT INTO tbl_vencimientos_mantenimientos (id_mantenimiento, id_cliente, id_consultor, fecha_vencimiento, estado_actividad, observaciones) VALUES ({$idMant}, {$ID_CLIENTE}, {$ID_CONSULTOR}, '{$vence}', 'sin ejecutar', '{$obs}')");
    echo "  + {$catTypes[$i]} (vence {$vence})\n";
}

// ============================================================
// 15. PROGRAMAS SANEAMIENTO: limpieza, residuos, plagas, agua potable + plan
// ============================================================
$programas = [
    'tbl_programa_limpieza'      => 'Limpieza y Desinfección',
    'tbl_programa_residuos'      => 'Residuos Sólidos',
    'tbl_programa_plagas'        => 'Control Plagas',
    'tbl_programa_agua_potable'  => 'Agua Potable',
    'tbl_plan_saneamiento'       => 'Plan Saneamiento',
];
foreach ($programas as $tabla => $label) {
    echo "\n=== {$label} ===\n";
    $m->query("DELETE FROM {$tabla} WHERE id_cliente={$ID_CLIENTE} AND nombre_responsable LIKE '%{$MARK}%'");
    for ($i = 0; $i < 3; $i++) {
        $fecha = date('Y-m-d', strtotime("-" . ($i*30) . " days"));
        $resp = "Responsable demo {$MARK}";
        $extra = $tabla === 'tbl_programa_residuos' ? ", flujo_residente" : ($tabla === 'tbl_programa_agua_potable' ? ", cantidad_tanques, capacidad_individual, capacidad_total" : "");
        $extraVal = $tabla === 'tbl_programa_residuos' ? ", 'Flujo de recolección demo'" : ($tabla === 'tbl_programa_agua_potable' ? ", '2', '500L', '1000L'" : "");
        $sql = "INSERT INTO {$tabla} (id_cliente, id_consultor, fecha_programa, nombre_responsable, estado{$extra}) VALUES ({$ID_CLIENTE}, {$ID_CONSULTOR}, '{$fecha}', '{$resp}', 'completo'{$extraVal})";
        if (!$m->query($sql)) echo "  ERR: " . $m->error . "\n";
    }
    echo "  + 3 registros\n";
}

// ============================================================
// 16. KPIs (4 tablas, mismo esquema)
// ============================================================
$kpis = [
    'tbl_kpi_limpieza'     => ['Limpieza y Desinfección', '% cumplimiento rutinas limpieza'],
    'tbl_kpi_residuos'     => ['Residuos Sólidos',        '% separación correcta en fuente'],
    'tbl_kpi_plagas'       => ['Plagas',                  '% áreas sin indicio de plagas'],
    'tbl_kpi_agua_potable' => ['Agua Potable',            '% cumplimiento lavado de tanques'],
];
foreach ($kpis as $tabla => [$label, $indicador]) {
    echo "\n=== KPI {$label} ===\n";
    $m->query("DELETE FROM {$tabla} WHERE id_cliente={$ID_CLIENTE} AND observaciones LIKE '%{$MARK}%'");
    for ($i = 0; $i < 3; $i++) {
        $fecha = date('Y-m-d', strtotime("-" . ($i*30) . " days"));
        $num = rand(70, 100);
        $den = 100;
        $cump = $num;
        $obs = "{$MARK} KPI demo #" . ($i+1);
        $sql = "INSERT INTO {$tabla} (id_cliente, id_consultor, fecha_inspeccion, nombre_responsable, indicador, cumplimiento, valor_numerador, valor_denominador, calificacion_cualitativa, observaciones, estado) VALUES ({$ID_CLIENTE}, {$ID_CONSULTOR}, '{$fecha}', 'Responsable demo', '{$indicador}', {$cump}, {$num}, {$den}, 'BUENO', '{$obs}', 'completo')";
        if (!$m->query($sql)) echo "  ERR: " . $m->error . "\n";
    }
    echo "  + 3 registros\n";
}

// ============================================================
// 17. ASISTENCIA INDUCCIÓN
// ============================================================
echo "\n=== Asistencia Inducción ===\n";
$m->query("DELETE FROM tbl_asistencia_induccion WHERE id_cliente={$ID_CLIENTE} AND observaciones LIKE '%{$MARK}%'");
$sesiones = [
    ['Inducción general SG-SST',        'induccion_reinduccion', 2.0],
    ['Manipulación de alimentos',        'capacitacion',          1.5],
    ['Uso correcto de EPP',              'capacitacion',          1.0],
];
foreach ($sesiones as $idx => $s) {
    $fecha = date('Y-m-d', strtotime("-" . ($idx*25) . " days"));
    $obs = "{$MARK} Sesión demo #" . ($idx+1);
    $sql = "INSERT INTO tbl_asistencia_induccion (id_cliente, id_consultor, fecha_sesion, tema, lugar, objetivo, capacitador, tipo_charla, tiempo_horas, observaciones, estado) VALUES ({$ID_CLIENTE}, {$ID_CONSULTOR}, '{$fecha}', '{$s[0]}', 'Sede principal', 'Capacitar al personal', 'Consultor SST', '{$s[1]}', {$s[2]}, '{$obs}', 'completo')";
    if (!$m->query($sql)) echo "  ERR: " . $m->error . "\n";
    else echo "  + {$s[0]}\n";
}

// ============================================================
// 18. REPORTES DE CAPACITACIÓN
// ============================================================
echo "\n=== Reportes de Capacitación ===\n";
$m->query("DELETE FROM tbl_reporte_capacitacion WHERE id_cliente={$ID_CLIENTE} AND observaciones LIKE '%{$MARK}%'");
$caps = [
    ['Primeros auxilios básicos',      'Personal de caja',    8, 6],
    ['Prevención de accidentes',       'Todo el personal',    10, 9],
    ['BPM — Buenas Prácticas de Manufactura', 'Manipuladores', 5, 5],
];
foreach ($caps as $idx => $c) {
    $fecha = date('Y-m-d', strtotime("-" . ($idx*20 + 5) . " days"));
    $obs = "{$MARK} Capacitación demo #" . ($idx+1);
    $foto1 = nextFoto(); $foto2 = nextFoto();
    $sql = "INSERT INTO tbl_reporte_capacitacion (id_cliente, id_consultor, fecha_capacitacion, nombre_capacitacion, objetivo_capacitacion, perfil_asistentes, nombre_capacitador, horas_duracion, numero_asistentes, numero_programados, foto_listado_asistencia, foto_capacitacion, observaciones, estado) VALUES ({$ID_CLIENTE}, {$ID_CONSULTOR}, '{$fecha}', '{$c[0]}', 'Formar al personal', '{$c[1]}', 'Consultor SST', 2.0, {$c[3]}, {$c[2]}, '{$foto1}', '{$foto2}', '{$obs}', 'completo')";
    if (!$m->query($sql)) echo "  ERR: " . $m->error . "\n";
    else echo "  + {$c[0]}\n";
}

// ============================================================
// 19. PENDIENTES (derivados de actas)
// ============================================================
echo "\n=== Pendientes ===\n";
$m->query("DELETE FROM tbl_pendientes WHERE id_cliente={$ID_CLIENTE} AND tarea_actividad LIKE '%{$MARK}%'");
$actas = [];
$rs = $m->query("SELECT id FROM tbl_acta_visita WHERE id_cliente={$ID_CLIENTE} ORDER BY id DESC LIMIT 3");
while ($row = $rs->fetch_assoc()) $actas[] = (int)$row['id'];
$tareas = [
    'Entregar soportes de manipulación de alimentos al consultor',
    'Realizar mantenimiento preventivo a neveras',
    'Capacitar al personal en uso de extintores',
];
foreach ($tareas as $i => $t) {
    $idActa = $actas[$i % max(1,count($actas))] ?? 0;
    $resp = 'Tendero';
    $tarea = "{$MARK} " . $m->real_escape_string($t);
    $m->query("INSERT INTO tbl_pendientes (id_cliente, id_acta, responsable, tarea_actividad, estado, id_acta_visita) VALUES ({$ID_CLIENTE}, '{$idActa}', '{$resp}', '{$tarea}', 'ABIERTA', {$idActa})");
    echo "  + {$t}\n";
}

// ============================================================
// 20. CERTIFICADOS DE SERVICIO (lavado tanques, fumigación, desratización)
// ============================================================
echo "\n=== Certificados de servicio ===\n";
$m->query("DELETE FROM tbl_certificado_servicio WHERE id_cliente={$ID_CLIENTE} AND observaciones LIKE '%{$MARK}%'");
$servicios = [
    2 => 'Lavado de tanques',
    3 => 'Fumigación',
    4 => 'Desratización',
];
foreach ($servicios as $idM => $label) {
    for ($i = 0; $i < 3; $i++) {
        $fecha = date('Y-m-d', strtotime("-" . ($i*60) . " days"));
        $obs = "{$MARK} Certificado {$label} demo #" . ($i+1);
        $foto = nextFoto();
        $m->query("INSERT INTO tbl_certificado_servicio (id_cliente, id_mantenimiento, fecha_servicio, archivo, observaciones, id_consultor) VALUES ({$ID_CLIENTE}, {$idM}, '{$fecha}', '{$foto}', '{$obs}', {$ID_CONSULTOR})");
    }
    echo "  + {$label}: 3 certificados\n";
}

// ============================================================
// 21. PLANILLAS SEGURIDAD SOCIAL (global, sin id_cliente)
// ============================================================
echo "\n=== Planilla SS ===\n";
$m->query("DELETE FROM tbl_planillas_seguridad_social WHERE notas LIKE '%{$MARK}%'");
$meses = [date('Y-m', strtotime('-0 months')), date('Y-m', strtotime('-1 months')), date('Y-m', strtotime('-2 months'))];
foreach ($meses as $mes) {
    $foto = nextFoto();
    $notas = "{$MARK} Planilla demo";
    $m->query("INSERT INTO tbl_planillas_seguridad_social (mes_aportes, archivo_pdf, cantidad_envios, estado_envio, notas) VALUES ('{$mes}', '{$foto}', 0, 'sin_enviar', '{$notas}')");
    echo "  + Planilla {$mes}\n";
}

// ============================================================
// 22. PROVEEDORES DE SERVICIO
// ============================================================
echo "\n=== Proveedores de servicio ===\n";
$m->query("DELETE FROM tbl_proveedor_servicio WHERE id_cliente={$ID_CLIENTE} AND razon_social LIKE '%{$MARK}%'");
$provs = [
    ['fumigacion',   "Fumigadora Protección Total SAS {$MARK}", '900123456', 'contacto@fumigadora.com', '3001111111'],
    ['aseo',         "Aseo Integral del Valle Ltda {$MARK}",     '900123457', 'contacto@aseointegral.com', '3001111112'],
    ['mantenimiento',"Mantenimiento y Servicios Técnicos {$MARK}", '900123458', 'contacto@mantservicios.com', '3001111113'],
];
foreach ($provs as $p) {
    $razon = $m->real_escape_string($p[1]);
    $m->query("INSERT INTO tbl_proveedor_servicio (id_cliente, tipo_servicio, estado, razon_social, nit, email_empresa, telefono_empresa, nombre_responsable_sst, email_responsable_sst, cargo_responsable_sst, telefono_responsable_sst, id_consultor, created_at, updated_at) VALUES ({$ID_CLIENTE}, '{$p[0]}', 'activo', '{$razon}', '{$p[2]}', '{$p[3]}', '{$p[4]}', 'Responsable SST demo', 'sst@empresa.com', 'Coordinador SST', '3009999999', {$ID_CONSULTOR}, NOW(), NOW())");
    echo "  + {$p[1]}\n";
}

// ============================================================
// 22.5 EVALUACIONES DE INDUCCIÓN
// ============================================================
echo "\n=== Evaluaciones de Inducción ===\n";
$m->query("DELETE FROM tbl_evaluaciones WHERE id_cliente={$ID_CLIENTE} AND titulo LIKE '%{$MARK}%'");
$asist = [];
$rs = $m->query("SELECT id FROM tbl_asistencia_induccion WHERE id_cliente={$ID_CLIENTE} ORDER BY id DESC LIMIT 3");
while ($row = $rs->fetch_assoc()) $asist[] = (int)$row['id'];
$temas = ['Inducción SG-SST', 'Manipulación de alimentos', 'Uso correcto de EPP'];
foreach ($temas as $i => $titulo) {
    $idAsist = $asist[$i] ?? 0;
    $tituloEsc = "{$MARK} " . $m->real_escape_string($titulo);
    $token = bin2hex(random_bytes(12));
    $m->query("INSERT INTO tbl_evaluaciones (id_asistencia_induccion, id_cliente, id_tema, titulo, token, estado) VALUES ({$idAsist}, {$ID_CLIENTE}, 1, '{$tituloEsc}', '{$token}', 'activo')");
    echo "  + {$titulo}\n";
}

// ============================================================
// 23. AUDITORÍA ZONA RESIDUOS
// ============================================================
echo "\n=== Auditoría Zona Residuos ===\n";
$m->query("DELETE FROM tbl_auditoria_zona_residuos WHERE id_cliente={$ID_CLIENTE} AND observaciones LIKE '%{$MARK}%'");
for ($i = 0; $i < 3; $i++) {
    $fecha = date('Y-m-d', strtotime("-" . ($i*25) . " days"));
    $obs = "{$MARK} Auditoría zona residuos demo #" . ($i+1);
    $fotos = [];
    for ($j = 0; $j < 12; $j++) $fotos[] = nextFoto();
    $sql = "INSERT INTO tbl_auditoria_zona_residuos (id_cliente, id_consultor, fecha_inspeccion,
        estado_acceso, foto_acceso,
        estado_techo_pared_pisos, foto_techo_pared_pisos,
        estado_ventilacion, foto_ventilacion,
        estado_prevencion_incendios, foto_prevencion_incendios,
        estado_drenajes, foto_drenajes,
        proliferacion_plagas, foto_proliferacion_plagas,
        estado_recipientes, foto_recipientes,
        estado_reciclaje, foto_reciclaje,
        estado_iluminarias, foto_iluminarias,
        estado_senalizacion, foto_senalizacion,
        estado_limpieza_desinfeccion, foto_limpieza_desinfeccion,
        estado_poseta, foto_poseta,
        observaciones, estado)
        VALUES ({$ID_CLIENTE}, {$ID_CONSULTOR}, '{$fecha}',
        'bueno', '{$fotos[0]}',
        'bueno', '{$fotos[1]}',
        'bueno', '{$fotos[2]}',
        'bueno', '{$fotos[3]}',
        'regular', '{$fotos[4]}',
        'No se observa proliferación de plagas', '{$fotos[5]}',
        'bueno', '{$fotos[6]}',
        'bueno', '{$fotos[7]}',
        'bueno', '{$fotos[8]}',
        'bueno', '{$fotos[9]}',
        'bueno', '{$fotos[10]}',
        'bueno', '{$fotos[11]}',
        '{$obs}', 'completo')";
    if (!$m->query($sql)) echo "  ERR: " . $m->error . "\n";
}
echo "  + 3 auditorías con 12 fotos cada una\n";

echo "\n=== RESUMEN ===\n";
foreach ([
    'tbl_inspeccion_nevera','tbl_inspeccion_limpieza_local','tbl_inspeccion_equipos',
    'tbl_inspeccion_contaminacion','tbl_inspeccion_almacenamiento','tbl_recepcion_mp',
    'tbl_trabajadores','tbl_bomberos_solicitud','tbl_inspeccion_botiquin','tbl_inspeccion_botiquin_tipo_a',
    'tbl_acta_visita','tbl_inspeccion_locativa','tbl_inspeccion_extintores',
    'tbl_carta_vigia','tbl_vencimientos_mantenimientos',
    'tbl_programa_limpieza','tbl_programa_residuos','tbl_programa_plagas','tbl_programa_agua_potable','tbl_plan_saneamiento',
    'tbl_kpi_limpieza','tbl_kpi_residuos','tbl_kpi_plagas','tbl_kpi_agua_potable',
    'tbl_asistencia_induccion','tbl_reporte_capacitacion',
    'tbl_pendientes','tbl_certificado_servicio','tbl_proveedor_servicio','tbl_auditoria_zona_residuos','tbl_evaluaciones'
] as $t) {
    $r = $m->query("SELECT COUNT(*) c FROM {$t} WHERE id_cliente={$ID_CLIENTE}")->fetch_assoc();
    echo str_pad($t,42) . 'total=' . $r['c'] . "\n";
}
echo "\nOK.\n";

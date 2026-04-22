<?php
/**
 * TAT Fase 5.2 — Inspección de Aseo (Limpieza del Local).
 *
 * Crea:
 *   - tbl_limpieza_item               (catálogo administrado por consultor; globales + por cliente)
 *   - tbl_inspeccion_limpieza_local   (cabecera de cada inspección)
 *   - tbl_inspeccion_limpieza_detalle (1 fila por item evaluado)
 *
 * Siembra:
 *   - 12 items globales iniciales
 *   - report_type_table: "Inspección de Aseo"
 *   - detail_report: "Registro Inspección de Aseo"
 *
 * Estados de item: limpio / sucio / no_aplica  (3-estados, decisión usuario 2026-04-20).
 */

if (php_sapi_name() !== 'cli') die('CLI only');

$env = $argv[1] ?? 'local';
if ($env === 'local') {
    $config = ['host'=>'127.0.0.1','port'=>3306,'user'=>'root','password'=>'','database'=>'tat_cycloid','ssl'=>false];
} elseif ($env === 'production') {
    $config = [
        'host'=>'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'=>25060,'user'=>'cycloid_userdb',
        'password'=>getenv('DB_PROD_PASS') ?: '',
        'database'=>'tat_cycloid','ssl'=>true,
    ];
    if (empty($config['password'])) die("DB_PROD_PASS no establecido\n");
} else die("Uso: [local|production]\n");

echo "=== Migración TAT Fase 5.2 - Inspección de Aseo ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$m = mysqli_init();
if ($config['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,false); }
if (!@$m->real_connect($config['host'],$config['user'],$config['password'],$config['database'],$config['port'],null,$config['ssl']?MYSQLI_CLIENT_SSL:0)) {
    die("ERR: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');
echo "Conectado.\n\n";

$errors = 0;

/* -------- Catálogo -------- */
$sql = "CREATE TABLE IF NOT EXISTS `tbl_limpieza_item` (
    `id_item` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(150) NOT NULL,
    `descripcion` VARCHAR(500) NULL,
    `icono` VARCHAR(50) NULL,
    `orden` INT NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `id_cliente` INT NULL COMMENT 'NULL = global; con valor = específico de ese cliente',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_limp_item_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_limp_item_activo` (`activo`),
    INDEX `idx_limp_item_cliente` (`id_cliente`),
    INDEX `idx_limp_item_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_limpieza_item]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* -------- Cabecera de inspección -------- */
$sql = "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_limpieza_local` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `fecha_hora` DATETIME NOT NULL,
    `observaciones_generales` TEXT NULL,
    `resultado_general` ENUM('ok','no_conforme') NOT NULL DEFAULT 'ok',
    `id_reporte` INT NULL,
    `registrado_por` ENUM('cliente','consultor') NOT NULL DEFAULT 'cliente',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_insp_limp_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_insp_limp_cliente` (`id_cliente`),
    INDEX `idx_insp_limp_fecha` (`fecha_hora`),
    INDEX `idx_insp_limp_resultado` (`resultado_general`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_inspeccion_limpieza_local]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* -------- Detalle (1 fila por item) -------- */
$sql = "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_limpieza_detalle` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_inspeccion` INT NOT NULL,
    `id_item` INT NOT NULL,
    `estado` ENUM('limpio','sucio','no_aplica') NOT NULL,
    `foto` VARCHAR(255) NULL,
    `observaciones` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_insp_limp_det_insp`
        FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_limpieza_local`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_insp_limp_det_item`
        FOREIGN KEY (`id_item`) REFERENCES `tbl_limpieza_item`(`id_item`)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX `idx_insp_limp_det_insp` (`id_inspeccion`),
    INDEX `idx_insp_limp_det_item` (`id_item`),
    INDEX `idx_insp_limp_det_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_inspeccion_limpieza_detalle]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* -------- Seed 12 items globales -------- */
echo "\n[SEED catálogo items (12 globales)]...\n";
$items = [
    ['Techos',                          'Revisar polvo, telarañas, humedad',                  'fa-arrow-up',          10],
    ['Paredes',                         'Manchas, polvo, integridad de la pintura',           'fa-border-style',      20],
    ['Pisos',                           'Barrido, trapeado, sin residuos',                    'fa-broom',             30],
    ['Ventanas y vidrios',              'Limpieza interna y externa, sin marcas',             'fa-window-maximize',   40],
    ['Estanterías',                     'Sin polvo, organizadas, sin residuos',               'fa-boxes-stacked',     50],
    ['Canecas / Puntos ecológicos',     'Bolsas en buen estado, separación por color',        'fa-trash',             60],
    ['Baño',                            'Sanitario, lavamanos, pisos, olores',                'fa-toilet',            70],
    ['Cuarto de residuos',              'Sin acumulación fuera de canecas, aseo diario',      'fa-dumpster',          80],
    ['Zona de venta / Mostrador',       'Superficie de atención limpia y ordenada',           'fa-cash-register',     90],
    ['Lavamanos y grifería',            'Agua corriente, jabón, secado, sin sarro',           'fa-faucet',           100],
    ['Utensilios e implementos de aseo','Escobas, traperos, paños en buen estado',            'fa-spray-can',        110],
    ['Nevera (exterior)',               'Exterior de neveras: puerta, empaques, sin polvo',   'fa-snowflake',        120],
];
$stmt = $m->prepare("INSERT IGNORE INTO tbl_limpieza_item (nombre, descripcion, icono, orden, activo, id_cliente) VALUES (?, ?, ?, ?, 1, NULL)");
$okIns = 0; $dupIns = 0;
foreach ($items as [$nombre, $desc, $icono, $orden]) {
    $stmt->bind_param('sssi', $nombre, $desc, $icono, $orden);
    $stmt->execute();
    if ($m->affected_rows > 0) { echo "  + {$nombre}\n"; $okIns++; }
    else { $dupIns++; }
}
$stmt->close();
echo "Insertados: {$okIns}, ya existían: {$dupIns}\n";

/* -------- Seed report_type_table + detail_report -------- */
echo "\n[SEED report_type_table 'Inspección de Aseo']... ";
$chk = $m->query("SELECT id_report_type FROM report_type_table WHERE report_type = 'Inspección de Aseo'");
if ($chk->num_rows === 0) {
    if ($m->query("INSERT INTO report_type_table (report_type) VALUES ('Inspección de Aseo')")) {
        echo "insertado (id=" . $m->insert_id . ")\n";
    } else { echo "ERR: {$m->error}\n"; $errors++; }
} else echo "ya existe.\n";

echo "\n[SEED detail_report]... ";
$d = 'Registro Inspección de Aseo';
$stmt2 = $m->prepare("INSERT IGNORE INTO detail_report (detail_report) VALUES (?)");
$stmt2->bind_param('s', $d);
$stmt2->execute();
echo ($m->affected_rows > 0 ? "+ {$d}" : "ya existe") . "\n";
$stmt2->close();

echo "\n=== RESULTADO ===\n";
echo "Errores: {$errors}\n";
echo $errors === 0 ? "MIGRACIÓN COMPLETADA SIN ERRORES.\n" : "HAY ERRORES - REVISAR.\n";
exit($errors === 0 ? 0 : 1);

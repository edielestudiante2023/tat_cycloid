<?php
/**
 * TAT Fase 5.3cd — POES 4.2 Contaminación Cruzada + POES 4.4 Almacenamiento.
 *
 * Crea 6 tablas (3 para cada POES: catálogo + cabecera + detalle).
 * Siembra 10 items para cada módulo + report_type + detail_report.
 *
 * Estados: cumple / no_cumple / no_aplica.
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

echo "=== TAT Fase 5.3cd — Contaminación + Almacenamiento ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$m = mysqli_init();
if ($config['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,false); }
if (!@$m->real_connect($config['host'],$config['user'],$config['password'],$config['database'],$config['port'],null,$config['ssl']?MYSQLI_CLIENT_SSL:0)) {
    die("ERR: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');
echo "Conectado.\n\n";

$errors = 0;

/* ============ POES 4.2 CONTAMINACIÓN CRUZADA ============ */

$sql = "CREATE TABLE IF NOT EXISTS `tbl_contaminacion_item` (
    `id_item` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(200) NOT NULL,
    `descripcion` VARCHAR(500) NULL,
    `icono` VARCHAR(50) NULL,
    `orden` INT NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `id_cliente` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cont_item_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_cont_item_activo` (`activo`),
    INDEX `idx_cont_item_cliente` (`id_cliente`),
    INDEX `idx_cont_item_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_contaminacion_item]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

$sql = "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_contaminacion` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `fecha_hora` DATETIME NOT NULL,
    `observaciones_generales` TEXT NULL,
    `resultado_general` ENUM('ok','no_conforme') NOT NULL DEFAULT 'ok',
    `id_reporte` INT NULL,
    `registrado_por` ENUM('cliente','consultor') NOT NULL DEFAULT 'cliente',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_insp_cont_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_insp_cont_cliente` (`id_cliente`),
    INDEX `idx_insp_cont_fecha` (`fecha_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_inspeccion_contaminacion]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

$sql = "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_contaminacion_detalle` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_inspeccion` INT NOT NULL,
    `id_item` INT NOT NULL,
    `estado` ENUM('cumple','no_cumple','no_aplica') NOT NULL,
    `foto` VARCHAR(255) NULL,
    `observaciones` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_insp_cont_det_insp`
        FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_contaminacion`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_insp_cont_det_item`
        FOREIGN KEY (`id_item`) REFERENCES `tbl_contaminacion_item`(`id_item`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_insp_cont_det_insp` (`id_inspeccion`),
    INDEX `idx_insp_cont_det_item` (`id_item`),
    INDEX `idx_insp_cont_det_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_inspeccion_contaminacion_detalle]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* ============ POES 4.4 ALMACENAMIENTO ============ */

$sql = "CREATE TABLE IF NOT EXISTS `tbl_almacenamiento_item` (
    `id_item` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(200) NOT NULL,
    `descripcion` VARCHAR(500) NULL,
    `icono` VARCHAR(50) NULL,
    `orden` INT NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `id_cliente` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_almc_item_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_almc_item_activo` (`activo`),
    INDEX `idx_almc_item_cliente` (`id_cliente`),
    INDEX `idx_almc_item_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_almacenamiento_item]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

$sql = "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_almacenamiento` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `fecha_hora` DATETIME NOT NULL,
    `observaciones_generales` TEXT NULL,
    `resultado_general` ENUM('ok','no_conforme') NOT NULL DEFAULT 'ok',
    `id_reporte` INT NULL,
    `registrado_por` ENUM('cliente','consultor') NOT NULL DEFAULT 'cliente',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_insp_almc_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_insp_almc_cliente` (`id_cliente`),
    INDEX `idx_insp_almc_fecha` (`fecha_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_inspeccion_almacenamiento]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

$sql = "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_almacenamiento_detalle` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_inspeccion` INT NOT NULL,
    `id_item` INT NOT NULL,
    `estado` ENUM('cumple','no_cumple','no_aplica') NOT NULL,
    `foto` VARCHAR(255) NULL,
    `observaciones` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_insp_almc_det_insp`
        FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_almacenamiento`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_insp_almc_det_item`
        FOREIGN KEY (`id_item`) REFERENCES `tbl_almacenamiento_item`(`id_item`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_insp_almc_det_insp` (`id_inspeccion`),
    INDEX `idx_insp_almc_det_item` (`id_item`),
    INDEX `idx_insp_almc_det_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_inspeccion_almacenamiento_detalle]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* ============ SEED ITEMS CONTAMINACIÓN (10) ============ */
echo "\n[SEED items contaminación cruzada]...\n";
$itemsCont = [
    ['Separación física cárnicos/lácteos/vegetales', 'Alimentos diferentes en áreas separadas para evitar contaminación', 'fa-border-all', 10],
    ['Tablas de picar diferenciadas',                 'Color / rotulado por tipo de alimento',                            'fa-cutting-board', 20],
    ['Cambio de utensilios crudos↔cocidos',           'No reutilizar sin lavado intermedio',                              'fa-utensils',     30],
    ['Lavado de manos entre tareas',                  'Al cambiar de producto o de área',                                 'fa-hand-sparkles',40],
    ['Cambio/limpieza de delantal entre tareas',      'Uso correcto y limpieza o cambio al pasar de crudo a listo',      'fa-user-nurse',   50],
    ['Uso de cofia, tapabocas, guantes',              'Dotación completa del manipulador',                                'fa-head-side-mask',60],
    ['Limpieza de superficies entre preparaciones',   'Desinfección de mesones/mostradores',                              'fa-spray-can-sparkles',70],
    ['Almacenamiento crudos vs listos separados',     'Crudos NO encima de alimentos listos para consumo',                'fa-layer-group',  80],
    ['Control de temperatura durante preparación',    'No dejar alimentos perecederos fuera de refrigeración mucho tiempo','fa-temperature-half',90],
    ['Residuos fuera de zona de preparación',         'Basura en tapas cerradas, fuera del área crítica',                 'fa-trash-arrow-up',100],
];
$stmt = $m->prepare("INSERT IGNORE INTO tbl_contaminacion_item (nombre, descripcion, icono, orden, activo, id_cliente) VALUES (?, ?, ?, ?, 1, NULL)");
$okC = 0;
foreach ($itemsCont as [$n, $d, $ic, $o]) {
    $stmt->bind_param('sssi', $n, $d, $ic, $o);
    $stmt->execute();
    if ($m->affected_rows > 0) { echo "  + {$n}\n"; $okC++; }
}
$stmt->close();
echo "Insertados: {$okC}\n";

/* ============ SEED ITEMS ALMACENAMIENTO (10) ============ */
echo "\n[SEED items almacenamiento]...\n";
$itemsAlm = [
    ['Productos en estanterías elevadas (no en el piso)', 'Mínimo 15 cm del piso', 'fa-layer-group', 10],
    ['FIFO: más antiguos al frente',                        'Rotación por fecha de recepción',                            'fa-arrows-rotate',20],
    ['Sin productos vencidos',                              'Revisar fechas de vencimiento periódicamente',                'fa-calendar-xmark',30],
    ['Temperatura adecuada de despensa/bodega',             'Zona seca y ventilada, sin humedad excesiva',                 'fa-temperature-arrow-down',40],
    ['Sin humedad ni filtraciones',                         'Techos, paredes y pisos sin rastros de agua',                 'fa-droplet-slash',50],
    ['Químicos separados de alimentos',                     'Productos de aseo en área diferente',                         'fa-flask',        60],
    ['Envases bien cerrados o rotulados',                   'Sin exposición al aire, protegidos de plagas',                'fa-box',          70],
    ['Etiquetado claro (producto + fecha)',                 'Con fecha de apertura / vencimiento visible',                  'fa-tag',          80],
    ['Aseo del área de almacenamiento',                     'Libre de polvo, telarañas, residuos de alimentos',            'fa-broom',        90],
    ['Sin presencia de plagas ni rastros',                  'No excrementos, no insectos, no roedores',                    'fa-bug-slash',   100],
];
$stmt = $m->prepare("INSERT IGNORE INTO tbl_almacenamiento_item (nombre, descripcion, icono, orden, activo, id_cliente) VALUES (?, ?, ?, ?, 1, NULL)");
$okA = 0;
foreach ($itemsAlm as [$n, $d, $ic, $o]) {
    $stmt->bind_param('sssi', $n, $d, $ic, $o);
    $stmt->execute();
    if ($m->affected_rows > 0) { echo "  + {$n}\n"; $okA++; }
}
$stmt->close();
echo "Insertados: {$okA}\n";

/* ============ SEEDS reportlist ============ */
echo "\n[SEED report_type_table]...\n";
foreach (['POES Contaminación Cruzada', 'POES Almacenamiento'] as $rt) {
    $chk = $m->query("SELECT id_report_type FROM report_type_table WHERE report_type = '" . $m->real_escape_string($rt) . "'");
    if ($chk->num_rows === 0) {
        $ins = $m->prepare("INSERT INTO report_type_table (report_type) VALUES (?)");
        $ins->bind_param('s', $rt);
        $ins->execute();
        echo "  + {$rt} (id=" . $m->insert_id . ")\n";
        $ins->close();
    } else echo "  = {$rt} ya existe\n";
}

echo "\n[SEED detail_report]...\n";
$stmt = $m->prepare("INSERT IGNORE INTO detail_report (detail_report) VALUES (?)");
foreach (['Registro Contaminación Cruzada','Registro Almacenamiento'] as $d) {
    $stmt->bind_param('s', $d);
    $stmt->execute();
    echo "  " . ($m->affected_rows > 0 ? '+ ' : '= ') . $d . "\n";
}
$stmt->close();

echo "\n=== RESULTADO ===\n";
echo "Errores: {$errors}\n";
echo $errors === 0 ? "MIGRACIÓN COMPLETADA.\n" : "HAY ERRORES.\n";
exit($errors === 0 ? 0 : 1);

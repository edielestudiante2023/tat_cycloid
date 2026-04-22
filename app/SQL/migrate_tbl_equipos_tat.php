<?php
/**
 * TAT Fase 5.3a — Inspección de Condiciones de Equipos y Utensilios.
 *
 * Crea:
 *   - tbl_equipos_item                 (catálogo: items globales + específicos por cliente)
 *   - tbl_inspeccion_equipos           (cabecera)
 *   - tbl_inspeccion_equipos_detalle   (1 fila por item evaluado)
 *
 * Siembra:
 *   - 15 items globales iniciales (Res. 2674/2013, Bloque 2)
 *   - report_type_table: "Condiciones de Equipos y Utensilios"
 *   - detail_report: "Registro Equipos y Utensilios"
 *
 * Estados de item: funcional / defectuoso / no_aplica.
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

echo "=== TAT Fase 5.3a - Equipos y Utensilios ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$m = mysqli_init();
if ($config['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,false); }
if (!@$m->real_connect($config['host'],$config['user'],$config['password'],$config['database'],$config['port'],null,$config['ssl']?MYSQLI_CLIENT_SSL:0)) {
    die("ERR: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');
echo "Conectado.\n\n";

$errors = 0;

/* ------ Catálogo ------ */
$sql = "CREATE TABLE IF NOT EXISTS `tbl_equipos_item` (
    `id_item` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(150) NOT NULL,
    `descripcion` VARCHAR(500) NULL,
    `icono` VARCHAR(50) NULL,
    `orden` INT NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `id_cliente` INT NULL COMMENT 'NULL = global; con valor = específico',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_equip_item_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_equip_item_activo` (`activo`),
    INDEX `idx_equip_item_cliente` (`id_cliente`),
    INDEX `idx_equip_item_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_equipos_item]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* ------ Cabecera ------ */
$sql = "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_equipos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `fecha_hora` DATETIME NOT NULL,
    `observaciones_generales` TEXT NULL,
    `resultado_general` ENUM('ok','no_conforme') NOT NULL DEFAULT 'ok',
    `id_reporte` INT NULL,
    `registrado_por` ENUM('cliente','consultor') NOT NULL DEFAULT 'cliente',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_insp_equip_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_insp_equip_cliente` (`id_cliente`),
    INDEX `idx_insp_equip_fecha` (`fecha_hora`),
    INDEX `idx_insp_equip_resultado` (`resultado_general`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_inspeccion_equipos]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* ------ Detalle ------ */
$sql = "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_equipos_detalle` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_inspeccion` INT NOT NULL,
    `id_item` INT NOT NULL,
    `estado` ENUM('funcional','defectuoso','no_aplica') NOT NULL,
    `foto` VARCHAR(255) NULL,
    `observaciones` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_insp_equip_det_insp`
        FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_equipos`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_insp_equip_det_item`
        FOREIGN KEY (`id_item`) REFERENCES `tbl_equipos_item`(`id_item`)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX `idx_insp_equip_det_insp` (`id_inspeccion`),
    INDEX `idx_insp_equip_det_item` (`id_item`),
    INDEX `idx_insp_equip_det_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_inspeccion_equipos_detalle]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* ------ Seed 15 items globales estándar ------ */
echo "\n[SEED items globales]...\n";
$items = [
    ['Cuchillos y cortantes',             'Filos, mangos firmes, sin óxido',                   'fa-knife-kitchen',     10],
    ['Tabla de picar',                    'Superficie sin cortes profundos, limpia, sin olor', 'fa-cutting-board',     20],
    ['Licuadora / Procesador',            'Funcionamiento motor, cuchillas, vaso',             'fa-blender',           30],
    ['Báscula / Balanza',                 'Calibración, pantalla legible, plato limpio',       'fa-scale-balanced',    40],
    ['Cortadora de embutidos',            'Cuchilla afilada, guardas de seguridad, empuje',    'fa-sliders',           50],
    ['Horno / Estufa / Plancha',          'Encendido, temperatura, perillas, parrillas',       'fa-fire-burner',       60],
    ['Batidora / Amasadora',              'Motor, accesorios, cuenco, base estable',           'fa-blender-phone',     70],
    ['Rebanadora de pan',                 'Cuchillas, protección, funcionamiento',             'fa-bread-slice',       80],
    ['Recipientes de almacenamiento',     'Tapas, sin grietas, aptos uso alimentario',         'fa-box',               90],
    ['Superficies de mostrador / prep.',  'Material apto contacto alimento, sin grietas',      'fa-table-cells',      100],
    ['Utensilios de servicio',            'Pinzas, cucharones, espátulas en buen estado',      'fa-utensils',         110],
    ['Sartenes y ollas',                  'Sin hundimientos, mangos firmes, sin residuos',     'fa-pot-food',         120],
    ['Lavaplatos / Lavavajillas',         'Funcionamiento, desagüe, ausencia de fugas',        'fa-sink',             130],
    ['Ralladores y coladores',            'Malla íntegra, sin óxido',                          'fa-filter',           140],
    ['Vitrina / Exhibidor de alimentos',  'Vidrios, iluminación, temperatura si refrigerada',  'fa-store',            150],
];
$stmt = $m->prepare("INSERT IGNORE INTO tbl_equipos_item (nombre, descripcion, icono, orden, activo, id_cliente) VALUES (?, ?, ?, ?, 1, NULL)");
$ok = 0; $dup = 0;
foreach ($items as [$n, $d, $ic, $o]) {
    $stmt->bind_param('sssi', $n, $d, $ic, $o);
    $stmt->execute();
    if ($m->affected_rows > 0) { echo "  + {$n}\n"; $ok++; }
    else { $dup++; }
}
$stmt->close();
echo "Insertados: {$ok}, ya existían: {$dup}\n";

/* ------ Seeds reportlist ------ */
echo "\n[SEED report_type_table 'Condiciones de Equipos y Utensilios']... ";
$chk = $m->query("SELECT id_report_type FROM report_type_table WHERE report_type = 'Condiciones de Equipos y Utensilios'");
if ($chk->num_rows === 0) {
    if ($m->query("INSERT INTO report_type_table (report_type) VALUES ('Condiciones de Equipos y Utensilios')")) {
        echo "insertado (id=" . $m->insert_id . ")\n";
    } else { echo "ERR: {$m->error}\n"; $errors++; }
} else echo "ya existe.\n";

echo "\n[SEED detail_report]... ";
$d = 'Registro Equipos y Utensilios';
$stmt2 = $m->prepare("INSERT IGNORE INTO detail_report (detail_report) VALUES (?)");
$stmt2->bind_param('s', $d);
$stmt2->execute();
echo ($m->affected_rows > 0 ? "+ {$d}" : "ya existe") . "\n";
$stmt2->close();

echo "\n=== RESULTADO ===\n";
echo "Errores: {$errors}\n";
echo $errors === 0 ? "MIGRACIÓN COMPLETADA.\n" : "HAY ERRORES.\n";
exit($errors === 0 ? 0 : 1);

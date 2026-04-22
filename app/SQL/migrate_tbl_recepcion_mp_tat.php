<?php
/**
 * TAT Fase 5.3b — POES 4.1 Control de Materias Primas.
 *
 * Crea:
 *   - tbl_proveedor        (catálogo por cliente)
 *   - tbl_recepcion_mp     (una fila por recepción)
 *
 * Siembra reportlist:
 *   - report_type_table: "Recepción de Materias Primas"
 *   - detail_report: "Registro Recepción MP", "Rechazo Materia Prima"
 *
 * Estados del producto: aceptado (tinyint) + motivo_rechazo opcional.
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

echo "=== TAT Fase 5.3b - Recepción Materias Primas ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$m = mysqli_init();
if ($config['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,false); }
if (!@$m->real_connect($config['host'],$config['user'],$config['password'],$config['database'],$config['port'],null,$config['ssl']?MYSQLI_CLIENT_SSL:0)) {
    die("ERR: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');
echo "Conectado.\n\n";

$errors = 0;

/* -------- tbl_proveedor -------- */
$sql = "CREATE TABLE IF NOT EXISTS `tbl_proveedor` (
    `id_proveedor` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `nombre` VARCHAR(200) NOT NULL,
    `nit` VARCHAR(30) NULL,
    `telefono` VARCHAR(30) NULL,
    `direccion` VARCHAR(255) NULL,
    `categoria_principal` ENUM('carnicos','lacteos','frutas_verduras','panaderia','empacados','bebidas','congelados','otros') NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_prov_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_prov_cliente` (`id_cliente`),
    INDEX `idx_prov_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_proveedor]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* -------- tbl_recepcion_mp -------- */
$sql = "CREATE TABLE IF NOT EXISTS `tbl_recepcion_mp` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `id_proveedor` INT NULL,
    `proveedor_nombre` VARCHAR(200) NOT NULL COMMENT 'Backup si no hay FK o proveedor se eliminó',
    `fecha_hora` DATETIME NOT NULL,
    `producto` VARCHAR(200) NOT NULL,
    `categoria` ENUM('carnicos','lacteos','frutas_verduras','panaderia','empacados','bebidas','congelados','otros') NOT NULL,
    `cantidad` DECIMAL(10,2) NULL,
    `unidad` VARCHAR(20) NULL COMMENT 'kg, und, lt, caja',
    `numero_factura` VARCHAR(50) NULL,
    `fecha_vencimiento_producto` DATE NULL,
    `temperatura_recepcion` DECIMAL(5,1) NULL COMMENT 'Solo si aplica (cárnicos/lácteos/congelados)',
    `registro_sanitario` VARCHAR(100) NULL COMMENT 'INVIMA cuando aplica',
    `lote` VARCHAR(50) NULL,
    `empaque_ok` TINYINT(1) NOT NULL DEFAULT 1,
    `producto_ok` TINYINT(1) NOT NULL DEFAULT 1,
    `aceptado` TINYINT(1) NOT NULL DEFAULT 1,
    `motivo_rechazo` VARCHAR(500) NULL,
    `foto_producto` VARCHAR(255) NULL,
    `foto_factura` VARCHAR(255) NULL,
    `foto_temperatura` VARCHAR(255) NULL,
    `observaciones` TEXT NULL,
    `id_reporte` INT NULL,
    `registrado_por` ENUM('cliente','consultor') NOT NULL DEFAULT 'cliente',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_recmp_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_recmp_proveedor`
        FOREIGN KEY (`id_proveedor`) REFERENCES `tbl_proveedor`(`id_proveedor`)
        ON DELETE SET NULL ON UPDATE CASCADE,

    INDEX `idx_recmp_cliente` (`id_cliente`),
    INDEX `idx_recmp_fecha` (`fecha_hora`),
    INDEX `idx_recmp_aceptado` (`aceptado`),
    INDEX `idx_recmp_categoria` (`categoria`),
    INDEX `idx_recmp_vencimiento` (`fecha_vencimiento_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
echo "[CREATE tbl_recepcion_mp]... ";
if ($m->query($sql)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* -------- Seeds reportlist -------- */
echo "\n[SEED report_type_table]... ";
$chk = $m->query("SELECT id_report_type FROM report_type_table WHERE report_type = 'Recepción de Materias Primas'");
if ($chk->num_rows === 0) {
    if ($m->query("INSERT INTO report_type_table (report_type) VALUES ('Recepción de Materias Primas')")) {
        echo "insertado (id=" . $m->insert_id . ")\n";
    } else { echo "ERR: {$m->error}\n"; $errors++; }
} else echo "ya existe.\n";

echo "\n[SEED detail_report (2 tipos)]...\n";
$detalles = ['Registro Recepción MP', 'Rechazo Materia Prima'];
$stmt = $m->prepare("INSERT IGNORE INTO detail_report (detail_report) VALUES (?)");
foreach ($detalles as $d) {
    $stmt->bind_param('s', $d);
    $stmt->execute();
    echo "  " . ($m->affected_rows > 0 ? '+ ' : '= ') . $d . "\n";
}
$stmt->close();

echo "\n=== RESULTADO ===\n";
echo "Errores: {$errors}\n";
echo $errors === 0 ? "MIGRACIÓN COMPLETADA.\n" : "HAY ERRORES.\n";
exit($errors === 0 ? 0 : 1);

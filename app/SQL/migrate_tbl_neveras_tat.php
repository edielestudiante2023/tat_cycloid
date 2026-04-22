<?php
/**
 * TAT Fase 5.1 — Control de Neveras (tendero-driven).
 *
 * Crea:
 *   - tbl_nevera (catálogo de neveras del local)
 *   - tbl_inspeccion_nevera (registros de temperatura+humedad periódicos)
 *
 * Siembra:
 *   - report_type_table: "Control Neveras"
 *   - detail_report: 2 tipos (Registro Medición, Reporte Mensual Consolidado)
 *
 * Uso:
 *   php app/SQL/migrate_tbl_neveras_tat.php local
 *   DB_PROD_PASS=xxxxx php app/SQL/migrate_tbl_neveras_tat.php production
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

echo "=== Migración TAT Fase 5.1 - Control de Neveras ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$m = mysqli_init();
if ($config['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,false); }
if (!@$m->real_connect($config['host'],$config['user'],$config['password'],$config['database'],$config['port'],null,$config['ssl']?MYSQLI_CLIENT_SSL:0)) {
    die("ERR conexión: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');
echo "Conectado.\n\n";

$errors = 0;

/* -------- tbl_nevera (catálogo) -------- */
$sql1 = "CREATE TABLE IF NOT EXISTS `tbl_nevera` (
    `id_nevera` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `nombre` VARCHAR(150) NOT NULL COMMENT 'Ej: Nevera mostrador, Congelador bodega',
    `tipo` ENUM('refrigeracion','congelacion','mixta') NOT NULL DEFAULT 'refrigeracion',
    `ubicacion` VARCHAR(150) NULL,
    `rango_temp_min` DECIMAL(5,1) NOT NULL DEFAULT 0.0,
    `rango_temp_max` DECIMAL(5,1) NOT NULL DEFAULT 8.0,
    `controla_humedad` TINYINT(1) NOT NULL DEFAULT 0,
    `rango_humedad_min` DECIMAL(5,1) NULL,
    `rango_humedad_max` DECIMAL(5,1) NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_nevera_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_nevera_cliente` (`id_cliente`),
    INDEX `idx_nevera_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

echo "[CREATE tbl_nevera]... ";
if ($m->query($sql1)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* -------- tbl_inspeccion_nevera (registros) -------- */
$sql2 = "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_nevera` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `id_nevera` INT NOT NULL,
    `fecha_hora` DATETIME NOT NULL,
    `temperatura` DECIMAL(5,1) NOT NULL,
    `humedad_relativa` DECIMAL(5,1) NULL,
    `foto_evidencia` VARCHAR(255) NULL COMMENT 'Foto del termómetro/higrómetro',
    `observaciones` TEXT NULL,
    `dentro_rango` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Auto-calculado: 1 = dentro rango, 0 = fuera',
    `id_reporte` INT NULL COMMENT 'FK inversa a tbl_reporte si se generó reporte',
    `registrado_por` ENUM('cliente','consultor') NOT NULL DEFAULT 'cliente',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_insp_nev_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_insp_nev_nevera`
        FOREIGN KEY (`id_nevera`) REFERENCES `tbl_nevera`(`id_nevera`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_insp_nev_cliente` (`id_cliente`),
    INDEX `idx_insp_nev_nevera` (`id_nevera`),
    INDEX `idx_insp_nev_fecha` (`fecha_hora`),
    INDEX `idx_insp_nev_fuera_rango` (`dentro_rango`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

echo "[CREATE tbl_inspeccion_nevera]... ";
if ($m->query($sql2)) echo "OK\n"; else { echo "ERR: {$m->error}\n"; $errors++; }

/* -------- Sembrado report_type_table -------- */
echo "\n[SEED report_type_table 'Control Neveras']... ";
$check = $m->query("SELECT id_report_type FROM report_type_table WHERE report_type = 'Control Neveras'");
if ($check->num_rows === 0) {
    if ($m->query("INSERT INTO report_type_table (report_type) VALUES ('Control Neveras')")) {
        echo "insertado (id=" . $m->insert_id . ")\n";
    } else { echo "ERR: {$m->error}\n"; $errors++; }
} else echo "ya existe.\n";

/* -------- Sembrado detail_report -------- */
echo "\n[SEED detail_report (2 tipos Neveras)]...\n";
$detalles = [
    'Registro Control de Nevera',
    'Reporte Mensual Consolidado Neveras',
];
$stmtSeed = $m->prepare("INSERT IGNORE INTO detail_report (detail_report) VALUES (?)");
foreach ($detalles as $d) {
    $stmtSeed->bind_param('s', $d);
    if ($stmtSeed->execute()) {
        echo "  " . ($m->affected_rows > 0 ? '+ ' : '= ') . $d . "\n";
    } else { echo "  ERR: {$m->error}\n"; $errors++; }
}
$stmtSeed->close();

echo "\n=== RESULTADO ===\n";
echo "Errores: {$errors}\n";
echo $errors === 0 ? "MIGRACIÓN COMPLETADA SIN ERRORES.\n" : "HAY ERRORES - REVISAR.\n";
exit($errors === 0 ? 0 : 1);

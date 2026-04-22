<?php
/**
 * Script CLI - TAT Fase 4.1 — Módulo Trabajadores.
 *
 * Crea:
 *   - tbl_trabajadores
 *   - tbl_trabajador_soporte
 * Semilla:
 *   - report_type_table: "Trabajadores"
 *   - detail_report: 4 tipos de soporte
 *
 * Uso:
 *   php app/SQL/migrate_tbl_trabajadores_tat.php local
 *   DB_PROD_PASS=xxxxx php app/SQL/migrate_tbl_trabajadores_tat.php production
 *
 * Idempotente (CREATE IF NOT EXISTS + INSERT IGNORE).
 *
 * Documento: docs/migracion-tat/decisiones-alcance.md §3.2 (Trabajadores)
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos.');
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host' => '127.0.0.1', 'port' => 3306, 'user' => 'root',
        'password' => '', 'database' => 'tat_cycloid', 'ssl' => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060, 'user' => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'tat_cycloid', 'ssl' => true,
    ];
    if (empty($config['password'])) {
        die("ERROR: DB_PROD_PASS no establecido.\n");
    }
} else {
    die("Uso: php migrate_tbl_trabajadores_tat.php [local|production]\n");
}

echo "=== Migración TAT Fase 4.1 - Módulo Trabajadores ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "---\n";

$mysqli = mysqli_init();
if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}
$connected = @$mysqli->real_connect(
    $config['host'], $config['user'], $config['password'],
    $config['database'], $config['port'], null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);
if (!$connected) die("ERROR conexión: {$mysqli->connect_error}\n");
$mysqli->set_charset('utf8mb4');
echo "Conexión exitosa.\n\n";

$errors = 0;
$statements = [
    [
        'desc' => 'CREATE TABLE tbl_trabajadores',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_trabajadores` (
            `id_trabajador` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `nombre` VARCHAR(255) NOT NULL,
            `tipo_id` ENUM('CC','CE','TI','PA','RC') NOT NULL DEFAULT 'CC',
            `numero_id` VARCHAR(30) NOT NULL,
            `cargo` VARCHAR(100) NULL,
            `fecha_ingreso` DATE NULL,
            `telefono` VARCHAR(30) NULL,
            `tipo_contrato` VARCHAR(50) NULL COMMENT 'NULL permitido para autoempleados',
            `fecha_terminacion` DATE NULL,
            `manipula_alimentos` TINYINT(1) NOT NULL DEFAULT 0,
            `activo` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_trab_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_trab_cliente` (`id_cliente`),
            INDEX `idx_trab_activo` (`activo`),
            INDEX `idx_trab_numero_id` (`numero_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_trabajador_soporte',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_trabajador_soporte` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_trabajador` INT NOT NULL,
            `tipo_soporte` ENUM('datos','afiliacion_salud','manipulacion_alimentos','dotacion_epp') NOT NULL,
            `archivo` VARCHAR(255) NOT NULL,
            `fecha_expedicion` DATE NULL,
            `fecha_vencimiento` DATE NULL COMMENT 'Solo se exige para manipulacion_alimentos',
            `id_reporte` INT NULL COMMENT 'FK inversa a tbl_reporte (integración reportlist)',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_trab_sop_trabajador`
                FOREIGN KEY (`id_trabajador`) REFERENCES `tbl_trabajadores`(`id_trabajador`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_trab_sop_trab` (`id_trabajador`),
            INDEX `idx_trab_sop_tipo` (`tipo_soporte`),
            INDEX `idx_trab_sop_vence` (`fecha_vencimiento`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
];

foreach ($statements as $stmt) {
    echo "[{$stmt['desc']}]... ";
    if ($mysqli->query($stmt['sql'])) {
        echo "OK\n";
    } else {
        echo "ERROR: {$mysqli->error}\n";
        $errors++;
    }
}

/* ------------------------------------------------------------------ *
 * Sembrado de catálogos
 * ------------------------------------------------------------------ */

// report_type_table: "Trabajadores"
echo "\n[SEED report_type_table]... ";
$check = $mysqli->query("SELECT id_report_type FROM report_type_table WHERE report_type = 'Trabajadores'");
if ($check->num_rows === 0) {
    if ($mysqli->query("INSERT INTO report_type_table (report_type) VALUES ('Trabajadores')")) {
        echo "insertado (id=" . $mysqli->insert_id . ")\n";
    } else {
        echo "ERROR: {$mysqli->error}\n";
        $errors++;
    }
} else {
    echo "ya existe.\n";
}

// detail_report: 4 subcategorías
echo "\n[SEED detail_report]...\n";
$detalles = [
    'Datos del Trabajador',
    'Afiliación a Salud',
    'Certificado Manipulación de Alimentos',
    'Dotación / EPP Manipulador',
];
$stmtSeed = $mysqli->prepare("INSERT IGNORE INTO detail_report (detail_report) VALUES (?)");
foreach ($detalles as $d) {
    $stmtSeed->bind_param('s', $d);
    if ($stmtSeed->execute()) {
        echo "  " . ($mysqli->affected_rows > 0 ? '+ ' : '= ') . $d . "\n";
    } else {
        echo "  ERROR: {$mysqli->error}\n";
        $errors++;
    }
}
$stmtSeed->close();

/* ------------------------------------------------------------------ *
 * Resumen
 * ------------------------------------------------------------------ */
echo "\n=== RESULTADO ===\n";
echo "Errores: {$errors}\n";
if ($errors === 0) {
    echo "MIGRACIÓN COMPLETADA SIN ERRORES.\n";
    exit(0);
} else {
    echo "HAY ERRORES - REVISAR.\n";
    exit(1);
}

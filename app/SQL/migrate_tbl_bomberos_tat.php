<?php
/**
 * TAT Fase 4.2 — Módulo Permisos de Bomberos.
 *
 * Crea:
 *   - tbl_bomberos_solicitud (una por cliente y año)
 *   - tbl_bomberos_documento (docs obligatorios y condicionales + respuestas)
 * Semilla:
 *   - report_type_table: "Permisos Bomberos"
 *   - detail_report: 8 subcategorías por tipo de doc
 *
 * Uso:
 *   php app/SQL/migrate_tbl_bomberos_tat.php local
 *   DB_PROD_PASS=xxxxx php app/SQL/migrate_tbl_bomberos_tat.php production
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

echo "=== Migración TAT Fase 4.2 - Permisos de Bomberos ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$m = mysqli_init();
if ($config['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,false); }
if (!@$m->real_connect($config['host'],$config['user'],$config['password'],$config['database'],$config['port'],null,$config['ssl']?MYSQLI_CLIENT_SSL:0)) {
    die("ERR conexión: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');
echo "Conectado.\n\n";

$errors = 0;
$statements = [
    [
        'desc' => 'CREATE TABLE tbl_bomberos_solicitud',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_bomberos_solicitud` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `anio` YEAR NOT NULL,
            `departamento` VARCHAR(100) NOT NULL DEFAULT 'Cundinamarca',
            `municipio` VARCHAR(100) NOT NULL DEFAULT 'Soacha',
            `estado` ENUM('borrador','listo','radicado','aprobado','rechazado') NOT NULL DEFAULT 'borrador',
            `fecha_radicacion` DATE NULL,
            `numero_radicado` VARCHAR(50) NULL,
            `observaciones` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_bomb_sol_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            UNIQUE KEY `uq_bomb_cliente_anio` (`id_cliente`, `anio`),
            INDEX `idx_bomb_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_bomberos_documento',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_bomberos_documento` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_solicitud` INT NOT NULL,
            `tipo_doc` ENUM(
                'cedula_rl',
                'recibo_predial',
                'camara_comercio',
                'rut',
                'uso_suelo',
                'respuesta_gestion_riesgo',
                'concepto_bomberos',
                'otro'
            ) NOT NULL,
            `archivo` VARCHAR(255) NOT NULL,
            `id_reporte` INT NULL,
            `observaciones` VARCHAR(500) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_bomb_doc_sol`
                FOREIGN KEY (`id_solicitud`) REFERENCES `tbl_bomberos_solicitud`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_bomb_doc_sol` (`id_solicitud`),
            INDEX `idx_bomb_doc_tipo` (`tipo_doc`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
];

foreach ($statements as $stmt) {
    echo "[{$stmt['desc']}]... ";
    if ($m->query($stmt['sql'])) echo "OK\n";
    else { echo "ERROR: {$m->error}\n"; $errors++; }
}

/* -------- Sembrado report_type_table -------- */
echo "\n[SEED report_type_table 'Permisos Bomberos']... ";
$check = $m->query("SELECT id_report_type FROM report_type_table WHERE report_type = 'Permisos Bomberos'");
if ($check->num_rows === 0) {
    if ($m->query("INSERT INTO report_type_table (report_type) VALUES ('Permisos Bomberos')")) {
        echo "insertado (id=" . $m->insert_id . ")\n";
    } else { echo "ERROR: {$m->error}\n"; $errors++; }
} else echo "ya existe.\n";

/* -------- Sembrado detail_report (8 subcategorías) -------- */
echo "\n[SEED detail_report 8 tipos de documento]...\n";
$detalles = [
    'Cédula Representante Legal',
    'Recibo Predial',
    'Cámara de Comercio',
    'RUT',
    'Concepto de Uso de Suelo',
    'Respuesta Oficina Gestión del Riesgo',
    'Concepto Bomberos',
    'Documento Bomberos Adicional',
];
$stmtSeed = $m->prepare("INSERT IGNORE INTO detail_report (detail_report) VALUES (?)");
foreach ($detalles as $d) {
    $stmtSeed->bind_param('s', $d);
    if ($stmtSeed->execute()) {
        echo "  " . ($m->affected_rows > 0 ? '+ ' : '= ') . $d . "\n";
    } else { echo "  ERROR: {$m->error}\n"; $errors++; }
}
$stmtSeed->close();

echo "\n=== RESULTADO ===\n";
echo "Errores: {$errors}\n";
echo $errors === 0 ? "MIGRACIÓN COMPLETADA SIN ERRORES.\n" : "HAY ERRORES - REVISAR.\n";
exit($errors === 0 ? 0 : 1);

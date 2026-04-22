<?php
/**
 * Script CLI - Migración TAT Fase 1
 *
 * Extiende tbl_clientes con los campos requeridos por:
 *   - Acta de Inspección Sanitaria con Enfoque de Riesgo (Secretaría de Salud, SL-FR-019)
 *   - Formulario de visita técnica de Bomberos (Soacha y similares)
 *
 * Crea tbl_tipo_establecimiento (catálogo editable).
 *
 * Uso:
 *   php app/SQL/migrate_tbl_clientes_tat.php local
 *   DB_PROD_PASS=xxxxx php app/SQL/migrate_tbl_clientes_tat.php production
 *
 * Idempotente: verifica existencia antes de cada ALTER/INSERT.
 *
 * Documento relacionado: docs/migracion-tat/decisiones-alcance.md (§5, §3.2)
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos.');
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'tat_cycloid',
        'ssl'      => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'tat_cycloid',
        'ssl'      => true,
    ];
    if (empty($config['password'])) {
        die("ERROR: Debe establecer la variable de entorno DB_PROD_PASS antes de ejecutar en producción.\n"
          . "       Ej: DB_PROD_PASS='xxxxx' php app/SQL/migrate_tbl_clientes_tat.php production\n");
    }
} else {
    die("Uso: php migrate_tbl_clientes_tat.php [local|production]\n");
}

echo "=== Migración TAT Fase 1 - tbl_clientes + tbl_tipo_establecimiento ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Database: {$config['database']}\n";
echo "---\n";

$mysqli = mysqli_init();

if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database'],
    $config['port'],
    null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    die("ERROR de conexión: " . $mysqli->connect_error . "\n");
}
$mysqli->set_charset('utf8mb4');
echo "Conexión exitosa.\n\n";

/* =========================================================================
 * Helper: añade una columna solo si no existe (idempotente).
 * ========================================================================= */
function addColumnIfNotExists(mysqli $db, string $table, string $column, string $definition): array
{
    $stmt = $db->prepare("SELECT COUNT(*) AS c FROM information_schema.COLUMNS
                          WHERE TABLE_SCHEMA = DATABASE()
                          AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $exists = (int) $stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();

    if ($exists > 0) {
        return ['status' => 'skipped', 'msg' => "columna ya existe"];
    }

    $sql = "ALTER TABLE `{$table}` ADD COLUMN {$definition}";
    if ($db->query($sql)) {
        return ['status' => 'ok', 'msg' => 'columna añadida'];
    }
    return ['status' => 'error', 'msg' => $db->error];
}

/* =========================================================================
 * 1) Nuevas columnas en tbl_clientes
 * ========================================================================= */
$nuevasColumnas = [
    ['nombre_comercial',                 "`nombre_comercial` VARCHAR(255) NULL COMMENT 'Nombre comercial visible - distinto de razón social'"],
    ['numero_inscripcion_sanitaria',     "`numero_inscripcion_sanitaria` VARCHAR(50) NULL COMMENT 'Asignado por Secretaría de Salud'"],
    ['matricula_mercantil',              "`matricula_mercantil` VARCHAR(50) NULL COMMENT 'Cámara de Comercio'"],
    ['departamento',                     "`departamento` VARCHAR(100) NULL"],
    ['comuna',                           "`comuna` VARCHAR(50) NULL COMMENT 'Sector/Comuna del municipio'"],
    ['barrio',                           "`barrio` VARCHAR(150) NULL"],
    ['propietario_nombre',               "`propietario_nombre` VARCHAR(255) NULL COMMENT 'Propietario del establecimiento (puede ser persona natural distinta al rep. legal)'"],
    ['propietario_tipo_id',              "`propietario_tipo_id` ENUM('CC','CE','NIT','TI','PA','RC') NULL"],
    ['propietario_numero_id',            "`propietario_numero_id` VARCHAR(30) NULL"],
    ['rep_legal_tipo_id',                "`rep_legal_tipo_id` ENUM('CC','CE','NIT','TI','PA','RC') NULL"],
    ['numero_trabajadores',              "`numero_trabajadores` INT NULL"],
    ['autoriza_notificacion_electronica','`autoriza_notificacion_electronica` TINYINT(1) NULL DEFAULT 0'],
    ['id_tipo_establecimiento',          "`id_tipo_establecimiento` INT NULL COMMENT 'FK a tbl_tipo_establecimiento'"],
    ['aforo',                            "`aforo` INT NULL COMMENT 'Aforo del establecimiento (Bomberos)'"],
    ['area_m2',                          "`area_m2` DECIMAL(10,2) NULL COMMENT 'Área en metros cuadrados'"],
];

$totalAlter = 0; $okAlter = 0; $skipAlter = 0; $errAlter = 0;
foreach ($nuevasColumnas as [$col, $def]) {
    $totalAlter++;
    echo "[ALTER {$totalAlter}] tbl_clientes.{$col}... ";
    $r = addColumnIfNotExists($mysqli, 'tbl_clientes', $col, $def);
    echo strtoupper($r['status']) . " - " . $r['msg'] . "\n";
    if ($r['status'] === 'ok')      $okAlter++;
    if ($r['status'] === 'skipped') $skipAlter++;
    if ($r['status'] === 'error')   $errAlter++;
}

/* =========================================================================
 * 2) Crear tbl_tipo_establecimiento (catálogo)
 * ========================================================================= */
echo "\n[CATALOGO] Creando tbl_tipo_establecimiento si no existe... ";
$createCatalogo = "CREATE TABLE IF NOT EXISTS `tbl_tipo_establecimiento` (
    `id_tipo_establecimiento` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `codigo` VARCHAR(30) NULL COMMENT 'Slug / código corto para uso programático',
    `aplica_bomberos_docs_extra` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = requiere doc. adicional de Gestión del Riesgo (Soacha)',
    `aplica_manipulacion_alimentos` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = exige certificación del manipulador',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `orden` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_tipo_establecimiento_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($mysqli->query($createCatalogo)) {
    echo "OK\n";
} else {
    echo "ERROR: " . $mysqli->error . "\n";
    $errAlter++;
}

/* =========================================================================
 * 3) Sembrar catálogo inicial (idempotente: INSERT IGNORE)
 * ========================================================================= */
echo "\n[SEED] Sembrando catálogo de tipos de establecimiento...\n";
$catalogo = [
    // [nombre, codigo, aplica_bomberos_docs_extra, aplica_manipulacion_alimentos, orden]
    ['Tienda de barrio',              'TIENDA_BARRIO',   0, 0, 10],
    ['Panadería / Pastelería',        'PANADERIA',       0, 1, 20],
    ['Droguería / Farmacia',          'DROGUERIA',       0, 0, 30],
    ['Ferretería',                    'FERRETERIA',      0, 0, 40],
    ['Miscelánea / Papelería',        'MISCELANEA',      0, 0, 50],
    ['Peluquería / Estética',         'PELUQUERIA',      0, 0, 60],
    ['Lavandería',                    'LAVANDERIA',      0, 0, 70],
    ['Restaurante / Asadero',         'RESTAURANTE',     1, 1, 80],
    ['Bar / Cafetería',               'BAR_CAFETERIA',   1, 1, 90],
    ['Carnicería / Fruver',           'CARNICERIA',      0, 1, 100],
    ['Heladería',                     'HELADERIA',       0, 1, 110],
    ['Supermercado / Autoservicio',   'SUPERMERCADO',    0, 1, 120],
    ['Centro comercial',              'CENTRO_COMERCIAL',1, 0, 130],
    ['Estación de servicio',          'ESTACION_SERV',   1, 0, 140],
    ['Hotel / Motel',                 'HOTEL',           1, 0, 150],
    ['Discoteca',                     'DISCOTECA',       1, 0, 160],
    ['Institución educativa',         'INST_EDUCATIVA',  1, 0, 170],
    ['Hospital / IPS',                'HOSPITAL_IPS',    1, 0, 180],
    ['Industria / Empresa',           'INDUSTRIA',       1, 0, 190],
    ['Microempresa',                  'MICROEMPRESA',    1, 0, 200],
    ['Oficina / Consultorio',         'OFICINA',         0, 0, 210],
    ['Taller mecánico',               'TALLER',          0, 0, 220],
    ['Otro',                          'OTRO',            0, 0, 999],
];

$seedStmt = $mysqli->prepare("INSERT IGNORE INTO tbl_tipo_establecimiento
    (nombre, codigo, aplica_bomberos_docs_extra, aplica_manipulacion_alimentos, orden)
    VALUES (?, ?, ?, ?, ?)");

$okSeed = 0; $dupSeed = 0;
foreach ($catalogo as [$nombre, $codigo, $bombExtra, $manipAlim, $orden]) {
    $seedStmt->bind_param('ssiii', $nombre, $codigo, $bombExtra, $manipAlim, $orden);
    if ($seedStmt->execute()) {
        if ($mysqli->affected_rows > 0) {
            echo "  + {$nombre}\n";
            $okSeed++;
        } else {
            $dupSeed++;
        }
    } else {
        echo "  ERROR en {$nombre}: " . $mysqli->error . "\n";
        $errAlter++;
    }
}
$seedStmt->close();

echo "  Insertados: {$okSeed}, Ya existentes (omitidos): {$dupSeed}\n";

/* =========================================================================
 * 4) FK id_tipo_establecimiento -> tbl_tipo_establecimiento
 *    (sólo si no existe la restricción)
 * ========================================================================= */
echo "\n[FK] Verificando FK tbl_clientes.id_tipo_establecimiento -> tbl_tipo_establecimiento... ";
$fkStmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_clientes'
      AND CONSTRAINT_NAME = 'fk_clientes_tipo_establecimiento'");
$fkStmt->execute();
$fkExists = (int) $fkStmt->get_result()->fetch_assoc()['c'];
$fkStmt->close();

if ($fkExists > 0) {
    echo "ya existe, omitido.\n";
} else {
    $addFk = "ALTER TABLE tbl_clientes
             ADD CONSTRAINT fk_clientes_tipo_establecimiento
             FOREIGN KEY (id_tipo_establecimiento)
             REFERENCES tbl_tipo_establecimiento(id_tipo_establecimiento)
             ON DELETE SET NULL ON UPDATE CASCADE";
    if ($mysqli->query($addFk)) {
        echo "OK - FK creada.\n";
    } else {
        echo "ERROR: " . $mysqli->error . "\n";
        $errAlter++;
    }
}

/* =========================================================================
 * RESUMEN
 * ========================================================================= */
echo "\n=== RESULTADO ===\n";
echo "Columnas nuevas intentadas: {$totalAlter}\n";
echo "  -> OK añadidas:    {$okAlter}\n";
echo "  -> Ya existían:    {$skipAlter}\n";
echo "  -> Errores:        {$errAlter}\n";
echo "Tipos sembrados en catálogo: {$okSeed} (duplicados omitidos: {$dupSeed})\n";

if ($errAlter === 0) {
    echo "\nMIGRACIÓN COMPLETADA SIN ERRORES.\n";
    exit(0);
} else {
    echo "\nHAY ERRORES - REVISAR ANTES DE CONTINUAR.\n";
    exit(1);
}

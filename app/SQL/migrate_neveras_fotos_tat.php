<?php
/**
 * TAT Fase 5.1 — Ajustes de fotos y rangos en Neveras.
 *
 * tbl_nevera:
 *   + foto_equipo VARCHAR(255) NULL
 *
 * tbl_inspeccion_nevera:
 *   + foto_temperatura VARCHAR(255) NULL
 *   + foto_humedad VARCHAR(255) NULL
 *   - foto_evidencia (drop si existe, módulo sin datos reales)
 *
 * Idempotente.
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

echo "=== TAT Fase 5.1 - Ajustes fotos neveras ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$m = mysqli_init();
if ($config['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,false); }
if (!@$m->real_connect($config['host'],$config['user'],$config['password'],$config['database'],$config['port'],null,$config['ssl']?MYSQLI_CLIENT_SSL:0)) {
    die("ERR: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

function colExists(mysqli $db, string $table, string $col): bool
{
    $stmt = $db->prepare("SELECT COUNT(*) AS c FROM information_schema.COLUMNS
                          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->bind_param('ss', $table, $col);
    $stmt->execute();
    $exists = (int)$stmt->get_result()->fetch_assoc()['c'] > 0;
    $stmt->close();
    return $exists;
}

$errors = 0;

// tbl_nevera + foto_equipo
echo "[tbl_nevera.foto_equipo]... ";
if (colExists($m, 'tbl_nevera', 'foto_equipo')) {
    echo "ya existe.\n";
} else {
    if ($m->query("ALTER TABLE tbl_nevera ADD COLUMN foto_equipo VARCHAR(255) NULL AFTER ubicacion")) {
        echo "añadida.\n";
    } else { echo "ERR: {$m->error}\n"; $errors++; }
}

// tbl_inspeccion_nevera + foto_temperatura
echo "[tbl_inspeccion_nevera.foto_temperatura]... ";
if (colExists($m, 'tbl_inspeccion_nevera', 'foto_temperatura')) {
    echo "ya existe.\n";
} else {
    if ($m->query("ALTER TABLE tbl_inspeccion_nevera ADD COLUMN foto_temperatura VARCHAR(255) NULL AFTER humedad_relativa")) {
        echo "añadida.\n";
    } else { echo "ERR: {$m->error}\n"; $errors++; }
}

// tbl_inspeccion_nevera + foto_humedad
echo "[tbl_inspeccion_nevera.foto_humedad]... ";
if (colExists($m, 'tbl_inspeccion_nevera', 'foto_humedad')) {
    echo "ya existe.\n";
} else {
    if ($m->query("ALTER TABLE tbl_inspeccion_nevera ADD COLUMN foto_humedad VARCHAR(255) NULL AFTER foto_temperatura")) {
        echo "añadida.\n";
    } else { echo "ERR: {$m->error}\n"; $errors++; }
}

// tbl_inspeccion_nevera drop foto_evidencia (solo si existe)
echo "[tbl_inspeccion_nevera DROP foto_evidencia]... ";
if (!colExists($m, 'tbl_inspeccion_nevera', 'foto_evidencia')) {
    echo "no existe, skip.\n";
} else {
    // Verificar que no haya datos (solo drop si 0 rows usando la columna)
    $used = (int)$m->query("SELECT COUNT(*) c FROM tbl_inspeccion_nevera WHERE foto_evidencia IS NOT NULL AND foto_evidencia <> ''")
        ->fetch_assoc()['c'];
    if ($used > 0) {
        echo "hay {$used} registros con datos, NO se elimina.\n";
    } else {
        if ($m->query("ALTER TABLE tbl_inspeccion_nevera DROP COLUMN foto_evidencia")) {
            echo "columna eliminada.\n";
        } else { echo "ERR: {$m->error}\n"; $errors++; }
    }
}

echo "\n=== RESULTADO ===\n";
echo "Errores: {$errors}\n";
echo $errors === 0 ? "OK.\n" : "HAY ERRORES.\n";
exit($errors === 0 ? 0 : 1);

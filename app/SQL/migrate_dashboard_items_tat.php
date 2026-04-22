<?php
/**
 * Script CLI - TAT Fase 2
 *
 * Desactiva (activo=0) los items del menú admin que apuntan a dashboards
 * de Plan de Trabajo / Estándares Mínimos, no aplicables a locales comerciales.
 *
 * Uso:
 *   php app/SQL/migrate_dashboard_items_tat.php local
 *   DB_PROD_PASS=xxxxx php app/SQL/migrate_dashboard_items_tat.php production
 *
 * Reversible: UPDATE dashboard_items SET activo=1 WHERE id IN (57,59,68,69);
 *
 * Relacionado: docs/migracion-tat/decisiones-alcance.md §3.3
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
        die("ERROR: Debe establecer la variable de entorno DB_PROD_PASS antes de ejecutar en producción.\n");
    }
} else {
    die("Uso: php migrate_dashboard_items_tat.php [local|production]\n");
}

echo "=== Migración TAT Fase 2 - Ocultar dashboards Plan Trabajo/Estándares ===\n";
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

// Identificar items por URL (más robusto que por id, por si los ids difieren entre LOCAL/PROD)
$urls = [
    'consultant/dashboard-estandares',
    'consultant/dashboard-plan-trabajo',
    'consultant/evolucion-estandares',
    'consultant/evolucion-plan-trabajo',
];

echo "Items que se desactivarán (activo=0):\n";
$stmt = $mysqli->prepare("SELECT id, detalle, accion_url, activo FROM dashboard_items WHERE accion_url = ?");
foreach ($urls as $url) {
    $stmt->bind_param('s', $url);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        echo "  id={$row['id']} | {$row['detalle']} | {$row['accion_url']} | activo={$row['activo']}\n";
    }
}
$stmt->close();

// Desactivar
$upd = $mysqli->prepare("UPDATE dashboard_items SET activo = 0 WHERE accion_url = ?");
$totalUpdated = 0;
foreach ($urls as $url) {
    $upd->bind_param('s', $url);
    $upd->execute();
    $totalUpdated += $mysqli->affected_rows;
}
$upd->close();

echo "\n=== RESULTADO ===\n";
echo "Filas actualizadas: {$totalUpdated}\n";

if ($totalUpdated >= 0) {
    echo "MIGRACIÓN COMPLETADA.\n";
    exit(0);
} else {
    echo "HAY ERRORES - REVISAR.\n";
    exit(1);
}

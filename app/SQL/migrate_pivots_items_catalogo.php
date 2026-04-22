<?php
/**
 * TAT — Refactor many-to-many de catálogos de items.
 *
 * Crea 4 tablas pivot (tbl_<catalogo>_item_cliente) y las puebla a partir de
 * los datos actuales de tbl_<catalogo>_item, que usaban una columna id_cliente
 * (NULL = global, X = específico) denormalizada.
 *
 * NO borra la columna id_cliente — queda para un PR posterior una vez
 * verificado que ningún código la usa.
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

echo "Entorno: " . strtoupper($env) . "\n";

$m = mysqli_init();
if ($config['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,false); }
if (!@$m->real_connect($config['host'],$config['user'],$config['password'],$config['database'],$config['port'],null,$config['ssl']?MYSQLI_CLIENT_SSL:0)) {
    die("ERR conexión: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

$catalogos = ['equipos', 'limpieza', 'contaminacion', 'almacenamiento'];

foreach ($catalogos as $cat) {
    $tItem  = "tbl_{$cat}_item";
    $tPivot = "tbl_{$cat}_item_cliente";

    echo "\n=== Catálogo: {$cat} ===\n";

    // 1. Crear pivot
    $sql = "CREATE TABLE IF NOT EXISTS {$tPivot} (
        id_item     INT NOT NULL,
        id_cliente  INT NOT NULL,
        asignado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_item, id_cliente),
        INDEX idx_{$cat}_cliente (id_cliente),
        INDEX idx_{$cat}_item (id_item)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if ($m->query($sql)) echo "  + {$tPivot} creada/verificada.\n";
    else { echo "  ERR crear {$tPivot}: " . $m->error . "\n"; continue; }

    // 2. Verificar si ya tiene datos (evitar re-poblar)
    $existe = $m->query("SELECT COUNT(*) c FROM {$tPivot}")->fetch_assoc()['c'];
    if ($existe > 0) {
        echo "  = {$tPivot} ya tiene {$existe} filas; se salta poblado.\n";
        continue;
    }

    // 3. Poblar desde items globales (id_cliente IS NULL) → asignar a TODOS los clientes
    $sqlGlobal = "INSERT IGNORE INTO {$tPivot} (id_item, id_cliente)
                  SELECT i.id_item, c.id_cliente
                  FROM {$tItem} i
                  CROSS JOIN tbl_clientes c
                  WHERE i.id_cliente IS NULL AND i.activo = 1";
    if ($m->query($sqlGlobal)) {
        $filas = $m->affected_rows;
        echo "  + {$filas} filas insertadas desde items globales.\n";
    } else {
        echo "  ERR poblar globales: " . $m->error . "\n";
    }

    // 4. Poblar desde items específicos de cliente
    $sqlEspec = "INSERT IGNORE INTO {$tPivot} (id_item, id_cliente)
                 SELECT id_item, id_cliente
                 FROM {$tItem}
                 WHERE id_cliente IS NOT NULL AND activo = 1";
    if ($m->query($sqlEspec)) {
        $filas = $m->affected_rows;
        echo "  + {$filas} filas insertadas desde items específicos.\n";
    } else {
        echo "  ERR poblar específicos: " . $m->error . "\n";
    }

    // 5. Mostrar resumen
    $total = $m->query("SELECT COUNT(*) c FROM {$tPivot}")->fetch_assoc()['c'];
    $clientes = $m->query("SELECT COUNT(DISTINCT id_cliente) c FROM {$tPivot}")->fetch_assoc()['c'];
    $items    = $m->query("SELECT COUNT(DISTINCT id_item) c FROM {$tPivot}")->fetch_assoc()['c'];
    echo "  = Resumen: {$total} asignaciones ({$items} items × {$clientes} clientes).\n";
}

echo "\nOK. Para ver qué tiene cada pivot:\n";
echo "  SELECT * FROM tbl_equipos_item_cliente;\n";
echo "  SELECT * FROM tbl_limpieza_item_cliente;\n";
echo "  SELECT * FROM tbl_contaminacion_item_cliente;\n";
echo "  SELECT * FROM tbl_almacenamiento_item_cliente;\n";

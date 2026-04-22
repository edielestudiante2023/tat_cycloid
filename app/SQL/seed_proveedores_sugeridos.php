<?php
/**
 * TAT Fase 5.3b — Semilla de 12 proveedores sugeridos para cada cliente existente.
 * Idempotente: salta duplicados (unique por nombre + id_cliente a nivel lógico).
 *
 * Uso:
 *   php app/SQL/seed_proveedores_sugeridos.php local
 *   DB_PROD_PASS=xxxxx php app/SQL/seed_proveedores_sugeridos.php production
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

echo "=== Seed proveedores sugeridos ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$m = mysqli_init();
if ($config['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,false); }
if (!@$m->real_connect($config['host'],$config['user'],$config['password'],$config['database'],$config['port'],null,$config['ssl']?MYSQLI_CLIENT_SSL:0)) {
    die("ERR: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

$proveedores = [
    ['Panadería El Molino',            'panaderia'],
    ['Lácteos Alpina (Distribuidor)',  'lacteos'],
    ['Lácteos Alquería (Distribuidor)','lacteos'],
    ['Carnes Frescas La Sabana',       'carnicos'],
    ['Pollos Bucanero (Distribuidor)', 'carnicos'],
    ['Fruver El Campo',                'frutas_verduras'],
    ['Coca-Cola FEMSA',                'bebidas'],
    ['Postobón (Distribuidor)',        'bebidas'],
    ['Harinas y Abarrotes SAS',        'empacados'],
    ['Productos Ramo',                 'empacados'],
    ['Huevos Oro',                     'otros'],
    ['Heladería San Jerónimo',         'congelados'],
];

// Obtener clientes existentes
$clients = $m->query('SELECT id_cliente, nombre_cliente FROM tbl_clientes');
$totalClientes = 0;
$totalInsertados = 0;
$totalDuplicados = 0;

while ($c = $clients->fetch_assoc()) {
    $totalClientes++;
    $cid = (int)$c['id_cliente'];
    echo "\nCliente id={$cid} · {$c['nombre_cliente']}:\n";

    foreach ($proveedores as [$nombre, $cat]) {
        // Verificar si ya existe para este cliente
        $nombreEsc = $m->real_escape_string($nombre);
        $chk = $m->query("SELECT id_proveedor FROM tbl_proveedor WHERE id_cliente={$cid} AND nombre='{$nombreEsc}' LIMIT 1");
        if ($chk && $chk->num_rows > 0) {
            $totalDuplicados++;
            continue;
        }
        $stmt = $m->prepare("INSERT INTO tbl_proveedor (id_cliente, nombre, categoria_principal, activo) VALUES (?, ?, ?, 1)");
        $stmt->bind_param('iss', $cid, $nombre, $cat);
        if ($stmt->execute()) {
            echo "  + {$nombre}\n";
            $totalInsertados++;
        } else {
            echo "  ERR {$nombre}: {$m->error}\n";
        }
        $stmt->close();
    }
}

echo "\n=== RESULTADO ===\n";
echo "Clientes procesados: {$totalClientes}\n";
echo "Proveedores insertados: {$totalInsertados}\n";
echo "Duplicados omitidos: {$totalDuplicados}\n";
echo "OK.\n";

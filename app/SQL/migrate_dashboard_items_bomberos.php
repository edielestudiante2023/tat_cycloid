<?php
/**
 * TAT Fase 4.2 — Inserta item "Permisos Bomberos" en dashboard_items del consultor.
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
    die("ERR: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

$check = $m->query("SELECT id FROM dashboard_items WHERE accion_url = 'bomberos/seleccionar-cliente'");
if ($check && $check->num_rows > 0) {
    $row = $check->fetch_assoc();
    echo "Item ya existe (id={$row['id']}), actualizando...\n";
    $m->query("UPDATE dashboard_items SET activo=1, target_blank=1 WHERE id={$row['id']}");
} else {
    $sql = "INSERT INTO dashboard_items
        (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo, creado_en)
        VALUES
        ('consultor', 'modulo', 'Permisos Bomberos',
         'Gestión de expedientes anuales de permiso de bomberos por establecimiento',
         'bomberos/seleccionar-cliente', 16, 'Gestión Clientes',
         'fa-fire-extinguisher', 'linear-gradient(135deg, #d62828 0%, #f77f00 100%)',
         1, 1, NOW())";
    if ($m->query($sql)) {
        echo "Item insertado (id=" . $m->insert_id . ").\n";
    } else die("ERR: " . $m->error . "\n");
}
echo "OK.\n";

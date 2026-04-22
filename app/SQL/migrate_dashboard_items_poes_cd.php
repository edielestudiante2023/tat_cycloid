<?php
/**
 * TAT Fase 5.3cd — Items dashboard_items para POES Contaminación + Almacenamiento.
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

$items = [
    [
        'url' => 'contaminacion/seleccionar-cliente',
        'detalle' => 'POES Contaminación Cruzada',
        'desc' => 'POES 4.2 — Checklist de prevención de contaminación cruzada',
        'orden' => 21,
        'categoria' => 'Inspecciones y Auditoría',
        'icono' => 'fa-exchange-alt',
        'gradiente' => 'linear-gradient(135deg, #dc3545 0%, #e74c3c 100%)',
    ],
    [
        'url' => 'almacenamiento/seleccionar-cliente',
        'detalle' => 'POES Almacenamiento',
        'desc' => 'POES 4.4 — Checklist de condiciones de almacenamiento',
        'orden' => 22,
        'categoria' => 'Inspecciones y Auditoría',
        'icono' => 'fa-boxes-stacked',
        'gradiente' => 'linear-gradient(135deg, #7c3aed 0%, #a855f7 100%)',
    ],
    [
        'url' => 'admin/contaminacion-items',
        'detalle' => 'Catálogo Items Contaminación',
        'desc' => 'Items del checklist POES 4.2',
        'orden' => 92,
        'categoria' => 'Configuración',
        'icono' => 'fa-exchange-alt',
        'gradiente' => 'linear-gradient(135deg, #dc3545 0%, #e74c3c 100%)',
    ],
    [
        'url' => 'admin/almacenamiento-items',
        'detalle' => 'Catálogo Items Almacenamiento',
        'desc' => 'Items del checklist POES 4.4',
        'orden' => 93,
        'categoria' => 'Configuración',
        'icono' => 'fa-boxes-stacked',
        'gradiente' => 'linear-gradient(135deg, #7c3aed 0%, #a855f7 100%)',
    ],
];

foreach ($items as $i) {
    $check = $m->query("SELECT id FROM dashboard_items WHERE accion_url = '" . $m->real_escape_string($i['url']) . "'");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $m->query("UPDATE dashboard_items SET activo=1, target_blank=1 WHERE id={$row['id']}");
        echo "  = {$i['detalle']} ya existe.\n";
    } else {
        $sql = sprintf(
            "INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo, creado_en)
             VALUES ('consultor','modulo','%s','%s','%s',%d,'%s','%s','%s',1,1,NOW())",
            $m->real_escape_string($i['detalle']),
            $m->real_escape_string($i['desc']),
            $m->real_escape_string($i['url']),
            $i['orden'],
            $m->real_escape_string($i['categoria']),
            $m->real_escape_string($i['icono']),
            $m->real_escape_string($i['gradiente'])
        );
        if ($m->query($sql)) echo "  + {$i['detalle']} (id=" . $m->insert_id . ")\n";
        else echo "  ERR {$i['detalle']}: " . $m->error . "\n";
    }
}
echo "OK.\n";

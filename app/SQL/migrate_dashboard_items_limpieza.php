<?php
/**
 * TAT Fase 5.2 — 2 items nuevos en dashboard_items:
 *   1) Selector cliente para Inspección de Aseo
 *   2) Admin del catálogo de items (Configuración)
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
        'url' => 'limpieza-local/seleccionar-cliente',
        'detalle' => 'Inspección de Aseo',
        'desc' => 'Checklist dinámico de limpieza del local por cliente',
        'orden' => 18,
        'categoria' => 'Inspecciones y Auditoría',
        'icono' => 'fa-broom',
        'gradiente' => 'linear-gradient(135deg, #198754 0%, #20c997 100%)',
    ],
    [
        'url' => 'admin/limpieza-items',
        'detalle' => 'Catálogo Items de Aseo',
        'desc' => 'Administra los items (preguntas) del checklist de limpieza',
        'orden' => 90,
        'categoria' => 'Configuración',
        'icono' => 'fa-list-check',
        'gradiente' => 'linear-gradient(135deg, #6c757d 0%, #495057 100%)',
    ],
];

foreach ($items as $i) {
    $check = $m->query("SELECT id FROM dashboard_items WHERE accion_url = '" . $m->real_escape_string($i['url']) . "'");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $m->query("UPDATE dashboard_items SET activo=1, target_blank=1 WHERE id={$row['id']}");
        echo "  = {$i['detalle']} ya existe (id={$row['id']}), actualizado.\n";
    } else {
        $sql = sprintf(
            "INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo, creado_en)
             VALUES ('consultor', 'modulo', '%s', '%s', '%s', %d, '%s', '%s', '%s', 1, 1, NOW())",
            $m->real_escape_string($i['detalle']),
            $m->real_escape_string($i['desc']),
            $m->real_escape_string($i['url']),
            $i['orden'],
            $m->real_escape_string($i['categoria']),
            $m->real_escape_string($i['icono']),
            $m->real_escape_string($i['gradiente'])
        );
        if ($m->query($sql)) echo "  + {$i['detalle']} (id=" . $m->insert_id . ")\n";
        else { echo "  ERR {$i['detalle']}: " . $m->error . "\n"; }
    }
}
echo "OK.\n";

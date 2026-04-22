<?php
/**
 * TAT Fase 3-bis — Tabla tbl_solicitud_anulacion (eliminaciones con aprobación del consultor).
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

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS tbl_solicitud_anulacion (
  id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
  token VARCHAR(64) NOT NULL UNIQUE,
  tipo_registro VARCHAR(40) NOT NULL,
  id_registro INT NOT NULL,
  id_registro_secundario INT NULL,
  id_cliente INT NOT NULL,
  id_consultor INT NOT NULL,
  justificacion TEXT NOT NULL,
  estado ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  nota_respuesta TEXT NULL,
  fecha_solicitud DATETIME NOT NULL,
  fecha_respuesta DATETIME NULL,
  INDEX idx_token (token),
  INDEX idx_estado (estado),
  INDEX idx_cliente (id_cliente),
  INDEX idx_consultor (id_consultor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
SQL;

if ($m->query($sql)) echo "  + tbl_solicitud_anulacion creada/verificada.\n";
else echo "  ERR: " . $m->error . "\n";

echo "OK.\n";

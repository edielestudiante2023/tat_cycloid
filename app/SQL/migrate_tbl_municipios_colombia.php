<?php
/**
 * TAT Fase 4.2 — Catálogo de Departamentos y Municipios de Colombia.
 *
 * Lee app/SQL/colombia_municipios.json y siembra tbl_municipios_colombia.
 * Fuente: https://github.com/marcovega/colombia-json (32 depto, 1104 municipios).
 *
 * Uso:
 *   php app/SQL/migrate_tbl_municipios_colombia.php local
 *   DB_PROD_PASS=xxxxx php app/SQL/migrate_tbl_municipios_colombia.php production
 *
 * Idempotente: INSERT IGNORE + unique(departamento, municipio).
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

echo "=== Seed tbl_municipios_colombia ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$m = mysqli_init();
if ($config['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,false); }
if (!@$m->real_connect($config['host'],$config['user'],$config['password'],$config['database'],$config['port'],null,$config['ssl']?MYSQLI_CLIENT_SSL:0)) {
    die("ERR conexión: " . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');
echo "Conectado.\n";

// Crear tabla
$sql = "CREATE TABLE IF NOT EXISTS tbl_municipios_colombia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento VARCHAR(100) NOT NULL,
    municipio VARCHAR(100) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_dept_muni (departamento, municipio),
    INDEX idx_departamento (departamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if (!$m->query($sql)) die("ERR create: " . $m->error . "\n");
echo "[CREATE TABLE] OK\n";

// Leer JSON
$jsonPath = __DIR__ . '/colombia_municipios.json';
if (!file_exists($jsonPath)) die("No existe colombia_municipios.json junto al script\n");
$data = json_decode(file_get_contents($jsonPath), true);
if (!$data) die("JSON inválido\n");

$stmt = $m->prepare("INSERT IGNORE INTO tbl_municipios_colombia (departamento, municipio) VALUES (?, ?)");
$ok = 0; $dup = 0;
foreach ($data as $dept) {
    $departamento = $dept['departamento'] ?? null;
    if (!$departamento) continue;
    foreach (($dept['ciudades'] ?? []) as $municipio) {
        $stmt->bind_param('ss', $departamento, $municipio);
        if ($stmt->execute()) {
            if ($m->affected_rows > 0) $ok++;
            else $dup++;
        }
    }
}
$stmt->close();

echo "Insertados: {$ok}\n";
echo "Ya existían (ignorados): {$dup}\n";
echo "MIGRACIÓN COMPLETADA.\n";

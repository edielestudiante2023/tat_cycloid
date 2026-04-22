<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * spark rutinas:migrar                     → conexión default (.env local)
 * spark rutinas:migrar --env=production    → conexión database.production.* del .env
 *
 * Crea las 3 tablas del módulo Rutinas si no existen. Idempotente:
 * revisa INFORMATION_SCHEMA antes de crear.
 */
class RutinasMigrar extends BaseCommand
{
    protected $group       = 'Rutinas';
    protected $name        = 'rutinas:migrar';
    protected $description = 'Crea/actualiza las tablas del módulo Rutinas (actividades, asignaciones, registros).';
    protected $usage       = 'rutinas:migrar [local|production]';
    protected $arguments   = [
        'target' => 'BD a migrar: local (default) o production.',
    ];
    protected $options     = [];

    public function run(array $params)
    {
        $env = strtolower($params[0] ?? 'local');

        if (!in_array($env, ['local', 'production'], true)) {
            CLI::error("Entorno inválido '{$env}'. Usar 'local' o 'production'.");
            return;
        }

        CLI::write("=== MIGRACIÓN RUTINAS [{$env}] ===", 'yellow');

        $db = $this->getConnection($env);
        if ($db === null) {
            CLI::error('No se pudo conectar a la base de datos.');
            return;
        }

        try {
            $db->query('SELECT 1');
            CLI::write("Conectado a {$db->getDatabase()} @ " . ($db->hostname ?? 'default'), 'green');
        } catch (\Throwable $e) {
            CLI::error('Fallo de conexión: ' . $e->getMessage());
            return;
        }

        $database = $db->getDatabase();

        // Pre-check: tbl_usuarios debe existir
        $u = $db->query(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'tbl_usuarios'",
            [$database]
        )->getRow();

        if (!$u || (int)$u->c === 0) {
            CLI::error("La tabla 'tbl_usuarios' no existe en {$database}. Aborto.");
            return;
        }

        // 1) rutinas_actividades
        $this->createIfMissing($db, $database, 'rutinas_actividades', "
            CREATE TABLE rutinas_actividades (
                id_actividad INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(255) NOT NULL,
                descripcion TEXT NULL,
                frecuencia ENUM('L-V','diaria') DEFAULT 'L-V',
                peso DECIMAL(5,2) DEFAULT 1.00,
                activa TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 2) rutinas_asignaciones → FK tbl_usuarios(id_usuario)
        $this->createIfMissing($db, $database, 'rutinas_asignaciones', "
            CREATE TABLE rutinas_asignaciones (
                id_asignacion INT AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT NOT NULL,
                id_actividad INT NOT NULL,
                activa TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_usuario_actividad (id_usuario, id_actividad),
                KEY fk_ra_actividad (id_actividad),
                CONSTRAINT fk_ra_usuario   FOREIGN KEY (id_usuario)   REFERENCES tbl_usuarios(id_usuario)            ON DELETE CASCADE,
                CONSTRAINT fk_ra_actividad FOREIGN KEY (id_actividad) REFERENCES rutinas_actividades(id_actividad)   ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 3) rutinas_registros
        $this->createIfMissing($db, $database, 'rutinas_registros', "
            CREATE TABLE rutinas_registros (
                id_registro INT AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT NOT NULL,
                id_actividad INT NOT NULL,
                fecha DATE NOT NULL,
                completada TINYINT(1) DEFAULT 0,
                hora_completado DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_usuario_actividad_fecha (id_usuario, id_actividad, fecha),
                KEY idx_fecha (fecha),
                KEY fk_rr_actividad (id_actividad),
                CONSTRAINT fk_rr_usuario   FOREIGN KEY (id_usuario)   REFERENCES tbl_usuarios(id_usuario)            ON DELETE CASCADE,
                CONSTRAINT fk_rr_actividad FOREIGN KEY (id_actividad) REFERENCES rutinas_actividades(id_actividad)   ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        CLI::write('', 'white');
        CLI::write("=== MIGRACIÓN COMPLETA [{$env}] ===", 'green');
    }

    private function createIfMissing($db, string $database, string $table, string $sql): void
    {
        $row = $db->query(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
            [$database, $table]
        )->getRow();

        if ($row && (int)$row->c > 0) {
            CLI::write("  · {$table} ya existe. Saltado.", 'white');
            return;
        }

        try {
            $db->query($sql);
            CLI::write("  ✔ {$table} creada.", 'green');
        } catch (\Throwable $e) {
            CLI::error("  ✖ Error creando {$table}: " . $e->getMessage());
        }
    }

    private function getConnection(string $env)
    {
        if ($env === 'local') {
            return Database::connect();
        }

        // production: construir DSN a partir del .env database.production.*
        $cfg = [
            'hostname' => env('database.production.hostname'),
            'database' => env('database.production.database'),
            'username' => env('database.production.username'),
            'password' => env('database.production.password'),
            'port'     => (int) env('database.production.port', 3306),
            'DBDriver' => env('database.production.DBDriver', 'MySQLi'),
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_general_ci',
            'swapPre'  => '',
            'encrypt'  => [
                'ssl_verify' => false,
            ],
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
        ];

        if (empty($cfg['hostname']) || empty($cfg['database']) || empty($cfg['username'])) {
            CLI::error('Faltan credenciales en .env (database.production.*).');
            return null;
        }

        return Database::connect($cfg);
    }
}

<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * spark rutinas:dashboard-items [local|production]
 *
 * Inserta (idempotente) 3 items en tabla `dashboard_items` que apuntan a
 * /rutinas/calendario, /rutinas/actividades y /rutinas/asignaciones.
 * Categoría "Operación Diaria". No duplica si ya existen por accion_url.
 */
class RutinasDashboardItems extends BaseCommand
{
    protected $group       = 'Rutinas';
    protected $name        = 'rutinas:dashboard-items';
    protected $description = 'Inserta los 3 cards de Rutinas en dashboard_items (para dashboard admin/consultor).';
    protected $usage       = 'rutinas:dashboard-items [local|production]';
    protected $arguments   = [
        'target' => 'BD destino: local (default) o production.',
    ];

    public function run(array $params)
    {
        $env = strtolower($params[0] ?? 'local');
        if (!in_array($env, ['local', 'production'], true)) {
            CLI::error("Entorno inválido '{$env}'. Usar 'local' o 'production'.");
            return;
        }

        CLI::write("=== DASHBOARD ITEMS RUTINAS [{$env}] ===", 'yellow');

        $db = $this->getConnection($env);
        if ($db === null) {
            CLI::error('No se pudo conectar.');
            return;
        }

        try {
            $db->query('SELECT 1');
            CLI::write("Conectado a {$db->getDatabase()}", 'green');
        } catch (\Throwable $e) {
            CLI::error('Fallo conexión: ' . $e->getMessage());
            return;
        }

        // Pre-check tabla
        $exist = $db->query(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'dashboard_items'",
            [$db->getDatabase()]
        )->getRow();
        if (!$exist || (int)$exist->c === 0) {
            CLI::error('Tabla dashboard_items no existe en ' . $db->getDatabase());
            return;
        }

        $items = [
            [
                'rol'             => 'admin',
                'tipo_proceso'    => '',
                'detalle'         => 'Rutinas — Calendario',
                'descripcion'     => 'Matriz actividades x días hábiles + puntaje diario/semanal/mensual',
                'accion_url'      => 'rutinas/calendario',
                'orden'           => 60,
                'categoria'       => 'Operación Diaria',
                'icono'           => 'fas fa-calendar-check',
                'color_gradiente' => '#1c2437,#bd9751',
                'target_blank'    => 0,
                'activo'          => 1,
            ],
            [
                'rol'             => 'admin',
                'tipo_proceso'    => '',
                'detalle'         => 'Rutinas — Actividades',
                'descripcion'     => 'Catálogo maestro de actividades de rutinas (CRUD)',
                'accion_url'      => 'rutinas/actividades',
                'orden'           => 61,
                'categoria'       => 'Operación Diaria',
                'icono'           => 'fas fa-list-check',
                'color_gradiente' => '#1c2437,#2d3a5e',
                'target_blank'    => 0,
                'activo'          => 1,
            ],
            [
                'rol'             => 'admin',
                'tipo_proceso'    => '',
                'detalle'         => 'Rutinas — Asignaciones',
                'descripcion'     => 'Asignar actividades a usuarios (disparador del checklist diario)',
                'accion_url'      => 'rutinas/asignaciones',
                'orden'           => 62,
                'categoria'       => 'Operación Diaria',
                'icono'           => 'fas fa-people-arrows',
                'color_gradiente' => '#2d3a5e,#bd9751',
                'target_blank'    => 0,
                'activo'          => 1,
            ],
        ];

        $insertados = 0;
        $existentes = 0;
        foreach ($items as $it) {
            $dup = $db->table('dashboard_items')
                ->where('accion_url', $it['accion_url'])
                ->countAllResults();

            if ($dup > 0) {
                CLI::write("  · {$it['accion_url']} ya existe. Saltado.", 'white');
                $existentes++;
                continue;
            }

            $db->table('dashboard_items')->insert($it);
            CLI::write("  ✔ {$it['accion_url']} insertado.", 'green');
            $insertados++;
        }

        CLI::write('', 'white');
        CLI::write("Insertados: {$insertados} · Ya existían: {$existentes}", 'green');
    }

    private function getConnection(string $env)
    {
        if ($env === 'local') {
            return Database::connect();
        }

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
            'encrypt'  => ['ssl_verify' => false],
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
        ];

        if (empty($cfg['hostname']) || empty($cfg['username'])) {
            CLI::error('Faltan credenciales database.production.* en .env.');
            return null;
        }

        return Database::connect($cfg);
    }
}

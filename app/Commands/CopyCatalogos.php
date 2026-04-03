<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CopyCatalogos extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:copy-catalogos';
    protected $description = 'Copia tablas de catálogo de propiedad_horizontal → tat_cycloid (DO + local)';

    public function run(array $params)
    {
        $doHost = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
        $doUser = 'cycloid_userdb';
        $doPass = 'AVNS_MR2SLvzRh3i_7o9fEHN';
        $doPort = 25060;

        // Tablas catálogo a copiar
        $catalogTables = [
            'accesos',
            'estandares',
            'estandares_accesos',
            'dashboard_items',
            'capacitaciones_sst',
            'policy_types',
            'report_type_table',
            'tbl_roles',
            'tbl_kpi_type',
            'tbl_kpi_definition',
            'tbl_measurement_period',
            'tbl_presupuesto_categorias',
            'tbl_presupuesto_items',
        ];

        // ══ PASO 1: Leer de propiedad_horizontal ══
        CLI::write('=== PASO 1: Leyendo catálogos de propiedad_horizontal ===', 'yellow');

        $srcConn = mysqli_init();
        mysqli_ssl_set($srcConn, null, null, null, null, null);
        if (!mysqli_real_connect($srcConn, $doHost, $doUser, $doPass, 'propiedad_horizontal', $doPort, null, MYSQLI_CLIENT_SSL)) {
            CLI::error('No se pudo conectar: ' . mysqli_connect_error());
            return;
        }
        $srcConn->set_charset('utf8mb4');

        $allData = [];
        foreach ($catalogTables as $table) {
            // Leer columnas
            $colResult = $srcConn->query("SHOW COLUMNS FROM `{$table}`");
            if (!$colResult) {
                CLI::error("  Tabla {$table} no existe, saltando.");
                continue;
            }
            $columns = [];
            while ($col = $colResult->fetch_assoc()) {
                $columns[] = $col['Field'];
            }

            // Leer datos
            $result = $srcConn->query("SELECT * FROM `{$table}`");
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }

            $allData[$table] = ['columns' => $columns, 'rows' => $rows];
            CLI::write("  {$table}: " . count($rows) . " registros", 'green');
        }
        $srcConn->close();

        // ══ PASO 2: Insertar en tat_cycloid (DO) ══
        CLI::write('=== PASO 2: Insertando en tat_cycloid (DO) ===', 'yellow');

        $doTgt = mysqli_init();
        mysqli_ssl_set($doTgt, null, null, null, null, null);
        if (!mysqli_real_connect($doTgt, $doHost, $doUser, $doPass, 'tat_cycloid', $doPort, null, MYSQLI_CLIENT_SSL)) {
            CLI::error('No se pudo conectar a tat_cycloid (DO): ' . mysqli_connect_error());
            return;
        }
        $doTgt->set_charset('utf8mb4');
        $doTgt->query("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($allData as $table => $info) {
            $this->insertData($doTgt, $table, $info['columns'], $info['rows'], 'DO');
        }
        $doTgt->query("SET FOREIGN_KEY_CHECKS = 1");
        $doTgt->close();

        // ══ PASO 3: Insertar en tat_cycloid (LOCAL) ══
        CLI::write('=== PASO 3: Insertando en tat_cycloid (LOCAL) ===', 'yellow');

        $localConn = new \mysqli('localhost', 'root', '', 'tat_cycloid', 3306);
        if ($localConn->connect_error) {
            CLI::error('No se pudo conectar a local: ' . $localConn->connect_error);
            return;
        }
        $localConn->set_charset('utf8mb4');
        $localConn->query("SET sql_mode = 'ANSI_QUOTES'");
        $localConn->query("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($allData as $table => $info) {
            $this->insertData($localConn, $table, $info['columns'], $info['rows'], 'LOCAL');
        }
        $localConn->query("SET FOREIGN_KEY_CHECKS = 1");
        $localConn->close();

        CLI::write('', 'white');
        CLI::write('========== COMPLETADO ==========', 'green');
    }

    private function insertData(\mysqli $conn, string $table, array $columns, array $rows, string $label): void
    {
        if (empty($rows)) {
            CLI::write("  {$label} {$table}: 0 registros (vacía)", 'yellow');
            return;
        }

        $ok = 0;
        $colList = implode(', ', array_map(fn($c) => "`{$c}`", $columns));

        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $col) {
                if ($row[$col] === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . $conn->real_escape_string($row[$col]) . "'";
                }
            }
            $valList = implode(', ', $values);

            try {
                $conn->query("REPLACE INTO `{$table}` ({$colList}) VALUES ({$valList})");
                $ok++;
            } catch (\mysqli_sql_exception $e) {
                CLI::error("  {$label} ERROR en {$table}: {$e->getMessage()}");
            }
        }

        CLI::write("  {$label} {$table}: {$ok} de " . count($rows) . " OK", 'green');
    }
}

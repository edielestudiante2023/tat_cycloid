<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FixTestClient extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:fix-test-client';
    protected $description = 'Asigna estándar Mensual al cliente de prueba';

    public function run(array $params)
    {
        $doHost = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
        $doUser = 'cycloid_userdb';
        $doPass = 'AVNS_MR2SLvzRh3i_7o9fEHN';
        $doPort = 25060;

        // DO
        CLI::write('=== Actualizando en DO ===', 'yellow');
        $doConn = mysqli_init();
        mysqli_ssl_set($doConn, null, null, null, null, null);
        mysqli_real_connect($doConn, $doHost, $doUser, $doPass, 'tat_cycloid', $doPort, null, MYSQLI_CLIENT_SSL);
        $doConn->set_charset('utf8mb4');
        $doConn->query("UPDATE tbl_clientes SET estandares = 'Mensual' WHERE correo_cliente = 'tienda.donpepe@test.com'");
        CLI::write("  DO: {$doConn->affected_rows} fila(s) actualizadas", 'green');
        $doConn->close();

        // LOCAL
        CLI::write('=== Actualizando en LOCAL ===', 'yellow');
        try {
            $localConn = @new \mysqli('localhost', 'root', '', 'tat_cycloid', 3306);
            if (!$localConn->connect_error) {
                $localConn->query("SET sql_mode = 'ANSI_QUOTES'");
                $localConn->query("UPDATE tbl_clientes SET estandares = 'Mensual' WHERE correo_cliente = 'tienda.donpepe@test.com'");
                CLI::write("  LOCAL: {$localConn->affected_rows} fila(s) actualizadas", 'green');
                $localConn->close();
            } else {
                CLI::write('  MySQL local no disponible', 'yellow');
            }
        } catch (\Exception $e) {
            CLI::write('  MySQL local no disponible', 'yellow');
        }

        CLI::write('Listo. El cliente ahora tiene estándar Mensual y debería ver los accesos.', 'green');
    }
}

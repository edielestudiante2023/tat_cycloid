<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * spark rutinas:limpiar-dia [YYYY-MM-DD] [id_cliente]
 *
 * Borra rutinas_registros de una fecha específica (default: hoy).
 * Si se pasa id_cliente, filtra solo a usuarios de ese cliente.
 * Útil para resetear un día de prueba en LOCAL.
 */
class RutinasLimpiarDia extends BaseCommand
{
    protected $group       = 'Rutinas';
    protected $name        = 'rutinas:limpiar-dia';
    protected $description = 'Borra rutinas_registros de una fecha (default: hoy), opcionalmente por cliente.';
    protected $usage       = 'rutinas:limpiar-dia [YYYY-MM-DD] [id_cliente]';

    public function run(array $params)
    {
        $fecha = $params[0] ?? date('Y-m-d');
        $idCliente = isset($params[1]) ? (int) $params[1] : 0;

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            CLI::error("Fecha inválida '{$fecha}'. Formato esperado: YYYY-MM-DD");
            return;
        }

        $db = Database::connect();
        CLI::write("Conectado a {$db->getDatabase()}", 'green');
        CLI::write("Limpiar rutinas_registros de fecha = {$fecha}" . ($idCliente ? " (cliente id={$idCliente})" : ''), 'yellow');

        $builder = $db->table('rutinas_registros')->where('fecha', $fecha);

        if ($idCliente > 0) {
            // Filtrar por usuarios del cliente (tanto empleados como dueño)
            $usuarios = $db->table('tbl_usuarios')
                ->select('id_usuario')
                ->where('id_entidad', $idCliente)
                ->whereIn('tipo_usuario', ['employee', 'client'])
                ->get()->getResultArray();

            if (empty($usuarios)) {
                CLI::write('No hay usuarios del cliente — nada que borrar.', 'white');
                return;
            }
            $ids = array_column($usuarios, 'id_usuario');
            $builder->whereIn('id_usuario', $ids);
        }

        $count = $builder->countAllResults(false); // no resetea el builder
        if ($count === 0) {
            CLI::write('No hay registros para esa combinación.', 'white');
            return;
        }

        $builder->delete();
        CLI::write("✔ Borrados {$count} registro(s) de {$fecha}.", 'green');
    }
}

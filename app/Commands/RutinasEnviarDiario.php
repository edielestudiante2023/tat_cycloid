<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\NotificadorRutinas;

class RutinasEnviarDiario extends BaseCommand
{
    protected $group       = 'Rutinas';
    protected $name        = 'rutinas:enviar-diario';
    protected $description = 'Envía recordatorio diario L-V con checklist tokenizado.';
    protected $usage       = 'rutinas:enviar-diario [YYYY-MM-DD]';

    public function run(array $params)
    {
        $fecha = $params[0] ?? date('Y-m-d');
        CLI::write("Rutinas — envío diario para {$fecha}", 'yellow');

        $notif  = new NotificadorRutinas();
        $result = $notif->enviarRecordatoriosDiarios($fecha);

        if ($result['omitidos'] === -1) {
            CLI::write('Fecha fin de semana. Nada que enviar.', 'white');
            return;
        }

        CLI::write("Enviados: {$result['enviados']}", 'green');
        CLI::write("Fallidos: {$result['fallidos']}", $result['fallidos'] > 0 ? 'red' : 'white');
        foreach ($result['detalles'] as $d) {
            CLI::write('  ' . $d, 'white');
        }
    }
}

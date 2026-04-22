<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * spark rutinas:seed                   → inserta 12 actividades demo (panadería) si no existen
 * spark rutinas:seed <email>           → inserta + asigna todas las actividades al usuario con ese email
 * spark rutinas:seed usuarios          → lista primeros 10 usuarios activos con email (para elegir)
 *
 * Idempotente: no duplica actividades ni asignaciones.
 */
class RutinasSeed extends BaseCommand
{
    protected $group       = 'Rutinas';
    protected $name        = 'rutinas:seed';
    protected $description = 'Puebla actividades demo (limpieza panadería) y opcionalmente las asigna a un usuario.';
    protected $usage       = 'rutinas:seed [email|usuarios]';
    protected $arguments   = [
        'arg' => 'Opcional: email del usuario a asignar, o literal "usuarios" para listar candidatos.',
    ];

    private array $actividades = [
        ['Limpieza de hornos y bandejas',          'Retirar residuos, limpiar interior y bandejas con desengrasante apto alimentos.', 'diaria', 2.00],
        ['Limpieza amasadoras y batidoras',        'Desmontar accesorios, lavar con jabón neutro, desinfectar y secar.',             'diaria', 2.00],
        ['Lavado de mesones de trabajo',           'Lavar con agua y jabón, aplicar desinfectante y secar antes de usar.',           'diaria', 1.50],
        ['Limpieza de pisos y desagües',           'Barrido húmedo, trapeado con desinfectante, limpieza de rejillas.',              'diaria', 1.00],
        ['Limpieza campana extractora',            'Lavar filtros, limpiar superficie y revisar estado del extractor.',              'L-V',    1.50],
        ['Desinfección de utensilios',             'Lavar, desinfectar con hipoclorito diluido y secar en rack.',                    'diaria', 1.50],
        ['Limpieza de vitrinas y exhibidores',     'Limpiar vidrios, superficies interiores y canastillas con paño húmedo.',        'diaria', 1.00],
        ['Limpieza de neveras y congeladores',     'Revisar temperatura, limpiar empaques y superficies interiores.',                'L-V',    1.50],
        ['Limpieza de lavamanos y baños',          'Desinfectar griferías, inodoro, reponer jabón y toallas desechables.',           'diaria', 1.00],
        ['Limpieza de canecas de residuos',        'Vaciar, lavar con detergente y desinfectar; cambiar bolsa.',                      'diaria', 0.50],
        ['Inspección visual de plagas',            'Revisar rincones, rendijas, paquetes y zonas húmedas. Reportar hallazgos.',      'L-V',    1.00],
        ['Limpieza de estantes del almacén',       'Organizar producto, limpiar polvo y verificar fechas de vencimiento.',           'L-V',    1.00],
    ];

    public function run(array $params)
    {
        $db = Database::connect();

        try {
            $db->query('SELECT 1');
            CLI::write("Conectado a {$db->getDatabase()}", 'green');
        } catch (\Throwable $e) {
            CLI::error('Fallo conexión: ' . $e->getMessage());
            return;
        }

        $arg = $params[0] ?? null;

        if ($arg === 'usuarios') {
            $this->listarUsuarios($db);
            return;
        }

        // 1) INSERTAR ACTIVIDADES (idempotente por nombre)
        CLI::write('', 'white');
        CLI::write('=== SEED ACTIVIDADES (PANADERÍA) ===', 'yellow');
        $ids = [];
        $insertadas = 0;
        foreach ($this->actividades as [$nombre, $desc, $frec, $peso]) {
            $existing = $db->table('rutinas_actividades')->where('nombre', $nombre)->get()->getRow();
            if ($existing) {
                $ids[] = (int) $existing->id_actividad;
                CLI::write("  · {$nombre} ya existía (id={$existing->id_actividad}).", 'white');
                continue;
            }
            $db->table('rutinas_actividades')->insert([
                'nombre'      => $nombre,
                'descripcion' => $desc,
                'frecuencia'  => $frec,
                'peso'        => $peso,
                'activa'      => 1,
            ]);
            $newId = (int) $db->insertID();
            $ids[] = $newId;
            $insertadas++;
            CLI::write("  ✔ {$nombre} (id={$newId}, peso={$peso}, {$frec})", 'green');
        }
        CLI::write("Total actividades: " . count($ids) . " · Nuevas: {$insertadas}", 'green');

        // 2) ASIGNAR (si se pasó email)
        if ($arg && filter_var($arg, FILTER_VALIDATE_EMAIL)) {
            CLI::write('', 'white');
            CLI::write("=== ASIGNACIÓN AL USUARIO {$arg} ===", 'yellow');

            $user = $db->table('tbl_usuarios')
                ->where('email', $arg)
                ->where('estado', 'activo')
                ->get()->getRow();

            if (!$user) {
                CLI::error("Usuario {$arg} no encontrado o inactivo.");
                CLI::write("Sugerencia: ejecuta 'php spark rutinas:seed usuarios' para ver candidatos.", 'white');
                return;
            }

            $asignadas = 0;
            foreach ($ids as $idAct) {
                $dup = $db->table('rutinas_asignaciones')
                    ->where('id_usuario', (int)$user->id_usuario)
                    ->where('id_actividad', $idAct)
                    ->countAllResults();
                if ($dup > 0) continue;
                $db->table('rutinas_asignaciones')->insert([
                    'id_usuario'   => (int)$user->id_usuario,
                    'id_actividad' => $idAct,
                    'activa'       => 1,
                ]);
                $asignadas++;
            }

            CLI::write("Asignadas al usuario {$user->nombre_completo} (id={$user->id_usuario}): {$asignadas} nuevas", 'green');
        } elseif ($arg) {
            CLI::error("Argumento '{$arg}' no es un email válido.");
            CLI::write("Uso: php spark rutinas:seed user@example.com", 'white');
        } else {
            CLI::write('', 'white');
            CLI::write('Sin asignación (no se pasó email). Para asignar:', 'white');
            CLI::write('  php spark rutinas:seed usuarios          # ver candidatos', 'white');
            CLI::write('  php spark rutinas:seed user@example.com  # asignar todas al usuario', 'white');
        }

        CLI::write('', 'white');
        CLI::write('=== FIN SEED ===', 'green');
    }

    private function listarUsuarios($db): void
    {
        CLI::write('=== USUARIOS CANDIDATOS (activos con email) ===', 'yellow');
        $rows = $db->table('tbl_usuarios')
            ->select('id_usuario, nombre_completo, email, tipo_usuario')
            ->where('estado', 'activo')
            ->where('email IS NOT NULL')
            ->where('email <>', '')
            ->orderBy('tipo_usuario')
            ->orderBy('nombre_completo')
            ->limit(20)
            ->get()->getResultArray();

        if (empty($rows)) {
            CLI::error('No hay usuarios activos con email.');
            return;
        }

        foreach ($rows as $r) {
            CLI::write(
                sprintf('  %-4d  %-10s  %-35s  %s',
                    $r['id_usuario'],
                    $r['tipo_usuario'],
                    $r['email'],
                    $r['nombre_completo']
                ),
                'white'
            );
        }
        CLI::write('', 'white');
        CLI::write('Luego ejecuta: php spark rutinas:seed <email>', 'green');
    }
}

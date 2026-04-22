<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * spark rutinas:seed-demo [email_dueno]
 *
 * Siembra un escenario demo COMPLETO para probar el módulo:
 *   1) Verifica que existan las 12 actividades de panadería (llama a rutinas:seed si faltan).
 *   2) Crea 3 empleados (tipo_usuario='employee') para el cliente del dueño indicado.
 *   3) Distribuye las 12 actividades entre los 3 empleados (con overlap realista).
 *   4) Genera registros históricos de los últimos 10 días hábiles con cumplimiento variable.
 *   5) Al final imprime credenciales listas para probar.
 *
 * Si no se pasa email, usa 'tienda.donpepe@test.com'.
 * Idempotente: no duplica empleados, asignaciones ni registros.
 */
class RutinasSeedDemo extends BaseCommand
{
    protected $group       = 'Rutinas';
    protected $name        = 'rutinas:seed-demo';
    protected $description = 'Puebla un escenario demo completo: empleados + asignaciones + registros.';
    protected $usage       = 'rutinas:seed-demo [email_dueno]';

    private array $empleadosDemo = [
        ['nombre' => 'Ana Ramírez (Panadera)',   'email' => 'panadera@donpepe.test',    'password' => 'demo1234'],
        ['nombre' => 'Luis Torres (Aseador)',    'email' => 'aseador@donpepe.test',     'password' => 'demo1234'],
        ['nombre' => 'Carla Vega (Cajera)',      'email' => 'cajera@donpepe.test',      'password' => 'demo1234'],
    ];

    // Actividades por perfil (nombres deben coincidir con RutinasSeed)
    private array $actPorPerfil = [
        'panadera' => [
            'Limpieza de hornos y bandejas',
            'Limpieza amasadoras y batidoras',
            'Lavado de mesones de trabajo',
            'Desinfección de utensilios',
            'Limpieza campana extractora',
        ],
        'aseador' => [
            'Limpieza de pisos y desagües',
            'Limpieza de lavamanos y baños',
            'Limpieza de canecas de residuos',
            'Inspección visual de plagas',
            'Limpieza de estantes del almacén',
        ],
        'cajera' => [
            'Limpieza de vitrinas y exhibidores',
            'Limpieza de neveras y congeladores',
        ],
    ];

    public function run(array $params)
    {
        $emailDueno = $params[0] ?? 'tienda.donpepe@test.com';
        $db = Database::connect();

        CLI::write("=== SEED DEMO COMPLETO ===", 'yellow');
        CLI::write("Dueño: {$emailDueno}", 'white');

        // 1) Resolver cliente y su id_consultor
        $dueno = $db->query(
            "SELECT id_usuario, id_entidad AS id_cliente, nombre_completo
               FROM tbl_usuarios
              WHERE email = ? AND tipo_usuario = 'client' AND estado = 'activo'
              LIMIT 1",
            [$emailDueno]
        )->getRowArray();

        if (!$dueno) {
            CLI::error("Dueño {$emailDueno} no encontrado (tipo_usuario='client' activo).");
            return;
        }
        $idCliente = (int) $dueno['id_cliente'];
        CLI::write("Cliente id={$idCliente} — dueño: {$dueno['nombre_completo']}", 'green');

        // 2) Limpieza: quitar asignaciones antiguas del usuario dueño (antes asignábamos al dueño)
        $aff = $db->table('rutinas_asignaciones')->where('id_usuario', (int)$dueno['id_usuario'])->delete();
        if ($aff) {
            CLI::write("  · Limpiado: asignaciones previas del dueño (ya no aplica).", 'white');
        }
        $db->table('rutinas_registros')->where('id_usuario', (int)$dueno['id_usuario'])->delete();

        // 3) Actividades existentes (de RutinasSeed)
        $actividades = $db->table('rutinas_actividades')->where('activa', 1)->get()->getResultArray();
        if (count($actividades) < 12) {
            CLI::error("Faltan actividades. Ejecuta primero: php spark rutinas:seed");
            return;
        }
        $actByName = [];
        foreach ($actividades as $a) $actByName[$a['nombre']] = (int) $a['id_actividad'];

        // 4) Crear/asegurar empleados
        $userModel = new UserModel();
        $empleadosIds = [];
        foreach ($this->empleadosDemo as $i => $emp) {
            $perfil = ['panadera','aseador','cajera'][$i];
            $existing = $db->table('tbl_usuarios')->where('email', $emp['email'])->get()->getRowArray();
            if ($existing) {
                $id = (int) $existing['id_usuario'];
                // Reaseguramos tipo y entidad
                $db->table('tbl_usuarios')->where('id_usuario', $id)->update([
                    'tipo_usuario' => 'employee',
                    'id_entidad'   => $idCliente,
                    'estado'       => 'activo',
                ]);
                CLI::write("  · Empleado ya existía: {$emp['email']} (id={$id})", 'white');
            } else {
                $userModel->createUser([
                    'nombre_completo' => $emp['nombre'],
                    'email'           => $emp['email'],
                    'password'        => $emp['password'],
                    'tipo_usuario'    => 'employee',
                    'id_entidad'      => $idCliente,
                    'estado'          => 'activo',
                ]);
                $id = (int) $userModel->getInsertID();
                CLI::write("  ✔ Empleado creado: {$emp['email']} (id={$id})", 'green');
            }
            $empleadosIds[$perfil] = $id;
        }

        // 5) Asignaciones por perfil
        $totalAsig = 0;
        foreach ($this->actPorPerfil as $perfil => $nombres) {
            $idEmp = $empleadosIds[$perfil] ?? null;
            if (!$idEmp) continue;
            foreach ($nombres as $nombreAct) {
                $idAct = $actByName[$nombreAct] ?? null;
                if (!$idAct) continue;
                $dup = $db->table('rutinas_asignaciones')
                    ->where('id_usuario', $idEmp)
                    ->where('id_actividad', $idAct)
                    ->countAllResults();
                if ($dup === 0) {
                    $db->table('rutinas_asignaciones')->insert([
                        'id_usuario'   => $idEmp,
                        'id_actividad' => $idAct,
                        'activa'       => 1,
                    ]);
                    $totalAsig++;
                }
            }
        }
        CLI::write("Asignaciones creadas: {$totalAsig}", 'green');

        // 6) Registros históricos de los últimos 10 días hábiles
        $registrosCreados = 0;
        $diasGenerados = 0;
        $fecha = new \DateTime('today');
        while ($diasGenerados < 10) {
            $fecha->modify('-1 day');
            $dow = (int) $fecha->format('N');
            if ($dow >= 6) continue; // solo L-V

            $fechaStr = $fecha->format('Y-m-d');
            $diasGenerados++;

            // Cumplimiento aleatorio por empleado: 0%, 40%, 70%, 100% con diferentes probabilidades
            foreach ($empleadosIds as $perfil => $idEmp) {
                // Determinar cumplimiento del día (por empleado)
                $dado = random_int(1, 100);
                if ($dado <= 40)      $cumplimiento = 1.0;  // 40% → día completo
                elseif ($dado <= 65)  $cumplimiento = 0.7;  // 25% → 70%
                elseif ($dado <= 85)  $cumplimiento = 0.4;  // 20% → 40%
                else                  $cumplimiento = 0.0;  // 15% → 0%

                if ($cumplimiento === 0.0) continue;

                $nombres = $this->actPorPerfil[$perfil] ?? [];
                shuffle($nombres);
                $aMarcar = (int) ceil(count($nombres) * $cumplimiento);
                for ($j = 0; $j < $aMarcar; $j++) {
                    $idAct = $actByName[$nombres[$j]] ?? null;
                    if (!$idAct) continue;

                    $dup = $db->table('rutinas_registros')
                        ->where('id_usuario', $idEmp)
                        ->where('id_actividad', $idAct)
                        ->where('fecha', $fechaStr)
                        ->countAllResults();
                    if ($dup > 0) continue;

                    $hora = sprintf('%s %02d:%02d:00', $fechaStr, random_int(6, 18), random_int(0, 59));
                    $db->table('rutinas_registros')->insert([
                        'id_usuario'      => $idEmp,
                        'id_actividad'    => $idAct,
                        'fecha'           => $fechaStr,
                        'completada'      => 1,
                        'hora_completado' => $hora,
                    ]);
                    $registrosCreados++;
                }
            }
        }
        CLI::write("Registros históricos creados: {$registrosCreados} (últimos {$diasGenerados} días hábiles)", 'green');

        // 7) Resumen credenciales
        CLI::write('', 'white');
        CLI::write('=== CREDENCIALES DEMO ===', 'yellow');
        CLI::write("Dueño:       {$emailDueno} / (su password real)", 'white');
        foreach ($this->empleadosDemo as $emp) {
            CLI::write("Empleado:    {$emp['email']} / {$emp['password']}", 'white');
        }
        CLI::write('', 'white');
        CLI::write("Calendario: /rutinas/calendario?cliente={$idCliente}", 'green');
        CLI::write("Empleados:  /empleados?cliente={$idCliente}", 'green');
        CLI::write("Asignaciones: /rutinas/asignaciones?cliente={$idCliente}", 'green');
        CLI::write('', 'white');
        CLI::write('=== FIN SEED DEMO ===', 'green');
    }
}

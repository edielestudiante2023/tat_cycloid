<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * spark rutinas:sinc-prod
 *
 * Replica datos demo del módulo Rutinas de LOCAL → PRODUCCIÓN.
 * Idempotente: usa claves naturales (nombre de actividad, email de usuario,
 * fecha+email+actividad para registros).
 *
 * Copia:
 *   1) rutinas_actividades (por nombre)
 *   2) tbl_usuarios demo (por email) — solo crea si no existen (panadera, aseador, cajera)
 *   3) rutinas_asignaciones (por email+nombre_actividad)
 *   4) rutinas_registros (por email+nombre_actividad+fecha)
 */
class RutinasSincProd extends BaseCommand
{
    protected $group       = 'Rutinas';
    protected $name        = 'rutinas:sinc-prod';
    protected $description = 'Sincroniza datos demo Rutinas de LOCAL a PRODUCCIÓN (idempotente).';
    protected $usage       = 'rutinas:sinc-prod';

    public function run(array $params)
    {
        CLI::write('=== SINC RUTINAS LOCAL → PROD ===', 'yellow');

        // Conexiones
        $local = Database::connect();
        try {
            $local->query('SELECT 1');
            CLI::write("LOCAL conectado a {$local->getDatabase()}", 'green');
        } catch (\Throwable $e) {
            CLI::error('Fallo local: ' . $e->getMessage());
            return;
        }

        $prod = $this->getProdConnection();
        if (!$prod) return;

        try {
            $prod->query('SELECT 1');
            CLI::write("PROD  conectado a {$prod->getDatabase()}", 'green');
        } catch (\Throwable $e) {
            CLI::error('Fallo prod: ' . $e->getMessage());
            return;
        }

        // ─── 1) Actividades ───
        CLI::write('', 'white');
        CLI::write('--- Actividades ---', 'yellow');
        $actLocal = $local->table('rutinas_actividades')->get()->getResultArray();
        $actMapProd = []; // nombre → id_actividad en PROD
        $actInsertadas = 0;
        foreach ($actLocal as $a) {
            $existing = $prod->table('rutinas_actividades')->where('nombre', $a['nombre'])->get()->getRowArray();
            if ($existing) {
                $actMapProd[$a['nombre']] = (int) $existing['id_actividad'];
                continue;
            }
            $prod->table('rutinas_actividades')->insert([
                'nombre'      => $a['nombre'],
                'descripcion' => $a['descripcion'],
                'frecuencia'  => $a['frecuencia'],
                'peso'        => $a['peso'],
                'activa'      => $a['activa'],
            ]);
            $newId = (int) $prod->insertID();
            $actMapProd[$a['nombre']] = $newId;
            $actInsertadas++;
        }
        CLI::write("Actividades: " . count($actMapProd) . " totales · {$actInsertadas} nuevas", 'green');

        // ─── 2) Usuarios (empleados demo) ───
        CLI::write('', 'white');
        CLI::write('--- Usuarios demo ---', 'yellow');
        $emailsDemo = ['panadera@donpepe.test', 'aseador@donpepe.test', 'cajera@donpepe.test', 'tienda.donpepe@test.com'];

        // Necesitamos resolver id_cliente en PROD usando email del dueño (tienda.donpepe@test.com).
        $duenoProd = $prod->table('tbl_usuarios')
            ->where('email', 'tienda.donpepe@test.com')
            ->where('tipo_usuario', 'client')
            ->get()->getRowArray();

        if (!$duenoProd) {
            CLI::error('No se encontró dueño tienda.donpepe@test.com en PROD. Aborto.');
            CLI::write('Crea primero el cliente + usuario dueño en PROD antes de sincronizar.', 'white');
            return;
        }
        $idClienteProd = (int) $duenoProd['id_entidad'];
        CLI::write("PROD: cliente id={$idClienteProd} (dueño: {$duenoProd['nombre_completo']})", 'green');

        // Crear empleados si no existen (copiar nombre/password desde local)
        $userMapProd = []; // email → id_usuario en PROD
        $userMapProd['tienda.donpepe@test.com'] = (int) $duenoProd['id_usuario'];

        $usuariosInsertados = 0;
        foreach ($emailsDemo as $email) {
            if ($email === 'tienda.donpepe@test.com') continue; // ya resuelto

            $existsProd = $prod->table('tbl_usuarios')->where('email', $email)->get()->getRowArray();
            if ($existsProd) {
                $userMapProd[$email] = (int) $existsProd['id_usuario'];
                // Asegurar tipo y entidad
                $prod->table('tbl_usuarios')->where('id_usuario', (int)$existsProd['id_usuario'])->update([
                    'tipo_usuario' => 'employee',
                    'id_entidad'   => $idClienteProd,
                    'estado'       => 'activo',
                ]);
                continue;
            }

            $local_u = $local->table('tbl_usuarios')->where('email', $email)->get()->getRowArray();
            if (!$local_u) continue;

            $prod->table('tbl_usuarios')->insert([
                'email'           => $local_u['email'],
                'password'        => $local_u['password'], // ya está hasheado
                'nombre_completo' => $local_u['nombre_completo'],
                'tipo_usuario'    => 'employee',
                'id_entidad'      => $idClienteProd,
                'estado'          => 'activo',
            ]);
            $userMapProd[$email] = (int) $prod->insertID();
            $usuariosInsertados++;
            CLI::write("  ✔ {$email} creado (id=" . $userMapProd[$email] . ")", 'green');
        }
        CLI::write("Usuarios demo: " . count($userMapProd) . " resueltos · {$usuariosInsertados} nuevos", 'green');

        // ─── 3) Asignaciones ───
        CLI::write('', 'white');
        CLI::write('--- Asignaciones ---', 'yellow');
        // Traer asignaciones locales con JOINs para obtener email y nombre_actividad
        $asigLocal = $local->query(
            "SELECT u.email, a.nombre AS actividad, ra.activa
               FROM rutinas_asignaciones ra
               JOIN tbl_usuarios u          ON u.id_usuario   = ra.id_usuario
               JOIN rutinas_actividades a   ON a.id_actividad = ra.id_actividad
              WHERE u.email IN ('" . implode("','", $emailsDemo) . "')"
        )->getResultArray();

        $asigInsertadas = 0;
        foreach ($asigLocal as $a) {
            $idUsuarioProd = $userMapProd[$a['email']] ?? null;
            $idActProd     = $actMapProd[$a['actividad']] ?? null;
            if (!$idUsuarioProd || !$idActProd) continue;

            $dup = $prod->table('rutinas_asignaciones')
                ->where('id_usuario', $idUsuarioProd)
                ->where('id_actividad', $idActProd)
                ->countAllResults();
            if ($dup > 0) continue;

            $prod->table('rutinas_asignaciones')->insert([
                'id_usuario'   => $idUsuarioProd,
                'id_actividad' => $idActProd,
                'activa'       => (int) $a['activa'],
            ]);
            $asigInsertadas++;
        }
        CLI::write("Asignaciones: " . count($asigLocal) . " totales · {$asigInsertadas} nuevas", 'green');

        // ─── 4) Registros históricos ───
        CLI::write('', 'white');
        CLI::write('--- Registros históricos ---', 'yellow');
        $regLocal = $local->query(
            "SELECT u.email, a.nombre AS actividad, r.fecha, r.completada, r.hora_completado
               FROM rutinas_registros r
               JOIN tbl_usuarios u          ON u.id_usuario   = r.id_usuario
               JOIN rutinas_actividades a   ON a.id_actividad = r.id_actividad
              WHERE u.email IN ('" . implode("','", $emailsDemo) . "')"
        )->getResultArray();

        $regInsertados = 0;
        foreach ($regLocal as $r) {
            $idUsuarioProd = $userMapProd[$r['email']] ?? null;
            $idActProd     = $actMapProd[$r['actividad']] ?? null;
            if (!$idUsuarioProd || !$idActProd) continue;

            $dup = $prod->table('rutinas_registros')
                ->where('id_usuario', $idUsuarioProd)
                ->where('id_actividad', $idActProd)
                ->where('fecha', $r['fecha'])
                ->countAllResults();
            if ($dup > 0) continue;

            $prod->table('rutinas_registros')->insert([
                'id_usuario'      => $idUsuarioProd,
                'id_actividad'    => $idActProd,
                'fecha'           => $r['fecha'],
                'completada'      => (int) $r['completada'],
                'hora_completado' => $r['hora_completado'],
            ]);
            $regInsertados++;
        }
        CLI::write("Registros: " . count($regLocal) . " totales · {$regInsertados} nuevos", 'green');

        CLI::write('', 'white');
        CLI::write('=== SINC COMPLETA ===', 'green');
        CLI::write("Cliente en PROD: id={$idClienteProd}", 'white');
        CLI::write("Calendario:   /rutinas/calendario?cliente={$idClienteProd}", 'white');
        CLI::write("Empleados:    /empleados?cliente={$idClienteProd}", 'white');
        CLI::write("Asignaciones: /rutinas/asignaciones?cliente={$idClienteProd}", 'white');
    }

    private function getProdConnection()
    {
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

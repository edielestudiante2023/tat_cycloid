<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateTestClient extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:create-test-client';
    protected $description = 'Crea un cliente y usuario de prueba en tat_cycloid (DO + local)';

    public function run(array $params)
    {
        $doHost = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
        $doUser = 'cycloid_userdb';
        $doPass = 'AVNS_MR2SLvzRh3i_7o9fEHN';
        $doPort = 25060;

        // ── PASO 1: Leer estructura ──
        CLI::write('=== PASO 1: Leyendo estructura ===', 'yellow');

        $doConn = mysqli_init();
        mysqli_ssl_set($doConn, null, null, null, null, null);
        if (!mysqli_real_connect($doConn, $doHost, $doUser, $doPass, 'tat_cycloid', $doPort, null, MYSQLI_CLIENT_SSL)) {
            CLI::error('No se pudo conectar DO: ' . mysqli_connect_error());
            return;
        }
        $doConn->set_charset('utf8mb4');

        // Estructura de clientes
        $colResult = $doConn->query("SHOW COLUMNS FROM tbl_clientes");
        $clientCols = [];
        while ($col = $colResult->fetch_assoc()) {
            $clientCols[] = $col;
            CLI::write("  tbl_clientes: {$col['Field']} ({$col['Type']})", 'white');
        }

        // Consultores disponibles
        $result = $doConn->query("SELECT id_consultor, nombre_consultor FROM tbl_consultor LIMIT 5");
        CLI::write('Consultores disponibles:', 'yellow');
        $firstConsultorId = null;
        while ($row = $result->fetch_assoc()) {
            if (!$firstConsultorId) $firstConsultorId = $row['id_consultor'];
            CLI::write("  ID:{$row['id_consultor']} - {$row['nombre_consultor']}", 'white');
        }

        $doConn->close();

        // ── PASO 2: Crear cliente y usuario en DO ──
        CLI::write('=== PASO 2: Creando en tat_cycloid (DO) ===', 'yellow');

        $doConn = mysqli_init();
        mysqli_ssl_set($doConn, null, null, null, null, null);
        mysqli_real_connect($doConn, $doHost, $doUser, $doPass, 'tat_cycloid', $doPort, null, MYSQLI_CLIENT_SSL);
        $doConn->set_charset('utf8mb4');

        $this->createClientAndUser($doConn, $firstConsultorId, 'DO');
        $doConn->close();

        // ── PASO 3: Crear en LOCAL (si MySQL está corriendo) ──
        CLI::write('=== PASO 3: Creando en tat_cycloid (LOCAL) ===', 'yellow');
        try {
            $localConn = @new \mysqli('localhost', 'root', '', 'tat_cycloid', 3306);
            if ($localConn->connect_error) {
                CLI::write('  MySQL local no disponible, saltando.', 'yellow');
            } else {
                $localConn->set_charset('utf8mb4');
                $localConn->query("SET sql_mode = 'ANSI_QUOTES'");
                $this->createClientAndUser($localConn, $firstConsultorId, 'LOCAL');
                $localConn->close();
            }
        } catch (\Exception $e) {
            CLI::write('  MySQL local no disponible, saltando.', 'yellow');
        }

        CLI::write('', 'white');
        CLI::write('========================================', 'green');
        CLI::write('CREDENCIALES DEL CLIENTE DE PRUEBA:', 'yellow');
        CLI::write('  Email:    tienda.donpepe@test.com', 'green');
        CLI::write('  Password: Cycloid2026!', 'green');
        CLI::write('========================================', 'green');
    }

    private function createClientAndUser(\mysqli $conn, ?int $consultorId, string $label): void
    {
        $password = password_hash('Cycloid2026!', PASSWORD_DEFAULT);

        // Crear cliente
        try {
            $conn->query("INSERT INTO tbl_clientes (
                nombre_cliente, nit_cliente, direccion_cliente, telefono_1_cliente, telefono_2_cliente,
                correo_cliente, correo_consejo_admon, nombre_rep_legal, cedula_rep_legal,
                id_consultor, estado, ciudad_cliente, usuario, password,
                codigo_actividad_economica, fecha_ingreso, `datetime`,
                persona_contacto_compras, persona_contacto_operaciones, persona_contacto_pagos,
                horarios_y_dias, vendedor, plazo_cartera, estandares
            ) VALUES (
                'Tienda Don Pepe - Prueba',
                900123456,
                'Calle 50 #30-20 Local 3, Bogotá',
                '3101234567', '',
                'tienda.donpepe@test.com', '',
                'Pedro Pérez López', '80123456',
                " . ($consultorId ?? 'NULL') . ",
                'activo', 'Bogotá',
                'tienda.donpepe@test.com',
                '{$conn->real_escape_string($password)}',
                '4711', CURDATE(), NOW(),
                '', '', '',
                '', '', '', ''
            ) ON DUPLICATE KEY UPDATE nombre_cliente = nombre_cliente");
            $clientId = (int)$conn->insert_id;
            if ($clientId === 0) {
                $res = $conn->query("SELECT id_cliente FROM tbl_clientes WHERE nit_cliente = 900123456 LIMIT 1");
                $clientId = (int)$res->fetch_row()[0];
            }
            CLI::write("  {$label} Cliente creado: ID {$clientId}", 'green');
        } catch (\mysqli_sql_exception $e) {
            CLI::error("  {$label} Error cliente: {$e->getMessage()}");
            return;
        }

        // Crear usuario tipo client
        try {
            $conn->query("INSERT INTO tbl_usuarios (
                nombre_completo, email, password, tipo_usuario, id_entidad, estado
            ) VALUES (
                'Pedro Pérez López',
                'tienda.donpepe@test.com',
                '{$conn->real_escape_string($password)}',
                'client',
                {$clientId},
                'activo'
            ) ON DUPLICATE KEY UPDATE nombre_completo = nombre_completo");
            $userId = (int)$conn->insert_id;
            if ($userId === 0) {
                $res = $conn->query("SELECT id_usuario FROM tbl_usuarios WHERE email = 'tienda.donpepe@test.com' LIMIT 1");
                $userId = (int)$res->fetch_row()[0];
            }
            CLI::write("  {$label} Usuario creado: ID {$userId}", 'green');
        } catch (\mysqli_sql_exception $e) {
            CLI::error("  {$label} Error usuario: {$e->getMessage()}");
            return;
        }

        // Asignar rol client (id_rol = 3)
        try {
            $conn->query("REPLACE INTO tbl_usuario_roles (id_usuario, id_rol) VALUES ({$userId}, 3)");
            CLI::write("  {$label} Rol client asignado", 'green');
        } catch (\mysqli_sql_exception $e) {
            CLI::error("  {$label} Error rol: {$e->getMessage()}");
        }
    }
}

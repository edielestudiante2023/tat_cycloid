<?php
namespace App\Models;

use CodeIgniter\Model;

class SolicitudAnulacionModel extends Model
{
    protected $table         = 'tbl_solicitud_anulacion';
    protected $primaryKey    = 'id_solicitud';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'token', 'tipo_registro', 'id_registro', 'id_registro_secundario',
        'id_cliente', 'id_consultor', 'justificacion',
        'estado', 'nota_respuesta', 'fecha_solicitud', 'fecha_respuesta',
    ];

    public function porToken(string $token): ?array
    {
        $row = $this->where('token', $token)->first();
        return $row ?: null;
    }

    public function generarToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

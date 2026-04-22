<?php
namespace App\Models;

use CodeIgniter\Model;

class BomberosSolicitudModel extends Model
{
    protected $table         = 'tbl_bomberos_solicitud';
    protected $primaryKey    = 'id';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    protected $allowedFields = [
        'id_cliente', 'anio', 'departamento', 'municipio',
        'estado', 'fecha_radicacion', 'numero_radicado', 'observaciones',
    ];

    public const ESTADOS = ['borrador','listo','radicado','aprobado','rechazado'];

    /**
     * Busca la solicitud del cliente para un año. Si no existe, la crea en estado borrador.
     */
    public function getOrCreate(int $idCliente, int $anio): array
    {
        $row = $this->where('id_cliente', $idCliente)->where('anio', $anio)->first();
        if ($row) return $row;

        $id = $this->insert([
            'id_cliente'   => $idCliente,
            'anio'         => $anio,
            'departamento' => 'Cundinamarca',
            'municipio'    => 'Soacha',
            'estado'       => 'borrador',
        ]);
        return $this->find($id);
    }

    /**
     * Lista todos los años donde hay solicitud para un cliente.
     */
    public function aniosDeCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
                    ->orderBy('anio', 'DESC')
                    ->findAll();
    }
}

<?php
namespace App\Models;

use CodeIgniter\Model;

class TrabajadorModel extends Model
{
    protected $table         = 'tbl_trabajadores';
    protected $primaryKey    = 'id_trabajador';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    protected $allowedFields = [
        'id_cliente',
        'nombre',
        'tipo_id',
        'numero_id',
        'cargo',
        'fecha_ingreso',
        'telefono',
        'tipo_contrato',
        'fecha_terminacion',
        'manipula_alimentos',
        'activo',
    ];

    /**
     * Devuelve los trabajadores activos de un cliente con su conteo de soportes.
     */
    public function listarPorCliente(int $idCliente, bool $soloActivos = true): array
    {
        $builder = $this->db->table('tbl_trabajadores t')
            ->select("t.*,
                      (SELECT COUNT(*) FROM tbl_trabajador_soporte s WHERE s.id_trabajador = t.id_trabajador) AS total_soportes")
            ->where('t.id_cliente', $idCliente)
            ->orderBy('t.activo', 'DESC')
            ->orderBy('t.nombre', 'ASC');

        if ($soloActivos) {
            $builder->where('t.activo', 1);
        }

        return $builder->get()->getResultArray();
    }
}

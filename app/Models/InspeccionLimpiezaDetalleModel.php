<?php
namespace App\Models;

use CodeIgniter\Model;

class InspeccionLimpiezaDetalleModel extends Model
{
    protected $table         = 'tbl_inspeccion_limpieza_detalle';
    protected $primaryKey    = 'id';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'id_inspeccion', 'id_item', 'estado', 'foto', 'observaciones',
    ];

    /**
     * Lista con join a tbl_limpieza_item.
     */
    public function porInspeccion(int $idInspeccion): array
    {
        return $this->db->table('tbl_inspeccion_limpieza_detalle d')
            ->select('d.*, i.nombre AS item_nombre, i.icono AS item_icono, i.orden AS item_orden')
            ->join('tbl_limpieza_item i', 'i.id_item = d.id_item', 'left')
            ->where('d.id_inspeccion', $idInspeccion)
            ->orderBy('i.orden', 'ASC')
            ->orderBy('i.nombre', 'ASC')
            ->get()->getResultArray();
    }
}

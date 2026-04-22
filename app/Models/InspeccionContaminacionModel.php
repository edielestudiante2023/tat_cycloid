<?php
namespace App\Models;

use CodeIgniter\Model;

class InspeccionContaminacionModel extends Model
{
    protected $table         = 'tbl_inspeccion_contaminacion';
    protected $primaryKey    = 'id';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    protected $allowedFields = [
        'id_cliente','fecha_hora','observaciones_generales','resultado_general',
        'id_reporte','registrado_por',
    ];

    public function listarPorCliente(int $idCliente): array
    {
        $rows = $this->db->table('tbl_inspeccion_contaminacion i')
            ->select("i.*,
                      (SELECT COUNT(*) FROM tbl_inspeccion_contaminacion_detalle d WHERE d.id_inspeccion=i.id) AS total_items,
                      (SELECT COUNT(*) FROM tbl_inspeccion_contaminacion_detalle d WHERE d.id_inspeccion=i.id AND d.estado='no_cumple') AS total_incumplen,
                      (SELECT COUNT(*) FROM tbl_inspeccion_contaminacion_detalle d WHERE d.id_inspeccion=i.id AND d.foto IS NOT NULL AND d.foto<>'') AS total_fotos")
            ->where('i.id_cliente', $idCliente)
            ->orderBy('i.fecha_hora','DESC')
            ->get()->getResultArray();

        foreach ($rows as &$r) {
            $r['thumbs'] = $this->db->table('tbl_inspeccion_contaminacion_detalle')
                ->select('foto')->where('id_inspeccion', $r['id'])
                ->where('foto IS NOT NULL')->where('foto <>','')
                ->orderBy('id','ASC')->limit(4)->get()->getResultArray();
        }
        return $rows;
    }
}

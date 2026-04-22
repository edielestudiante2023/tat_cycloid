<?php
namespace App\Models;

use CodeIgniter\Model;

class InspeccionLimpiezaLocalModel extends Model
{
    protected $table         = 'tbl_inspeccion_limpieza_local';
    protected $primaryKey    = 'id';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    protected $allowedFields = [
        'id_cliente', 'fecha_hora', 'observaciones_generales',
        'resultado_general', 'id_reporte', 'registrado_por',
    ];

    public function listarPorCliente(int $idCliente): array
    {
        $rows = $this->db->table('tbl_inspeccion_limpieza_local i')
            ->select("i.*,
                      (SELECT COUNT(*) FROM tbl_inspeccion_limpieza_detalle d WHERE d.id_inspeccion = i.id) AS total_items,
                      (SELECT COUNT(*) FROM tbl_inspeccion_limpieza_detalle d WHERE d.id_inspeccion = i.id AND d.estado = 'sucio') AS total_sucios,
                      (SELECT COUNT(*) FROM tbl_inspeccion_limpieza_detalle d WHERE d.id_inspeccion = i.id AND d.foto IS NOT NULL AND d.foto <> '') AS total_fotos")
            ->where('i.id_cliente', $idCliente)
            ->orderBy('i.fecha_hora', 'DESC')
            ->get()->getResultArray();

        // Añadir primeras 4 fotos como thumbnails por inspección
        foreach ($rows as &$r) {
            $r['thumbs'] = $this->db->table('tbl_inspeccion_limpieza_detalle')
                ->select('foto')
                ->where('id_inspeccion', $r['id'])
                ->where('foto IS NOT NULL')
                ->where('foto <>', '')
                ->orderBy('id', 'ASC')
                ->limit(4)
                ->get()->getResultArray();
        }
        return $rows;
    }
}

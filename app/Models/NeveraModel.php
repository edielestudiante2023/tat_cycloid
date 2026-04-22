<?php
namespace App\Models;

use CodeIgniter\Model;

class NeveraModel extends Model
{
    protected $table         = 'tbl_nevera';
    protected $primaryKey    = 'id_nevera';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    protected $allowedFields = [
        'id_cliente', 'nombre', 'tipo', 'ubicacion', 'foto_equipo',
        'rango_temp_min', 'rango_temp_max',
        'controla_humedad', 'rango_humedad_min', 'rango_humedad_max',
        'activo',
    ];

    public const TIPOS = [
        'refrigeracion' => 'Refrigeración (0–8°C)',
        'congelacion'   => 'Congelación (-18°C)',
        'mixta'         => 'Mixta',
    ];

    /**
     * Rangos default por tipo.
     */
    public static function rangoDefault(string $tipo): array
    {
        return match ($tipo) {
            'congelacion' => ['min' => -25.0, 'max' => -15.0],
            'mixta'       => ['min' => -18.0, 'max' => 8.0],
            default       => ['min' => 0.0,   'max' => 8.0],
        };
    }

    /**
     * Lista neveras activas de un cliente con su conteo de mediciones.
     */
    public function listarPorCliente(int $idCliente, bool $soloActivas = true): array
    {
        $builder = $this->db->table('tbl_nevera n')
            ->select("n.*,
                      (SELECT COUNT(*) FROM tbl_inspeccion_nevera i WHERE i.id_nevera = n.id_nevera) AS total_mediciones,
                      (SELECT COUNT(*) FROM tbl_inspeccion_nevera i WHERE i.id_nevera = n.id_nevera AND i.dentro_rango = 0) AS mediciones_fuera_rango,
                      (SELECT i.fecha_hora FROM tbl_inspeccion_nevera i WHERE i.id_nevera = n.id_nevera ORDER BY i.fecha_hora DESC LIMIT 1) AS ultima_medicion")
            ->where('n.id_cliente', $idCliente)
            ->orderBy('n.activo', 'DESC')
            ->orderBy('n.nombre', 'ASC');

        if ($soloActivas) $builder->where('n.activo', 1);

        return $builder->get()->getResultArray();
    }
}

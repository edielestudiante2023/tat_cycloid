<?php
namespace App\Models;

use CodeIgniter\Model;

class InspeccionNeveraModel extends Model
{
    protected $table         = 'tbl_inspeccion_nevera';
    protected $primaryKey    = 'id';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    protected $allowedFields = [
        'id_cliente', 'id_nevera', 'fecha_hora',
        'temperatura', 'humedad_relativa',
        'foto_temperatura', 'foto_humedad', 'observaciones',
        'dentro_rango', 'id_reporte', 'registrado_por',
    ];

    /**
     * Histórico de un nevera ordenado por fecha_hora DESC.
     */
    public function historicoPorNevera(int $idNevera, int $limit = 100): array
    {
        return $this->where('id_nevera', $idNevera)
                    ->orderBy('fecha_hora', 'DESC')
                    ->findAll($limit);
    }

    /**
     * Mediciones de un cliente en un rango de fechas.
     */
    public function porCliente(int $idCliente, ?string $desde = null, ?string $hasta = null): array
    {
        $b = $this->where('id_cliente', $idCliente);
        if ($desde) $b->where('fecha_hora >=', $desde);
        if ($hasta) $b->where('fecha_hora <=', $hasta);
        return $b->orderBy('fecha_hora', 'DESC')->findAll();
    }

    /**
     * ¿Una medición está dentro del rango esperado de la nevera?
     */
    public static function calcularDentroRango(array $nevera, float $temperatura, ?float $humedad): int
    {
        $tempOk = ($temperatura >= (float)$nevera['rango_temp_min'])
               && ($temperatura <= (float)$nevera['rango_temp_max']);

        $humOk = true;
        if (!empty($nevera['controla_humedad']) && $humedad !== null) {
            $hMin = (float)($nevera['rango_humedad_min'] ?? 0);
            $hMax = (float)($nevera['rango_humedad_max'] ?? 100);
            $humOk = ($humedad >= $hMin) && ($humedad <= $hMax);
        }
        return ($tempOk && $humOk) ? 1 : 0;
    }
}

<?php
namespace App\Models;

use CodeIgniter\Model;

class RecepcionMpModel extends Model
{
    protected $table         = 'tbl_recepcion_mp';
    protected $primaryKey    = 'id';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    protected $allowedFields = [
        'id_cliente', 'id_proveedor', 'proveedor_nombre', 'fecha_hora',
        'producto', 'categoria', 'cantidad', 'unidad', 'numero_factura',
        'fecha_vencimiento_producto', 'temperatura_recepcion',
        'registro_sanitario', 'lote',
        'empaque_ok', 'producto_ok', 'aceptado', 'motivo_rechazo',
        'foto_producto', 'foto_factura', 'foto_temperatura',
        'observaciones', 'id_reporte', 'registrado_por',
    ];

    public const CATEGORIAS = [
        'carnicos'         => 'Cárnicos',
        'lacteos'          => 'Lácteos',
        'frutas_verduras'  => 'Frutas y verduras',
        'panaderia'        => 'Panadería',
        'empacados'        => 'Empacados / Secos',
        'bebidas'          => 'Bebidas',
        'congelados'       => 'Congelados',
        'otros'            => 'Otros',
    ];

    /**
     * Categorías donde la temperatura de recepción es crítica.
     */
    public const CATEGORIAS_CON_TEMPERATURA = ['carnicos','lacteos','congelados'];

    /**
     * Listado por cliente con datos de proveedor (JOIN left).
     */
    public function listarPorCliente(int $idCliente, ?string $desde = null, ?string $hasta = null): array
    {
        $b = $this->db->table('tbl_recepcion_mp r')
            ->select('r.*, p.nombre AS proveedor_nombre_cat')
            ->join('tbl_proveedor p', 'p.id_proveedor = r.id_proveedor', 'left')
            ->where('r.id_cliente', $idCliente)
            ->orderBy('r.fecha_hora', 'DESC');

        if ($desde) $b->where('r.fecha_hora >=', $desde);
        if ($hasta) $b->where('r.fecha_hora <=', $hasta);

        return $b->get()->getResultArray();
    }
}

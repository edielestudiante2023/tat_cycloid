<?php
namespace App\Models;

use CodeIgniter\Model;

class ProveedorModel extends Model
{
    protected $table         = 'tbl_proveedor';
    protected $primaryKey    = 'id_proveedor';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    protected $allowedFields = [
        'id_cliente', 'nombre', 'nit', 'telefono', 'direccion',
        'categoria_principal', 'activo',
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

    public function listarPorCliente(int $idCliente, bool $soloActivos = true): array
    {
        $b = $this->where('id_cliente', $idCliente)
                  ->orderBy('activo', 'DESC')
                  ->orderBy('nombre', 'ASC');
        if ($soloActivos) $b->where('activo', 1);
        return $b->findAll();
    }
}

<?php
namespace App\Models;

use CodeIgniter\Model;

class EquipoItemModel extends Model
{
    protected $table         = 'tbl_equipos_item';
    protected $primaryKey    = 'id_item';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    protected $allowedFields = ['nombre', 'descripcion', 'icono', 'orden', 'activo'];

    protected string $tablaPivot = 'tbl_equipos_item_cliente';

    /**
     * Items aplicables a un cliente (según asignaciones en la pivot).
     */
    public function itemsParaCliente(int $idCliente, bool $soloActivos = true): array
    {
        $b = $this->db->table("{$this->table} i")
            ->select('i.*')
            ->join("{$this->tablaPivot} p", 'p.id_item = i.id_item', 'inner')
            ->where('p.id_cliente', $idCliente)
            ->orderBy('i.orden', 'ASC')
            ->orderBy('i.nombre', 'ASC');
        if ($soloActivos) $b->where('i.activo', 1);
        return $b->get()->getResultArray();
    }

    /**
     * Listar todo el catálogo maestro (para admin).
     */
    public function listarTodos(): array
    {
        return $this->orderBy('activo', 'DESC')
                    ->orderBy('orden', 'ASC')
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }

    /**
     * IDs de items asignados a un cliente (para pre-marcar checkboxes).
     */
    public function idItemsAsignados(int $idCliente): array
    {
        $rows = $this->db->table($this->tablaPivot)
            ->select('id_item')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getResultArray();
        return array_map('intval', array_column($rows, 'id_item'));
    }

    /**
     * Reemplaza la lista de asignaciones de un cliente (DELETE + INSERT atómico).
     */
    public function reemplazarAsignaciones(int $idCliente, array $idItems): void
    {
        $db = $this->db;
        $db->transStart();
        $db->table($this->tablaPivot)->where('id_cliente', $idCliente)->delete();
        if (!empty($idItems)) {
            $filas = array_map(fn($id) => ['id_item' => (int)$id, 'id_cliente' => $idCliente], array_unique($idItems));
            $db->table($this->tablaPivot)->insertBatch($filas);
        }
        $db->transComplete();
    }
}

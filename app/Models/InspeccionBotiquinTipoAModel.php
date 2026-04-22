<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionBotiquinTipoAModel extends Model
{
    protected $table = 'tbl_inspeccion_botiquin_tipo_a';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_inspeccion', 'ubicacion_botiquin',
        'foto_1', 'foto_2',
        'instalado_pared', 'libre_obstaculos', 'lugar_visible', 'con_senalizacion',
        'tipo_botiquin', 'estado_botiquin',
        'recomendaciones', 'pendientes_generados',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_botiquin_tipo_a.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_botiquin_tipo_a.id_cliente', 'left')
            ->where('tbl_inspeccion_botiquin_tipo_a.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_botiquin_tipo_a.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_botiquin_tipo_a.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_botiquin_tipo_a.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_botiquin_tipo_a.id_cliente', 'left')
            ->where('tbl_inspeccion_botiquin_tipo_a.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_botiquin_tipo_a.estado', 'borrador')
            ->orderBy('tbl_inspeccion_botiquin_tipo_a.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_inspeccion_botiquin_tipo_a.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_botiquin_tipo_a.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_botiquin_tipo_a.id_consultor', 'left')
            ->where('tbl_inspeccion_botiquin_tipo_a.estado', 'borrador')
            ->orderBy('tbl_inspeccion_botiquin_tipo_a.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_botiquin_tipo_a.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_botiquin_tipo_a.id_consultor', 'left')
            ->where('tbl_inspeccion_botiquin_tipo_a.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_botiquin_tipo_a.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}

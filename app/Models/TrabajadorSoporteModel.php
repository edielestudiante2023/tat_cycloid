<?php
namespace App\Models;

use CodeIgniter\Model;

class TrabajadorSoporteModel extends Model
{
    protected $table         = 'tbl_trabajador_soporte';
    protected $primaryKey    = 'id';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'id_trabajador',
        'tipo_soporte',
        'archivo',
        'fecha_expedicion',
        'fecha_vencimiento',
        'id_reporte',
    ];

    public const TIPOS = [
        'datos'                  => 'Datos del Trabajador',
        'afiliacion_salud'       => 'Afiliación a Salud',
        'manipulacion_alimentos' => 'Certificado Manipulación de Alimentos',
        'dotacion_epp'           => 'Dotación / EPP Manipulador',
    ];

    public function listarPorTrabajador(int $idTrabajador): array
    {
        return $this->where('id_trabajador', $idTrabajador)
                    ->orderBy('tipo_soporte', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Agrupa los soportes de un trabajador por tipo_soporte para renderizar
     * las 4 secciones fijas del formulario.
     */
    public function agrupadosPorTipo(int $idTrabajador): array
    {
        $rows = $this->listarPorTrabajador($idTrabajador);
        $out = array_fill_keys(array_keys(self::TIPOS), []);
        foreach ($rows as $r) {
            $out[$r['tipo_soporte']][] = $r;
        }
        return $out;
    }
}

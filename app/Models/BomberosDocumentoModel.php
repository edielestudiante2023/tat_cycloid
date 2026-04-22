<?php
namespace App\Models;

use CodeIgniter\Model;

class BomberosDocumentoModel extends Model
{
    protected $table         = 'tbl_bomberos_documento';
    protected $primaryKey    = 'id';
    protected $useAutoIncrement = true;
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'id_solicitud', 'tipo_doc', 'archivo', 'id_reporte', 'observaciones',
    ];

    /**
     * Tipos obligatorios para todos los establecimientos (5).
     */
    public const OBLIGATORIOS = [
        'cedula_rl'       => 'Cédula Representante Legal',
        'recibo_predial'  => 'Recibo Predial',
        'camara_comercio' => 'Cámara de Comercio',
        'rut'             => 'RUT',
        'uso_suelo'       => 'Concepto de Uso de Suelo',
    ];

    /**
     * Condicional: aplica si tipo_establecimiento.aplica_bomberos_docs_extra = 1.
     */
    public const CONDICIONALES = [
        'respuesta_gestion_riesgo' => 'Respuesta Oficina Gestión del Riesgo',
    ];

    /**
     * Documentos que emite bomberos después de radicar.
     */
    public const RESPUESTA = [
        'concepto_bomberos' => 'Concepto Bomberos',
        'otro'              => 'Documento Bomberos Adicional',
    ];

    public static function etiqueta(string $tipoDoc): string
    {
        return self::OBLIGATORIOS[$tipoDoc]
            ?? self::CONDICIONALES[$tipoDoc]
            ?? self::RESPUESTA[$tipoDoc]
            ?? $tipoDoc;
    }

    public function agrupadosPorTipo(int $idSolicitud): array
    {
        $rows = $this->where('id_solicitud', $idSolicitud)
                     ->orderBy('created_at', 'DESC')
                     ->findAll();
        $all = array_merge(self::OBLIGATORIOS, self::CONDICIONALES, self::RESPUESTA);
        $out = array_fill_keys(array_keys($all), []);
        foreach ($rows as $r) {
            $out[$r['tipo_doc']][] = $r;
        }
        return $out;
    }

    public function tieneDoc(int $idSolicitud, string $tipoDoc): bool
    {
        return $this->where('id_solicitud', $idSolicitud)
                    ->where('tipo_doc', $tipoDoc)
                    ->countAllResults() > 0;
    }
}

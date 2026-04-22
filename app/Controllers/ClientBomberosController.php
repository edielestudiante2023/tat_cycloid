<?php

namespace App\Controllers;

use App\Models\BomberosSolicitudModel;
use App\Models\BomberosDocumentoModel;
use App\Models\ClientModel;
use App\Models\ReporteModel;
use App\Libraries\AnulacionHelper;
use CodeIgniter\Controller;

/**
 * ClientBomberosController - TAT Fase 4.2
 *
 * Expediente de permisos de bomberos por cliente y año, con checklist
 * de completitud y subida de documentos. Integración automática con reportlist.
 *
 * Acceso: cliente (tendero) o consultor con viewing_client_id en sesión.
 */
class ClientBomberosController extends Controller
{
    private const URL_PORTAL_SOACHA = 'https://www.alcaldiasoacha.gov.co/AtencionalCiudadano/Paginas/Visita-Bomberos.aspx';

    /* ============================================================
     * SELECTOR DE CLIENTE (consultor)
     * ============================================================ */
    public function seleccionarCliente()
    {
        $session = session();
        if (!in_array($session->get('role'), ['consultant','admin'], true)) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }
        $clients = (new ClientModel())->orderBy('nombre_cliente','ASC')->findAll();
        return view('consultant/bomberos/seleccionar_cliente', ['clients' => $clients]);
    }

    /* ============================================================
     * EXPEDIENTE del cliente (índice — listado de años)
     * ============================================================ */
    public function index(?int $idCliente = null)
    {
        $clientId = $this->resolveClientId($idCliente);
        if (!$clientId) return redirect()->to('/login')->with('error','Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        if (!$client) return redirect()->to('/login')->with('error','Cliente no encontrado.');

        $solModel = new BomberosSolicitudModel();
        $anios = $solModel->aniosDeCliente($clientId);

        // Si no hay solicitud aún, crear la del año actual
        if (empty($anios)) {
            $actual = (int)date('Y');
            $solModel->getOrCreate($clientId, $actual);
            $anios = $solModel->aniosDeCliente($clientId);
        }

        return view('client/bomberos/list', [
            'client' => $client,
            'anios'  => $anios,
        ]);
    }

    /* ============================================================
     * EXPEDIENTE (detalle de un año)
     * ============================================================ */
    public function expediente(int $idSolicitud)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $solModel = new BomberosSolicitudModel();
        $sol = $solModel->find($idSolicitud);

        if (!$sol || (int)$sol['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/bomberos'))
                ->with('error', 'Solicitud no encontrada.');
        }

        $client = (new ClientModel())->find($clientId);

        // ¿Requiere doc condicional (Gestión del Riesgo)?
        $requiereExtra = false;
        if (!empty($client['id_tipo_establecimiento'])) {
            $te = \Config\Database::connect()
                ->table('tbl_tipo_establecimiento')
                ->where('id_tipo_establecimiento', $client['id_tipo_establecimiento'])
                ->get()->getRowArray();
            $requiereExtra = !empty($te['aplica_bomberos_docs_extra']);
        }

        $docModel = new BomberosDocumentoModel();
        $docs = $docModel->agrupadosPorTipo($idSolicitud);

        // Calcular completitud
        $obligatorios = array_keys(BomberosDocumentoModel::OBLIGATORIOS);
        if ($requiereExtra) {
            $obligatorios = array_merge($obligatorios, array_keys(BomberosDocumentoModel::CONDICIONALES));
        }
        $cumplidos = 0;
        foreach ($obligatorios as $tipo) {
            if (!empty($docs[$tipo])) $cumplidos++;
        }
        $total = count($obligatorios);
        $completitud = $total > 0 ? (int) round(($cumplidos / $total) * 100) : 0;

        // Municipios para dropdown
        $departamentos = \Config\Database::connect()
            ->table('tbl_municipios_colombia')
            ->select('departamento')
            ->distinct()
            ->orderBy('departamento','ASC')
            ->get()->getResultArray();

        $municipiosDelDept = \Config\Database::connect()
            ->table('tbl_municipios_colombia')
            ->where('departamento', $sol['departamento'])
            ->orderBy('municipio','ASC')
            ->get()->getResultArray();

        return view('client/bomberos/expediente', [
            'client'            => $client,
            'solicitud'         => $sol,
            'docs'              => $docs,
            'requiereExtra'     => $requiereExtra,
            'completitud'       => $completitud,
            'cumplidos'         => $cumplidos,
            'totalRequeridos'   => $total,
            'urlPortal'         => self::URL_PORTAL_SOACHA,
            'departamentos'     => $departamentos,
            'municipiosDelDept' => $municipiosDelDept,
            'obligatorios'      => BomberosDocumentoModel::OBLIGATORIOS,
            'condicionales'     => BomberosDocumentoModel::CONDICIONALES,
            'respuesta'         => BomberosDocumentoModel::RESPUESTA,
        ]);
    }

    /* ============================================================
     * Crear solicitud para un nuevo año
     * ============================================================ */
    public function nuevoAnio()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $anio = (int)$this->request->getPost('anio');
        if ($anio < 2020 || $anio > 2100) {
            return redirect()->back()->with('error', 'Año inválido.');
        }

        $sol = (new BomberosSolicitudModel())->getOrCreate($clientId, $anio);

        return redirect()->to(base_url('client/bomberos/expediente/' . $sol['id']))
            ->with('msg', 'Expediente ' . $anio . ' listo.');
    }

    /* ============================================================
     * Actualizar encabezado (municipio, estado, radicación)
     * ============================================================ */
    public function actualizarEncabezado(int $idSolicitud)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $solModel = new BomberosSolicitudModel();
        $sol = $solModel->find($idSolicitud);
        if (!$sol || (int)$sol['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/bomberos'))
                ->with('error', 'Solicitud no encontrada.');
        }

        $data = [
            'departamento'      => $this->request->getPost('departamento') ?: 'Cundinamarca',
            'municipio'         => $this->request->getPost('municipio') ?: 'Soacha',
            'estado'            => in_array($this->request->getPost('estado'), BomberosSolicitudModel::ESTADOS, true)
                                    ? $this->request->getPost('estado') : $sol['estado'],
            'fecha_radicacion'  => $this->request->getPost('fecha_radicacion') ?: null,
            'numero_radicado'   => $this->request->getPost('numero_radicado') ?: null,
            'observaciones'     => $this->request->getPost('observaciones') ?: null,
        ];

        $solModel->update($idSolicitud, $data);

        return redirect()->to(base_url('client/bomberos/expediente/' . $idSolicitud))
            ->with('msg', 'Datos actualizados.');
    }

    /* ============================================================
     * SUBIR documento
     * ============================================================ */
    public function uploadDocumento(int $idSolicitud)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $solModel = new BomberosSolicitudModel();
        $sol = $solModel->find($idSolicitud);
        if (!$sol || (int)$sol['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/bomberos'))
                ->with('error', 'Solicitud no encontrada.');
        }

        $tipoDoc = $this->request->getPost('tipo_doc');
        $tiposValidos = array_merge(
            array_keys(BomberosDocumentoModel::OBLIGATORIOS),
            array_keys(BomberosDocumentoModel::CONDICIONALES),
            array_keys(BomberosDocumentoModel::RESPUESTA)
        );
        if (!in_array($tipoDoc, $tiposValidos, true)) {
            return redirect()->back()->with('error', 'Tipo de documento inválido.');
        }

        $file = $this->request->getFile('archivo');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'Archivo inválido.');
        }

        $allowed = ['pdf','jpg','jpeg','png','webp'];
        if (!in_array(strtolower($file->getExtension()), $allowed, true)) {
            return redirect()->back()->with('error', 'Formato no permitido. Solo PDF/imágenes.');
        }

        $uploadPath = ROOTPATH . 'public/uploads';
        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);
        compress_uploaded_image($uploadPath . '/' . $newName);

        $idReporte = $this->registrarEnReportlist($clientId, $sol, $tipoDoc, $newName);

        (new BomberosDocumentoModel())->insert([
            'id_solicitud'  => $idSolicitud,
            'tipo_doc'      => $tipoDoc,
            'archivo'       => $newName,
            'id_reporte'    => $idReporte,
            'observaciones' => $this->request->getPost('observaciones') ?: null,
        ]);

        return redirect()->to(base_url('client/bomberos/expediente/' . $idSolicitud))
            ->with('msg', 'Documento cargado.');
    }

    /* ============================================================
     * ELIMINAR documento
     * ============================================================ */
    public function deleteDocumento(int $idSolicitud, int $idDoc)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $sol = (new BomberosSolicitudModel())->find($idSolicitud);
        if (!$sol || (int)$sol['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/bomberos'))->with('error','No autorizado.');
        }

        $doc = (new BomberosDocumentoModel())->find($idDoc);
        if (!$doc || (int)$doc['id_solicitud'] !== (int)$idSolicitud) {
            return redirect()->back()->with('error','Documento no encontrado.');
        }

        $justificacion = (string)$this->request->getPost('justificacion');
        $desc = 'Documento bomberos "' . ($doc['tipo_documento'] ?? '—') . '" del expediente #' . $idSolicitud;
        $r = AnulacionHelper::crearSolicitud($clientId, 'bomberos-doc', $idDoc, $idSolicitud, $justificacion, $desc);

        if (!$r['success']) return redirect()->back()->with('error', $r['error']);
        return redirect()->to(base_url('client/bomberos/expediente/' . $idSolicitud))->with('msg', $r['flash_msg']);
    }

    /* ============================================================
     * AJAX: lista de municipios para un departamento
     * ============================================================ */
    public function municipiosPorDepartamento()
    {
        $depto = $this->request->getGet('departamento') ?? '';
        $rows = \Config\Database::connect()
            ->table('tbl_municipios_colombia')
            ->where('departamento', $depto)
            ->orderBy('municipio','ASC')
            ->get()->getResultArray();
        return $this->response->setJSON($rows);
    }

    /* ============================================================
     * HELPERS
     * ============================================================ */
    private function resolveClientId(?int $idCliente = null): ?int
    {
        $session = session();
        $role    = $session->get('role');

        if (in_array($role, ['consultant','admin'], true)) {
            if ($idCliente) {
                $session->set('viewing_client_id', $idCliente);
                return $idCliente;
            }
            $v = $session->get('viewing_client_id');
            if ($v) return (int)$v;
        }
        if ($role === 'client') {
            return (int)$session->get('user_id');
        }
        return null;
    }

    private function registrarEnReportlist(int $clientId, array $sol, string $tipoDoc, string $archivo): ?int
    {
        $db = \Config\Database::connect();
        $rt = $db->table('report_type_table')->where('report_type','Permisos Bomberos')->get()->getRowArray();
        if (!$rt) return null;

        $labelsDetail = [
            'cedula_rl'               => 'Cédula Representante Legal',
            'recibo_predial'          => 'Recibo Predial',
            'camara_comercio'         => 'Cámara de Comercio',
            'rut'                     => 'RUT',
            'uso_suelo'               => 'Concepto de Uso de Suelo',
            'respuesta_gestion_riesgo'=> 'Respuesta Oficina Gestión del Riesgo',
            'concepto_bomberos'       => 'Concepto Bomberos',
            'otro'                    => 'Documento Bomberos Adicional',
        ];
        $dr = $db->table('detail_report')->where('detail_report', $labelsDetail[$tipoDoc] ?? '')->get()->getRowArray();
        if (!$dr) return null;

        $titulo = ($labelsDetail[$tipoDoc] ?? $tipoDoc) . ' — Bomberos ' . ($sol['anio'] ?? '');
        $idConsultor = (session()->get('role') === 'consultant') ? (int)session()->get('user_id') : null;

        $reporteModel = new ReporteModel();
        // tag debe ser único (idx_tag UNIQUE en tbl_reporte).
        // Usamos prefijo identificable + uniqid() para poder filtrar con LIKE 'bomberos%'.
        $tag = 'bomberos-' . $clientId . '-' . ($sol['id'] ?? 0) . '-' . uniqid();
        $reporteModel->insert([
            'titulo_reporte'  => mb_substr($titulo, 0, 255),
            'id_detailreport' => $dr['id_detailreport'],
            'enlace'          => base_url('uploads/' . $archivo),
            'estado'          => 'CERRADO',
            'observaciones'   => 'Documento Bomberos cargado desde expediente.',
            'id_cliente'      => $clientId,
            'id_consultor'    => $idConsultor,
            'id_report_type'  => $rt['id_report_type'],
            'report_url'      => base_url('uploads/' . $archivo),
            'tag'             => $tag,
        ]);
        return (int)$reporteModel->getInsertID();
    }
}

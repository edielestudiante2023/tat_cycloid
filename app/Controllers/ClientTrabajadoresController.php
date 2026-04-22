<?php

namespace App\Controllers;

use App\Models\TrabajadorModel;
use App\Models\TrabajadorSoporteModel;
use App\Models\ClientModel;
use App\Models\ReporteModel;
use App\Libraries\AnulacionHelper;
use CodeIgniter\Controller;

/**
 * ClientTrabajadoresController - TAT Fase 4.1
 *
 * CRUD de trabajadores del tendero + soportes (PDF/imagen) con integración
 * automática al reportlist (tbl_reporte).
 *
 * Acceso: cliente (tendero) o consultor con viewing_client_id en sesión.
 * Rutas: bajo grupo /client/trabajadores/...
 */
class ClientTrabajadoresController extends Controller
{
    /* ============================================================
     * SELECTOR DE CLIENTE (consultor)
     * Punto de entrada cuando el consultor accede desde el menú principal
     * sin haber seleccionado un cliente aún.
     * ============================================================ */
    public function seleccionarCliente()
    {
        $session = session();
        $role = $session->get('role');

        if (!in_array($role, ['consultant', 'admin'], true)) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clients = (new ClientModel())->orderBy('nombre_cliente', 'ASC')->findAll();

        return view('consultant/trabajadores/seleccionar_cliente', [
            'clients' => $clients,
        ]);
    }

    /* ============================================================
     * LISTADO
     * ============================================================ */
    public function index(?int $idCliente = null)
    {
        $clientId = $this->resolveClientId($idCliente);
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $client = (new ClientModel())->find($clientId);
        if (!$client) {
            return redirect()->to('/login')->with('error', 'Cliente no encontrado.');
        }

        $trabajadores = (new TrabajadorModel())->listarPorCliente($clientId, false);

        return view('client/trabajadores/list', [
            'client'       => $client,
            'trabajadores' => $trabajadores,
        ]);
    }

    /* ============================================================
     * CREAR
     * ============================================================ */
    public function add()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        return view('client/trabajadores/form', [
            'client'     => (new ClientModel())->find($clientId),
            'trabajador' => null,
        ]);
    }

    public function addPost()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $data = $this->collectPostData($clientId);

        if (empty($data['nombre']) || empty($data['numero_id'])) {
            return redirect()->back()->withInput()
                ->with('error', 'Nombre y número de identificación son obligatorios.');
        }

        (new TrabajadorModel())->insert($data);

        return redirect()->to(base_url('client/trabajadores'))
            ->with('msg', 'Trabajador registrado exitosamente.');
    }

    /* ============================================================
     * EDITAR
     * ============================================================ */
    public function edit(int $idTrabajador)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $trabModel = new TrabajadorModel();
        $trabajador = $trabModel->find($idTrabajador);

        if (!$trabajador || (int)$trabajador['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/trabajadores'))
                ->with('error', 'Trabajador no encontrado.');
        }

        return view('client/trabajadores/form', [
            'client'     => (new ClientModel())->find($clientId),
            'trabajador' => $trabajador,
        ]);
    }

    public function updatePost(int $idTrabajador)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $trabModel = new TrabajadorModel();
        $existing = $trabModel->find($idTrabajador);

        if (!$existing || (int)$existing['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/trabajadores'))
                ->with('error', 'Trabajador no encontrado.');
        }

        $data = $this->collectPostData($clientId);
        unset($data['id_cliente']); // no se cambia al editar
        $trabModel->update($idTrabajador, $data);

        return redirect()->to(base_url('client/trabajadores/' . $idTrabajador . '/soportes'))
            ->with('msg', 'Trabajador actualizado.');
    }

    /* ============================================================
     * ELIMINAR
     * ============================================================ */
    public function delete(int $idTrabajador)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $existing = (new TrabajadorModel())->find($idTrabajador);
        if (!$existing || (int)$existing['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/trabajadores'))
                ->with('error', 'Trabajador no encontrado.');
        }

        $justificacion = (string)$this->request->getPost('justificacion');
        $desc = 'Trabajador: ' . ($existing['nombre_completo'] ?? '—');
        $r = AnulacionHelper::crearSolicitud($clientId, 'trabajador', $idTrabajador, null, $justificacion, $desc);

        if (!$r['success']) return redirect()->back()->with('error', $r['error']);
        return redirect()->to(base_url('client/trabajadores'))->with('msg', $r['flash_msg']);
    }

    /* ============================================================
     * SOPORTES - listar + subir + eliminar
     * ============================================================ */
    public function soportes(int $idTrabajador)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $trabModel = new TrabajadorModel();
        $trabajador = $trabModel->find($idTrabajador);

        if (!$trabajador || (int)$trabajador['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/trabajadores'))
                ->with('error', 'Trabajador no encontrado.');
        }

        $soportesAgrupados = (new TrabajadorSoporteModel())->agrupadosPorTipo($idTrabajador);

        return view('client/trabajadores/soportes', [
            'client'            => (new ClientModel())->find($clientId),
            'trabajador'        => $trabajador,
            'soportesAgrupados' => $soportesAgrupados,
            'tipos'             => TrabajadorSoporteModel::TIPOS,
        ]);
    }

    public function uploadSoporte(int $idTrabajador)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $trabModel = new TrabajadorModel();
        $trabajador = $trabModel->find($idTrabajador);
        if (!$trabajador || (int)$trabajador['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/trabajadores'))
                ->with('error', 'Trabajador no encontrado.');
        }

        $tipoSoporte = $this->request->getPost('tipo_soporte');
        if (!array_key_exists($tipoSoporte, TrabajadorSoporteModel::TIPOS)) {
            return redirect()->back()->with('error', 'Tipo de soporte inválido.');
        }

        $file = $this->request->getFile('archivo');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'Archivo inválido o no recibido.');
        }

        // Solo PDF e imágenes (decisión del usuario en Fase 3)
        $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, $allowedExt, true)) {
            return redirect()->back()->with('error', 'Formato no permitido. Solo PDF e imágenes.');
        }

        $uploadPath = ROOTPATH . 'public/uploads';
        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);
        compress_uploaded_image($uploadPath . '/' . $newName);

        // Fecha de vencimiento solo válida para manipulación de alimentos
        $fechaVencimiento = null;
        if ($tipoSoporte === 'manipulacion_alimentos') {
            $fechaVencimiento = $this->request->getPost('fecha_vencimiento') ?: null;
        }
        $fechaExpedicion = $this->request->getPost('fecha_expedicion') ?: null;

        // 1) Insertar en tbl_reporte (integración reportlist)
        $idReporte = $this->registrarEnReportlist(
            (int)$clientId,
            $trabajador,
            $tipoSoporte,
            $newName
        );

        // 2) Insertar en tbl_trabajador_soporte con link al reporte
        (new TrabajadorSoporteModel())->insert([
            'id_trabajador'     => $idTrabajador,
            'tipo_soporte'      => $tipoSoporte,
            'archivo'           => $newName,
            'fecha_expedicion'  => $fechaExpedicion,
            'fecha_vencimiento' => $fechaVencimiento,
            'id_reporte'        => $idReporte,
        ]);

        return redirect()->to(base_url('client/trabajadores/' . $idTrabajador . '/soportes'))
            ->with('msg', 'Soporte cargado.');
    }

    public function deleteSoporte(int $idTrabajador, int $idSoporte)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $trabajador = (new TrabajadorModel())->find($idTrabajador);
        if (!$trabajador || (int)$trabajador['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/trabajadores'))
                ->with('error', 'Trabajador no encontrado.');
        }

        $soporte = (new TrabajadorSoporteModel())->find($idSoporte);
        if (!$soporte || (int)$soporte['id_trabajador'] !== (int)$idTrabajador) {
            return redirect()->back()->with('error', 'Soporte no encontrado.');
        }

        $justificacion = (string)$this->request->getPost('justificacion');
        $desc = 'Soporte "' . ($soporte['tipo'] ?? '—') . '" del trabajador ' . ($trabajador['nombre_completo'] ?? '');
        $r = AnulacionHelper::crearSolicitud($clientId, 'trabajador-soporte', $idSoporte, $idTrabajador, $justificacion, $desc);

        if (!$r['success']) return redirect()->back()->with('error', $r['error']);
        return redirect()->to(base_url('client/trabajadores/' . $idTrabajador . '/soportes'))->with('msg', $r['flash_msg']);
    }

    /* ============================================================
     * HELPERS
     * ============================================================ */

    /**
     * Resuelve id_cliente según el rol del usuario autenticado.
     *   - client: su propio id (sesión user_id)
     *   - consultant/admin: parámetro si lo pasan, o viewing_client_id de sesión
     */
    private function resolveClientId(?int $idCliente = null): ?int
    {
        $session = session();
        $role    = $session->get('role');

        if (in_array($role, ['consultant', 'admin'], true)) {
            if ($idCliente) {
                $session->set('viewing_client_id', $idCliente);
                return $idCliente;
            }
            $viewingId = $session->get('viewing_client_id');
            if ($viewingId) return (int)$viewingId;
        }

        if ($role === 'client') {
            return (int)$session->get('user_id');
        }

        return null;
    }

    /**
     * Recoge los campos del formulario de trabajador desde el POST.
     */
    private function collectPostData(int $clientId): array
    {
        return [
            'id_cliente'         => $clientId,
            'nombre'             => trim((string)$this->request->getPost('nombre')),
            'tipo_id'            => $this->request->getPost('tipo_id') ?: 'CC',
            'numero_id'          => trim((string)$this->request->getPost('numero_id')),
            'cargo'              => $this->request->getPost('cargo') ?: null,
            'fecha_ingreso'      => $this->request->getPost('fecha_ingreso') ?: null,
            'telefono'           => $this->request->getPost('telefono') ?: null,
            'tipo_contrato'      => $this->request->getPost('tipo_contrato') ?: null,
            'fecha_terminacion'  => $this->request->getPost('fecha_terminacion') ?: null,
            'manipula_alimentos' => $this->request->getPost('manipula_alimentos') ? 1 : 0,
            'activo'             => $this->request->getPost('activo') !== null
                                    ? ($this->request->getPost('activo') ? 1 : 0)
                                    : 1,
        ];
    }

    /**
     * Crea un registro en tbl_reporte (reportlist) para el soporte cargado.
     * Devuelve el id_reporte generado (o null en caso de fallo).
     */
    private function registrarEnReportlist(int $clientId, array $trabajador, string $tipoSoporte, string $archivo): ?int
    {
        $reporteModel = new ReporteModel();

        // Buscar ids de report_type y detail_report correspondientes
        $db = \Config\Database::connect();

        $rt = $db->table('report_type_table')
            ->where('report_type', 'Trabajadores')
            ->get()->getRowArray();

        if (!$rt) return null;

        $detailMap = [
            'datos'                  => 'Datos del Trabajador',
            'afiliacion_salud'       => 'Afiliación a Salud',
            'manipulacion_alimentos' => 'Certificado Manipulación de Alimentos',
            'dotacion_epp'           => 'Dotación / EPP Manipulador',
        ];

        $dr = $db->table('detail_report')
            ->where('detail_report', $detailMap[$tipoSoporte] ?? '')
            ->get()->getRowArray();

        if (!$dr) return null;

        $titulo = ($detailMap[$tipoSoporte] ?? $tipoSoporte)
                . ' — ' . ($trabajador['nombre'] ?? '');

        $idConsultor = (int)(session()->get('role') === 'consultant'
            ? session()->get('user_id')
            : 0);

        // tag debe ser único (idx_tag UNIQUE en tbl_reporte).
        // Usamos prefijo + uniqid() para poder filtrar con LIKE 'trabajadores%'.
        $tag = 'trabajadores-' . $clientId . '-' . ($trabajador['id_trabajador'] ?? 0) . '-' . uniqid();

        $data = [
            'titulo_reporte'  => mb_substr($titulo, 0, 255),
            'id_detailreport' => $dr['id_detailreport'],
            'enlace'          => base_url('uploads/' . $archivo),
            'estado'          => 'CERRADO',
            'observaciones'   => 'Soporte cargado desde módulo Trabajadores.',
            'id_cliente'      => $clientId,
            'id_consultor'    => $idConsultor ?: null,
            'id_report_type'  => $rt['id_report_type'],
            'report_url'      => base_url('uploads/' . $archivo),
            'tag'             => $tag,
        ];

        $reporteModel->insert($data);
        return (int)$reporteModel->getInsertID();
    }
}

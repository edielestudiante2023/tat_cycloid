<?php

namespace App\Controllers;

use App\Models\NeveraModel;
use App\Models\InspeccionNeveraModel;
use App\Models\ClientModel;
use App\Models\ReporteModel;
use App\Libraries\AnulacionHelper;
use CodeIgniter\Controller;

/**
 * ClientNeverasController - TAT Fase 5.1
 *
 * Control diario de temperatura y humedad de las neveras del establecimiento.
 * CRUD primario del tendero (rutas bajo /client/neveras/...).
 * Consultor accede mediante view_as_client (viewing_client_id en sesión).
 */
class ClientNeverasController extends Controller
{
    /* ============================================================
     * Selector de cliente (consultor)
     * ============================================================ */
    public function seleccionarCliente()
    {
        if (!in_array(session()->get('role'), ['consultant','admin'], true)) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }
        $clients = (new ClientModel())->orderBy('nombre_cliente','ASC')->findAll();
        return view('consultant/neveras/seleccionar_cliente', ['clients' => $clients]);
    }

    /* ============================================================
     * Listado de neveras del cliente
     * ============================================================ */
    public function index(?int $idCliente = null)
    {
        $clientId = $this->resolveClientId($idCliente);
        if (!$clientId) return redirect()->to('/login');

        $client = (new ClientModel())->find($clientId);
        if (!$client) return redirect()->to('/login')->with('error','Cliente no encontrado.');

        $neveras = (new NeveraModel())->listarPorCliente($clientId, false);

        return view('client/neveras/list', [
            'client'  => $client,
            'neveras' => $neveras,
        ]);
    }

    /* ============================================================
     * CRUD Neveras
     * ============================================================ */
    public function agregarNevera()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        return view('client/neveras/form_nevera', [
            'client' => (new ClientModel())->find($clientId),
            'nevera' => null,
        ]);
    }

    public function guardarNevera()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $tipo = $this->request->getPost('tipo') ?: 'refrigeracion';
        // Rangos se derivan del tipo (opción A: no se piden al usuario)
        $def = NeveraModel::rangoDefault($tipo);

        $controlaHumedad = $this->request->getPost('controla_humedad') ? 1 : 0;
        // Humedad default estándar para refrigeración de alimentos
        $rangoHumMin = $controlaHumedad ? 40.0 : null;
        $rangoHumMax = $controlaHumedad ? 70.0 : null;

        $fotoEquipo = $this->uploadFoto('foto_equipo');

        $data = [
            'id_cliente'        => $clientId,
            'nombre'            => trim((string)$this->request->getPost('nombre')),
            'tipo'              => $tipo,
            'ubicacion'         => $this->request->getPost('ubicacion') ?: null,
            'foto_equipo'       => $fotoEquipo,
            'rango_temp_min'    => $def['min'],
            'rango_temp_max'    => $def['max'],
            'controla_humedad'  => $controlaHumedad,
            'rango_humedad_min' => $rangoHumMin,
            'rango_humedad_max' => $rangoHumMax,
            'activo'            => 1,
        ];

        if (empty($data['nombre'])) {
            return redirect()->back()->withInput()->with('error','El nombre es obligatorio.');
        }

        (new NeveraModel())->insert($data);
        return redirect()->to(base_url('client/neveras'))->with('msg','Nevera registrada.');
    }

    public function editarNevera(int $idNevera)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $n = (new NeveraModel())->find($idNevera);
        if (!$n || (int)$n['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/neveras'))->with('error','Nevera no encontrada.');
        }

        return view('client/neveras/form_nevera', [
            'client' => (new ClientModel())->find($clientId),
            'nevera' => $n,
        ]);
    }

    public function actualizarNevera(int $idNevera)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $model = new NeveraModel();
        $n = $model->find($idNevera);
        if (!$n || (int)$n['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/neveras'))->with('error','Nevera no encontrada.');
        }

        $tipo = $this->request->getPost('tipo') ?: $n['tipo'];
        $def  = NeveraModel::rangoDefault($tipo);
        $controlaHumedad = $this->request->getPost('controla_humedad') ? 1 : 0;

        $data = [
            'nombre'            => trim((string)$this->request->getPost('nombre')),
            'tipo'              => $tipo,
            'ubicacion'         => $this->request->getPost('ubicacion') ?: null,
            // Rangos se derivan del tipo (opción A) - SIEMPRE se resetean a los defaults regulatorios.
            'rango_temp_min'    => $def['min'],
            'rango_temp_max'    => $def['max'],
            'controla_humedad'  => $controlaHumedad,
            // Humedad también siempre reseteada a 40-70% (estándar alimentos refrigerados)
            'rango_humedad_min' => $controlaHumedad ? 40.0 : null,
            'rango_humedad_max' => $controlaHumedad ? 70.0 : null,
            'activo'            => $this->request->getPost('activo') !== null ? ($this->request->getPost('activo') ? 1 : 0) : (int)$n['activo'],
        ];

        // Nueva foto de equipo (opcional). Si suben otra, borra la anterior
        $nuevaFoto = $this->uploadFoto('foto_equipo');
        if ($nuevaFoto) {
            if (!empty($n['foto_equipo'])) {
                $old = ROOTPATH . 'public/uploads/' . $n['foto_equipo'];
                if (file_exists($old)) @unlink($old);
            }
            $data['foto_equipo'] = $nuevaFoto;
        }

        $model->update($idNevera, $data);
        return redirect()->to(base_url('client/neveras'))->with('msg','Nevera actualizada.');
    }

    public function eliminarNevera(int $idNevera)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $n = (new NeveraModel())->find($idNevera);
        if (!$n || (int)$n['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/neveras'))->with('error','No autorizado.');
        }

        $justificacion = (string)$this->request->getPost('justificacion');
        $desc = 'Nevera "' . ($n['nombre'] ?? '—') . '"';
        $r = AnulacionHelper::crearSolicitud($clientId, 'nevera', $idNevera, null, $justificacion, $desc);

        if (!$r['success']) return redirect()->back()->with('error', $r['error']);
        return redirect()->to(base_url('client/neveras'))->with('msg', $r['flash_msg']);
    }

    /**
     * Sube un archivo opcional a public/uploads, retorna el nombre o null.
     */
    private function uploadFoto(string $inputName): ?string
    {
        $file = $this->request->getFile($inputName);
        if (!$file || !$file->isValid() || $file->hasMoved()) return null;
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) return null;
        $newName = $file->getRandomName();
        $file->move(ROOTPATH . 'public/uploads', $newName);
        compress_uploaded_image(ROOTPATH . 'public/uploads/' . $newName);
        return $newName;
    }

    /* ============================================================
     * Histórico + nueva medición
     * ============================================================ */
    public function historico(int $idNevera)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $nevera = (new NeveraModel())->find($idNevera);
        if (!$nevera || (int)$nevera['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/neveras'))->with('error','Nevera no encontrada.');
        }

        $mediciones = (new InspeccionNeveraModel())->historicoPorNevera($idNevera, 200);

        return view('client/neveras/historico', [
            'client'     => (new ClientModel())->find($clientId),
            'nevera'     => $nevera,
            'mediciones' => $mediciones,
        ]);
    }

    public function nuevaMedicion(int $idNevera)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $nevera = (new NeveraModel())->find($idNevera);
        if (!$nevera || (int)$nevera['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/neveras'))->with('error','Nevera no encontrada.');
        }

        return view('client/neveras/form_medicion', [
            'client' => (new ClientModel())->find($clientId),
            'nevera' => $nevera,
        ]);
    }

    public function guardarMedicion(int $idNevera)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $nevModel = new NeveraModel();
        $nevera = $nevModel->find($idNevera);
        if (!$nevera || (int)$nevera['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/neveras'))->with('error','Nevera no encontrada.');
        }

        $tempRaw = $this->request->getPost('temperatura');
        if ($tempRaw === null || $tempRaw === '') {
            return redirect()->back()->withInput()->with('error','La temperatura es obligatoria.');
        }
        $temperatura = (float)$tempRaw;

        $humedad = null;
        if (!empty($nevera['controla_humedad'])) {
            $hRaw = $this->request->getPost('humedad_relativa');
            $humedad = ($hRaw !== null && $hRaw !== '') ? (float)$hRaw : null;
        }

        // Fecha/hora forzada por el servidor (no editable, para integridad de auditoría)
        $fechaHora = date('Y-m-d H:i:s');

        // Dos fotos independientes (temperatura y humedad)
        $fotoTemp = $this->uploadFoto('foto_temperatura');
        $fotoHum  = !empty($nevera['controla_humedad']) ? $this->uploadFoto('foto_humedad') : null;

        // Validación de evidencia obligatoria
        if (!$fotoTemp) {
            return redirect()->back()->withInput()
                ->with('error', '📸 La foto del termómetro es obligatoria como evidencia.');
        }
        if (!empty($nevera['controla_humedad']) && !$fotoHum) {
            return redirect()->back()->withInput()
                ->with('error', '📸 La foto del higrómetro es obligatoria como evidencia.');
        }

        $dentroRango = InspeccionNeveraModel::calcularDentroRango($nevera, $temperatura, $humedad);

        // Integración reportlist: una entrada por medición si hay al menos una foto
        $idReporte = null;
        $fotoReporte = $fotoTemp ?: $fotoHum;
        if ($fotoReporte) {
            $idReporte = $this->registrarEnReportlist($clientId, $nevera, $fechaHora, $temperatura, $humedad, $fotoReporte, $dentroRango);
        }

        $registradoPor = session()->get('role') === 'consultant' ? 'consultor' : 'cliente';

        (new InspeccionNeveraModel())->insert([
            'id_cliente'       => $clientId,
            'id_nevera'        => $idNevera,
            'fecha_hora'       => $fechaHora,
            'temperatura'      => $temperatura,
            'humedad_relativa' => $humedad,
            'foto_temperatura' => $fotoTemp,
            'foto_humedad'     => $fotoHum,
            'observaciones'    => $this->request->getPost('observaciones') ?: null,
            'dentro_rango'     => $dentroRango,
            'id_reporte'       => $idReporte,
            'registrado_por'   => $registradoPor,
        ]);

        $msg = $dentroRango
            ? 'Medición registrada correctamente.'
            : '⚠️ Medición registrada FUERA DE RANGO. Revise el equipo.';
        return redirect()->to(base_url('client/neveras/' . $idNevera . '/historico'))
            ->with($dentroRango ? 'msg' : 'error', $msg);
    }

    public function eliminarMedicion(int $idNevera, int $idMedicion)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $nev = (new NeveraModel())->find($idNevera);
        if (!$nev || (int)$nev['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/neveras'))->with('error','No autorizado.');
        }

        $m = (new InspeccionNeveraModel())->find($idMedicion);
        if (!$m || (int)$m['id_nevera'] !== (int)$idNevera) {
            return redirect()->back()->with('error','Medición no encontrada.');
        }

        $justificacion = (string)$this->request->getPost('justificacion');
        $desc = 'Medición del ' . ($m['fecha_hora'] ?? '—') . ' — Nevera "' . ($nev['nombre'] ?? '—') . '"';
        $r = AnulacionHelper::crearSolicitud($clientId, 'nevera-medicion', $idMedicion, $idNevera, $justificacion, $desc);

        if (!$r['success']) return redirect()->back()->with('error', $r['error']);
        return redirect()->back()->with('msg', $r['flash_msg']);
    }

    /* ============================================================
     * HELPERS
     * ============================================================ */
    private function resolveClientId(?int $idCliente = null): ?int
    {
        $session = session();
        $role    = $session->get('role');
        if (in_array($role, ['consultant','admin'], true)) {
            if ($idCliente) { $session->set('viewing_client_id', $idCliente); return $idCliente; }
            $v = $session->get('viewing_client_id');
            if ($v) return (int)$v;
        }
        if ($role === 'client') return (int)$session->get('user_id');
        return null;
    }

    private function registrarEnReportlist(int $clientId, array $nevera, string $fechaHora, float $temp, ?float $hum, string $archivo, int $dentroRango): ?int
    {
        $db = \Config\Database::connect();
        $rt = $db->table('report_type_table')->where('report_type','Control Neveras')->get()->getRowArray();
        if (!$rt) return null;
        $dr = $db->table('detail_report')->where('detail_report','Registro Control de Nevera')->get()->getRowArray();
        if (!$dr) return null;

        $estado = $dentroRango ? 'CERRADO' : 'ABIERTO';
        $etiqueta = ($dentroRango ? '' : '⚠️ FUERA RANGO · ') . $nevera['nombre']
                  . ' — ' . number_format($temp, 1) . '°C'
                  . ($hum !== null ? ' · ' . number_format($hum, 1) . '% HR' : '')
                  . ' · ' . date('d/m/Y H:i', strtotime($fechaHora));

        $idConsultor = (session()->get('role') === 'consultant') ? (int)session()->get('user_id') : null;
        $tag = 'neveras-' . $clientId . '-' . $nevera['id_nevera'] . '-' . uniqid();

        $reporteModel = new ReporteModel();
        $reporteModel->insert([
            'titulo_reporte'  => mb_substr($etiqueta, 0, 255),
            'id_detailreport' => $dr['id_detailreport'],
            'enlace'          => base_url('uploads/' . $archivo),
            'estado'          => $estado,
            'observaciones'   => 'Medición control de nevera ' . ($dentroRango ? 'dentro de rango' : 'FUERA de rango esperado'),
            'id_cliente'      => $clientId,
            'id_consultor'    => $idConsultor,
            'id_report_type'  => $rt['id_report_type'],
            'report_url'      => base_url('uploads/' . $archivo),
            'tag'             => $tag,
        ]);
        return (int)$reporteModel->getInsertID();
    }
}

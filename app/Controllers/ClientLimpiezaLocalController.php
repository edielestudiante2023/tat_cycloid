<?php

namespace App\Controllers;

use App\Models\LimpiezaItemModel;
use App\Models\InspeccionLimpiezaLocalModel;
use App\Models\InspeccionLimpiezaDetalleModel;
use App\Models\ClientModel;
use App\Models\ReporteModel;
use App\Libraries\AnulacionHelper;
use CodeIgniter\Controller;

/**
 * ClientLimpiezaLocalController - TAT Fase 5.2
 *
 * Inspección de Aseo del Local — checklist dinámico de items (catálogo gestionado
 * por consultor). CRUD primario del tendero.
 */
class ClientLimpiezaLocalController extends Controller
{
    /* ============================================================
     * Selector de cliente (consultor)
     * ============================================================ */
    public function seleccionarCliente()
    {
        if (!in_array(session()->get('role'), ['consultant','admin'], true)) {
            return redirect()->to('/login')->with('error','Acceso no autorizado.');
        }
        $clients = (new ClientModel())->orderBy('nombre_cliente','ASC')->findAll();
        return view('consultant/limpieza-local/seleccionar_cliente', ['clients' => $clients]);
    }

    /* ============================================================
     * Listado de inspecciones (histórico)
     * ============================================================ */
    public function index(?int $idCliente = null)
    {
        $clientId = $this->resolveClientId($idCliente);
        if (!$clientId) return redirect()->to('/login');

        $client = (new ClientModel())->find($clientId);
        if (!$client) return redirect()->to('/login')->with('error','Cliente no encontrado.');

        $inspecciones = (new InspeccionLimpiezaLocalModel())->listarPorCliente($clientId);

        return view('client/limpieza-local/list', [
            'client'        => $client,
            'inspecciones'  => $inspecciones,
        ]);
    }

    /* ============================================================
     * Nueva inspección (formulario con checklist dinámico)
     * ============================================================ */
    public function crear()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $items = (new LimpiezaItemModel())->itemsParaCliente($clientId, true);
        if (empty($items)) {
            return redirect()->to(base_url('client/limpieza-local'))
                ->with('error', 'No hay items en el catálogo. Contacte al consultor para configurar el checklist.');
        }

        return view('client/limpieza-local/form', [
            'client'    => (new ClientModel())->find($clientId),
            'items'     => $items,
        ]);
    }

    public function guardar()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $items = (new LimpiezaItemModel())->itemsParaCliente($clientId, true);
        $estados = $this->request->getPost('estado') ?? [];
        $observaciones = $this->request->getPost('observaciones') ?? [];

        // Al menos uno marcado
        $marcados = 0;
        $faltanFotos = [];
        foreach ($items as $it) {
            $idItem = (int)$it['id_item'];
            $estado = $estados[$idItem] ?? null;
            if (!$estado) continue;
            $marcados++;

            // Validación: si estado != no_aplica, la foto es obligatoria
            if ($estado !== 'no_aplica') {
                $file = $this->request->getFile("foto_{$idItem}");
                if (!$file || !$file->isValid() || $file->hasMoved()) {
                    $faltanFotos[] = $it['nombre'];
                }
            }
        }
        if ($marcados === 0) {
            return redirect()->back()->with('error', 'Debe marcar el estado de al menos un item.');
        }
        if (!empty($faltanFotos)) {
            $lista = implode(', ', $faltanFotos);
            return redirect()->back()->with('error',
                'Foto obligatoria como evidencia. Falta en: ' . $lista);
        }

        // Fecha/hora forzada por servidor
        $fechaHora = date('Y-m-d H:i:s');
        $registradoPor = session()->get('role') === 'consultant' ? 'consultor' : 'cliente';

        // Crear cabecera
        $headerModel = new InspeccionLimpiezaLocalModel();
        $headerId = $headerModel->insert([
            'id_cliente'              => $clientId,
            'fecha_hora'              => $fechaHora,
            'observaciones_generales' => $this->request->getPost('observaciones_generales') ?: null,
            'resultado_general'       => 'ok', // se recalcula abajo
            'registrado_por'          => $registradoPor,
        ]);

        // Procesar detalles (uno por cada item del catálogo)
        $detModel = new InspeccionLimpiezaDetalleModel();
        $haySucio = false;
        $hayFoto = false;
        $ultimaFoto = null;

        foreach ($items as $it) {
            $idItem = (int)$it['id_item'];
            $estado = $estados[$idItem] ?? null;
            if (!$estado) continue; // no marcado → se omite
            if (!in_array($estado, ['limpio','sucio','no_aplica'], true)) continue;

            // Foto de este item
            $fotoName = $this->uploadFoto("foto_{$idItem}");
            if ($fotoName) { $hayFoto = true; $ultimaFoto = $fotoName; }

            if ($estado === 'sucio') $haySucio = true;

            $detModel->insert([
                'id_inspeccion' => $headerId,
                'id_item'       => $idItem,
                'estado'        => $estado,
                'foto'          => $fotoName,
                'observaciones' => $observaciones[$idItem] ?? null,
            ]);
        }

        // Recalcular resultado
        $resultado = $haySucio ? 'no_conforme' : 'ok';

        // Reportlist si hay foto
        $idReporte = null;
        if ($hayFoto) {
            $idReporte = $this->registrarEnReportlist($clientId, $headerId, $fechaHora, $resultado, $ultimaFoto);
        }

        $headerModel->update($headerId, [
            'resultado_general' => $resultado,
            'id_reporte'        => $idReporte,
        ]);

        $msg = $resultado === 'ok'
            ? '✅ Inspección registrada. Todo en orden.'
            : '⚠️ Inspección registrada con ítems sucios. Revise y tome acción.';
        return redirect()->to(base_url('client/limpieza-local/' . $headerId . '/ver'))
            ->with($resultado === 'ok' ? 'msg' : 'error', $msg);
    }

    /* ============================================================
     * Ver detalle (read-only)
     * ============================================================ */
    public function ver(int $idInspeccion)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $header = (new InspeccionLimpiezaLocalModel())->find($idInspeccion);
        if (!$header || (int)$header['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/limpieza-local'))->with('error','Inspección no encontrada.');
        }
        $detalles = (new InspeccionLimpiezaDetalleModel())->porInspeccion($idInspeccion);

        return view('client/limpieza-local/view', [
            'client'    => (new ClientModel())->find($clientId),
            'header'    => $header,
            'detalles'  => $detalles,
        ]);
    }

    /* ============================================================
     * Eliminar (borra fotos + reportlist + CASCADE detalles)
     * ============================================================ */
    public function eliminar(int $idInspeccion)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $header = (new InspeccionLimpiezaLocalModel())->find($idInspeccion);
        if (!$header || (int)$header['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/limpieza-local'))->with('error','No autorizado.');
        }

        $justificacion = (string)$this->request->getPost('justificacion');
        $desc = 'Inspección de limpieza del ' . ($header['fecha_hora'] ?? $header['creado_en'] ?? '—');
        $r = AnulacionHelper::crearSolicitud($clientId, 'limpieza-local', $idInspeccion, null, $justificacion, $desc);

        if (!$r['success']) return redirect()->back()->with('error', $r['error']);
        return redirect()->to(base_url('client/limpieza-local'))->with('msg', $r['flash_msg']);
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

    private function registrarEnReportlist(int $clientId, int $headerId, string $fechaHora, string $resultado, string $archivo): ?int
    {
        $db = \Config\Database::connect();
        $rt = $db->table('report_type_table')->where('report_type','Inspección de Aseo')->get()->getRowArray();
        if (!$rt) return null;
        $dr = $db->table('detail_report')->where('detail_report','Registro Inspección de Aseo')->get()->getRowArray();
        if (!$dr) return null;

        $estado = $resultado === 'ok' ? 'CERRADO' : 'ABIERTO';
        $titulo = ($resultado === 'ok' ? '✓ Aseo OK' : '⚠ Aseo con observaciones')
                . ' · ' . date('d/m/Y H:i', strtotime($fechaHora));

        $idConsultor = (session()->get('role') === 'consultant') ? (int)session()->get('user_id') : null;
        $tag = 'limpieza-' . $clientId . '-' . $headerId . '-' . uniqid();

        $reporteModel = new ReporteModel();
        $reporteModel->insert([
            'titulo_reporte'  => mb_substr($titulo, 0, 255),
            'id_detailreport' => $dr['id_detailreport'],
            'enlace'          => base_url('uploads/' . $archivo),
            'estado'          => $estado,
            'observaciones'   => 'Inspección de aseo del local — registro #' . $headerId,
            'id_cliente'      => $clientId,
            'id_consultor'    => $idConsultor,
            'id_report_type'  => $rt['id_report_type'],
            'report_url'      => base_url('uploads/' . $archivo),
            'tag'             => $tag,
        ]);
        return (int)$reporteModel->getInsertID();
    }
}

<?php

namespace App\Controllers;

use App\Models\ContaminacionItemModel;
use App\Models\InspeccionContaminacionModel;
use App\Models\InspeccionContaminacionDetalleModel;
use App\Models\ClientModel;
use App\Models\ReporteModel;
use App\Libraries\AnulacionHelper;
use CodeIgniter\Controller;

/**
 * POES 4.2 — Prevención de Contaminación Cruzada (checklist dinámico).
 * Misma estructura que Limpieza/Equipos; estados cumple/no_cumple/no_aplica.
 */
class ClientContaminacionController extends Controller
{
    public function seleccionarCliente()
    {
        if (!in_array(session()->get('role'), ['consultant','admin'], true)) {
            return redirect()->to('/login')->with('error','Acceso no autorizado.');
        }
        $clients = (new ClientModel())->orderBy('nombre_cliente','ASC')->findAll();
        return view('consultant/contaminacion/seleccionar_cliente', ['clients' => $clients]);
    }

    public function index(?int $idCliente = null)
    {
        $clientId = $this->resolveClientId($idCliente);
        if (!$clientId) return redirect()->to('/login');
        $client = (new ClientModel())->find($clientId);
        if (!$client) return redirect()->to('/login')->with('error','Cliente no encontrado.');
        $inspecciones = (new InspeccionContaminacionModel())->listarPorCliente($clientId);
        return view('client/contaminacion/list', ['client'=>$client,'inspecciones'=>$inspecciones]);
    }

    public function crear()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');
        $items = (new ContaminacionItemModel())->itemsParaCliente($clientId, true);
        if (empty($items)) {
            return redirect()->to(base_url('client/contaminacion'))
                ->with('error','No hay items en el catálogo. Contacte al consultor.');
        }
        return view('client/contaminacion/form', [
            'client' => (new ClientModel())->find($clientId),
            'items'  => $items,
        ]);
    }

    public function guardar()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $items = (new ContaminacionItemModel())->itemsParaCliente($clientId, true);
        $estados = $this->request->getPost('estado') ?? [];
        $observaciones = $this->request->getPost('observaciones') ?? [];

        $marcados = 0; $faltanFotos = [];
        foreach ($items as $it) {
            $idItem = (int)$it['id_item'];
            $estado = $estados[$idItem] ?? null;
            if (!$estado) continue;
            $marcados++;
            if ($estado !== 'no_aplica') {
                $file = $this->request->getFile("foto_{$idItem}");
                if (!$file || !$file->isValid() || $file->hasMoved()) $faltanFotos[] = $it['nombre'];
            }
        }
        if ($marcados === 0) return redirect()->back()->with('error','Debe marcar al menos un item.');
        if (!empty($faltanFotos)) {
            return redirect()->back()->with('error','Foto obligatoria. Falta en: ' . implode(', ', $faltanFotos));
        }

        $fechaHora = date('Y-m-d H:i:s');
        $registradoPor = session()->get('role') === 'consultant' ? 'consultor' : 'cliente';

        $headerModel = new InspeccionContaminacionModel();
        $headerId = $headerModel->insert([
            'id_cliente'              => $clientId,
            'fecha_hora'              => $fechaHora,
            'observaciones_generales' => $this->request->getPost('observaciones_generales') ?: null,
            'resultado_general'       => 'ok',
            'registrado_por'          => $registradoPor,
        ]);

        $detModel = new InspeccionContaminacionDetalleModel();
        $hayNoCumple = false; $ultimaFoto = null;
        foreach ($items as $it) {
            $idItem = (int)$it['id_item'];
            $estado = $estados[$idItem] ?? null;
            if (!$estado || !in_array($estado, ['cumple','no_cumple','no_aplica'], true)) continue;
            $fotoName = $this->uploadFoto("foto_{$idItem}");
            if ($fotoName) $ultimaFoto = $fotoName;
            if ($estado === 'no_cumple') $hayNoCumple = true;
            $detModel->insert([
                'id_inspeccion' => $headerId,
                'id_item'       => $idItem,
                'estado'        => $estado,
                'foto'          => $fotoName,
                'observaciones' => $observaciones[$idItem] ?? null,
            ]);
        }

        $resultado = $hayNoCumple ? 'no_conforme' : 'ok';
        $idReporte = null;
        if ($ultimaFoto) {
            $idReporte = $this->registrarEnReportlist($clientId, $headerId, $fechaHora, $resultado, $ultimaFoto);
        }
        $headerModel->update($headerId, ['resultado_general' => $resultado, 'id_reporte' => $idReporte]);

        $msg = $resultado === 'ok'
            ? '✅ Inspección registrada. Todo cumple.'
            : '⚠️ Registrada con incumplimientos. Tome acción.';
        return redirect()->to(base_url('client/contaminacion/' . $headerId . '/ver'))
            ->with($resultado === 'ok' ? 'msg' : 'error', $msg);
    }

    public function ver(int $idInspeccion)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');
        $header = (new InspeccionContaminacionModel())->find($idInspeccion);
        if (!$header || (int)$header['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/contaminacion'))->with('error','No encontrado.');
        }
        $detalles = (new InspeccionContaminacionDetalleModel())->porInspeccion($idInspeccion);
        return view('client/contaminacion/view', [
            'client'   => (new ClientModel())->find($clientId),
            'header'   => $header,
            'detalles' => $detalles,
        ]);
    }

    public function eliminar(int $idInspeccion)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');
        $header = (new InspeccionContaminacionModel())->find($idInspeccion);
        if (!$header || (int)$header['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/contaminacion'))->with('error','No autorizado.');
        }

        $justificacion = (string)$this->request->getPost('justificacion');
        $desc = 'Inspección de contaminación cruzada del ' . ($header['fecha_hora'] ?? $header['creado_en'] ?? '—');
        $r = AnulacionHelper::crearSolicitud($clientId, 'contaminacion', $idInspeccion, null, $justificacion, $desc);

        if (!$r['success']) return redirect()->back()->with('error', $r['error']);
        return redirect()->to(base_url('client/contaminacion'))->with('msg', $r['flash_msg']);
    }

    /* ----- HELPERS ----- */
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
        return $newName;
    }

    private function registrarEnReportlist(int $clientId, int $headerId, string $fechaHora, string $resultado, string $archivo): ?int
    {
        $db = \Config\Database::connect();
        $rt = $db->table('report_type_table')->where('report_type','POES Contaminación Cruzada')->get()->getRowArray();
        if (!$rt) return null;
        $dr = $db->table('detail_report')->where('detail_report','Registro Contaminación Cruzada')->get()->getRowArray();
        if (!$dr) return null;

        $estado = $resultado === 'ok' ? 'CERRADO' : 'ABIERTO';
        $titulo = ($resultado === 'ok' ? '✓ Sin contaminación cruzada' : '⚠ Contaminación cruzada con hallazgos')
                . ' · ' . date('d/m/Y H:i', strtotime($fechaHora));
        $idConsultor = (session()->get('role') === 'consultant') ? (int)session()->get('user_id') : null;
        $tag = 'contaminacion-' . $clientId . '-' . $headerId . '-' . uniqid();

        $r = new ReporteModel();
        $r->insert([
            'titulo_reporte'  => mb_substr($titulo, 0, 255),
            'id_detailreport' => $dr['id_detailreport'],
            'enlace'          => base_url('uploads/' . $archivo),
            'estado'          => $estado,
            'observaciones'   => 'POES 4.2 — Contaminación cruzada registro #' . $headerId,
            'id_cliente'      => $clientId,
            'id_consultor'    => $idConsultor,
            'id_report_type'  => $rt['id_report_type'],
            'report_url'      => base_url('uploads/' . $archivo),
            'tag'             => $tag,
        ]);
        return (int)$r->getInsertID();
    }
}

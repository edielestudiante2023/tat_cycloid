<?php

namespace App\Controllers;

use App\Models\ProveedorModel;
use App\Models\RecepcionMpModel;
use App\Models\ClientModel;
use App\Models\ReporteModel;
use App\Libraries\AnulacionHelper;
use CodeIgniter\Controller;

/**
 * ClientRecepcionMpController - TAT Fase 5.3b
 *
 * POES 4.1 — Control de Materias Primas.
 * Registro por cada entrega de proveedor. Integración con reportlist.
 * Acceso dual: tendero (primario) + consultor via view_as_client.
 */
class ClientRecepcionMpController extends Controller
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
        return view('consultant/recepcion-mp/seleccionar_cliente', ['clients' => $clients]);
    }

    /* ============================================================
     * Listado (histórico de recepciones)
     * ============================================================ */
    public function index(?int $idCliente = null)
    {
        $clientId = $this->resolveClientId($idCliente);
        if (!$clientId) return redirect()->to('/login');

        $client = (new ClientModel())->find($clientId);
        if (!$client) return redirect()->to('/login')->with('error','Cliente no encontrado.');

        $desde = $this->request->getGet('desde') ?: null;
        $hasta = $this->request->getGet('hasta') ?: null;

        $recepciones = (new RecepcionMpModel())->listarPorCliente($clientId, $desde, $hasta);

        return view('client/recepcion-mp/list', [
            'client'      => $client,
            'recepciones' => $recepciones,
            'desde'       => $desde,
            'hasta'       => $hasta,
        ]);
    }

    /* ============================================================
     * Nueva recepción - formulario
     * ============================================================ */
    public function crear()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $proveedores = (new ProveedorModel())->listarPorCliente($clientId, true);

        return view('client/recepcion-mp/form', [
            'client'      => (new ClientModel())->find($clientId),
            'proveedores' => $proveedores,
            'categorias'  => RecepcionMpModel::CATEGORIAS,
            'categoriasConTemp' => RecepcionMpModel::CATEGORIAS_CON_TEMPERATURA,
            'recepcion'   => null,
        ]);
    }

    public function guardar()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $idProveedor = $this->request->getPost('id_proveedor') ?: null;
        $proveedorNombre = $this->request->getPost('proveedor_nombre_libre')
                        ?: $this->request->getPost('proveedor_nombre')
                        ?: '';
        if ($idProveedor) {
            $p = (new ProveedorModel())->find($idProveedor);
            if ($p && (int)$p['id_cliente'] === (int)$clientId) {
                $proveedorNombre = $p['nombre'];
            } else {
                $idProveedor = null;
            }
        }
        if (empty($proveedorNombre)) {
            return redirect()->back()->withInput()->with('error','Debe seleccionar o indicar un proveedor.');
        }

        $producto = trim((string)$this->request->getPost('producto'));
        $categoria = $this->request->getPost('categoria') ?: 'otros';
        if (!array_key_exists($categoria, RecepcionMpModel::CATEGORIAS)) $categoria = 'otros';

        if (empty($producto)) {
            return redirect()->back()->withInput()->with('error','Debe indicar el producto.');
        }

        // Validación fotos
        $fotoProducto = $this->uploadFoto('foto_producto');
        if (!$fotoProducto) {
            return redirect()->back()->withInput()->with('error','📸 Foto del producto es obligatoria.');
        }

        $tempRaw = $this->request->getPost('temperatura_recepcion');
        $temperatura = ($tempRaw !== null && $tempRaw !== '') ? (float)$tempRaw : null;
        $aplicaTemperatura = in_array($categoria, RecepcionMpModel::CATEGORIAS_CON_TEMPERATURA, true);

        $fotoTemperatura = null;
        if ($aplicaTemperatura && $temperatura !== null) {
            $fotoTemperatura = $this->uploadFoto('foto_temperatura');
            if (!$fotoTemperatura) {
                return redirect()->back()->withInput()->with('error','📸 Si hay temperatura registrada, la foto del termómetro es obligatoria.');
            }
        }

        $fotoFactura = $this->uploadFoto('foto_factura'); // opcional

        $aceptado = $this->request->getPost('aceptado') ? 1 : 0;
        $motivoRechazo = $this->request->getPost('motivo_rechazo') ?: null;
        if (!$aceptado && empty($motivoRechazo)) {
            return redirect()->back()->withInput()->with('error','Si el producto es rechazado, debe indicar el motivo.');
        }

        // Fecha/hora forzada por servidor
        $fechaHora = date('Y-m-d H:i:s');
        $registradoPor = session()->get('role') === 'consultant' ? 'consultor' : 'cliente';

        $data = [
            'id_cliente'         => $clientId,
            'id_proveedor'       => $idProveedor ?: null,
            'proveedor_nombre'   => $proveedorNombre,
            'fecha_hora'         => $fechaHora,
            'producto'           => $producto,
            'categoria'          => $categoria,
            'cantidad'           => $this->request->getPost('cantidad') !== '' ? (float)$this->request->getPost('cantidad') : null,
            'unidad'             => $this->request->getPost('unidad') ?: null,
            'numero_factura'     => $this->request->getPost('numero_factura') ?: null,
            'fecha_vencimiento_producto' => $this->request->getPost('fecha_vencimiento_producto') ?: null,
            'temperatura_recepcion' => $aplicaTemperatura ? $temperatura : null,
            'registro_sanitario' => $this->request->getPost('registro_sanitario') ?: null,
            'lote'               => $this->request->getPost('lote') ?: null,
            'empaque_ok'         => $this->request->getPost('empaque_ok') ? 1 : 0,
            'producto_ok'        => $this->request->getPost('producto_ok') ? 1 : 0,
            'aceptado'           => $aceptado,
            'motivo_rechazo'     => $motivoRechazo,
            'foto_producto'      => $fotoProducto,
            'foto_factura'       => $fotoFactura,
            'foto_temperatura'   => $fotoTemperatura,
            'observaciones'      => $this->request->getPost('observaciones') ?: null,
            'registrado_por'     => $registradoPor,
        ];

        $model = new RecepcionMpModel();
        $id = $model->insert($data);

        $idReporte = $this->registrarEnReportlist($clientId, $id, $data, $fotoProducto);
        if ($idReporte) $model->update($id, ['id_reporte' => $idReporte]);

        $msg = $aceptado
            ? '✅ Recepción registrada (aceptada).'
            : '⚠️ Recepción registrada con RECHAZO. Revise el motivo.';
        return redirect()->to(base_url('client/recepcion-mp/' . $id . '/ver'))
            ->with($aceptado ? 'msg' : 'error', $msg);
    }

    /* ============================================================
     * Ver detalle
     * ============================================================ */
    public function ver(int $id)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $r = (new RecepcionMpModel())->find($id);
        if (!$r || (int)$r['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/recepcion-mp'))->with('error','No encontrado.');
        }

        return view('client/recepcion-mp/view', [
            'client'    => (new ClientModel())->find($clientId),
            'recepcion' => $r,
            'categorias'=> RecepcionMpModel::CATEGORIAS,
        ]);
    }

    /* ============================================================
     * Eliminar
     * ============================================================ */
    public function eliminar(int $id)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $r = (new RecepcionMpModel())->find($id);
        if (!$r || (int)$r['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/recepcion-mp'))->with('error','No autorizado.');
        }

        $justificacion = (string)$this->request->getPost('justificacion');
        $desc = 'Recepción MP del ' . ($r['fecha_hora'] ?? '—');
        $res = AnulacionHelper::crearSolicitud($clientId, 'recepcion-mp', $id, null, $justificacion, $desc);

        if (!$res['success']) return redirect()->back()->with('error', $res['error']);
        return redirect()->to(base_url('client/recepcion-mp'))->with('msg', $res['flash_msg']);
    }

    /* ============================================================
     * CRUD Proveedores
     * ============================================================ */
    public function proveedores()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $proveedores = (new ProveedorModel())->listarPorCliente($clientId, false);
        return view('client/recepcion-mp/proveedores', [
            'client'      => (new ClientModel())->find($clientId),
            'proveedores' => $proveedores,
            'categorias'  => ProveedorModel::CATEGORIAS,
        ]);
    }

    public function guardarProveedor()
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $nombre = trim((string)$this->request->getPost('nombre'));
        if (empty($nombre)) {
            return redirect()->back()->with('error','El nombre del proveedor es obligatorio.');
        }

        (new ProveedorModel())->insert([
            'id_cliente'          => $clientId,
            'nombre'              => $nombre,
            'nit'                 => $this->request->getPost('nit') ?: null,
            'telefono'            => $this->request->getPost('telefono') ?: null,
            'direccion'           => $this->request->getPost('direccion') ?: null,
            'categoria_principal' => $this->request->getPost('categoria_principal') ?: null,
            'activo'              => 1,
        ]);
        return redirect()->to(base_url('client/recepcion-mp/proveedores'))->with('msg','Proveedor agregado.');
    }

    public function actualizarProveedor(int $idProveedor)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $model = new ProveedorModel();
        $p = $model->find($idProveedor);
        if (!$p || (int)$p['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/recepcion-mp/proveedores'))->with('error','No autorizado.');
        }

        $model->update($idProveedor, [
            'nombre'              => trim((string)$this->request->getPost('nombre')),
            'nit'                 => $this->request->getPost('nit') ?: null,
            'telefono'            => $this->request->getPost('telefono') ?: null,
            'direccion'           => $this->request->getPost('direccion') ?: null,
            'categoria_principal' => $this->request->getPost('categoria_principal') ?: null,
            'activo'              => $this->request->getPost('activo') ? 1 : 0,
        ]);
        return redirect()->to(base_url('client/recepcion-mp/proveedores'))->with('msg','Proveedor actualizado.');
    }

    public function eliminarProveedor(int $idProveedor)
    {
        $clientId = $this->resolveClientId();
        if (!$clientId) return redirect()->to('/login');

        $p = (new ProveedorModel())->find($idProveedor);
        if (!$p || (int)$p['id_cliente'] !== (int)$clientId) {
            return redirect()->to(base_url('client/recepcion-mp/proveedores'))->with('error','No autorizado.');
        }

        $justificacion = (string)$this->request->getPost('justificacion');
        $desc = 'Proveedor: ' . ($p['nombre'] ?? '—');
        $res = AnulacionHelper::crearSolicitud($clientId, 'proveedor', $idProveedor, null, $justificacion, $desc);

        if (!$res['success']) return redirect()->back()->with('error', $res['error']);
        return redirect()->to(base_url('client/recepcion-mp/proveedores'))->with('msg', $res['flash_msg']);
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
        return $newName;
    }

    private function registrarEnReportlist(int $clientId, int $recepcionId, array $data, string $archivo): ?int
    {
        $db = \Config\Database::connect();
        $rt = $db->table('report_type_table')->where('report_type','Recepción de Materias Primas')->get()->getRowArray();
        if (!$rt) return null;

        $detalleLabel = $data['aceptado'] ? 'Registro Recepción MP' : 'Rechazo Materia Prima';
        $dr = $db->table('detail_report')->where('detail_report', $detalleLabel)->get()->getRowArray();
        if (!$dr) return null;

        $estado = $data['aceptado'] ? 'CERRADO' : 'ABIERTO';
        $titulo = ($data['aceptado'] ? '✓ Recepción OK' : '⚠ Rechazo MP')
                . ' · ' . $data['producto']
                . ' · ' . $data['proveedor_nombre']
                . ' · ' . date('d/m/Y H:i', strtotime($data['fecha_hora']));

        $idConsultor = (session()->get('role') === 'consultant') ? (int)session()->get('user_id') : null;
        $tag = 'recmp-' . $clientId . '-' . $recepcionId . '-' . uniqid();

        $obs = 'Recepción MP #' . $recepcionId;
        if (!$data['aceptado'] && !empty($data['motivo_rechazo'])) {
            $obs .= ' | Motivo rechazo: ' . $data['motivo_rechazo'];
        }

        $reporteModel = new ReporteModel();
        $reporteModel->insert([
            'titulo_reporte'  => mb_substr($titulo, 0, 255),
            'id_detailreport' => $dr['id_detailreport'],
            'enlace'          => base_url('uploads/' . $archivo),
            'estado'          => $estado,
            'observaciones'   => mb_substr($obs, 0, 65000),
            'id_cliente'      => $clientId,
            'id_consultor'    => $idConsultor,
            'id_report_type'  => $rt['id_report_type'],
            'report_url'      => base_url('uploads/' . $archivo),
            'tag'             => $tag,
        ]);
        return (int)$reporteModel->getInsertID();
    }
}

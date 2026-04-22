<?php

namespace App\Controllers;

use App\Models\AlmacenamientoItemModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class AlmacenamientoItemController extends Controller
{
    private const SLUG      = 'almacenamiento-items';
    private const TITULO    = 'Items — Almacenamiento';
    private const ICONO     = 'fa-boxes-stacked';
    private const HEADER_BG = 'linear-gradient(135deg,#7c3aed,#a855f7)';

    private function checkAccess(): bool
    {
        return in_array(session()->get('role'), ['consultant','admin'], true);
    }

    public function index()
    {
        if (!$this->checkAccess()) return redirect()->to('/login')->with('error','Acceso no autorizado.');
        $items   = (new AlmacenamientoItemModel())->listarTodos();
        $clients = (new ClientModel())->orderBy('nombre_cliente','ASC')->findAll();
        return view('admin/almacenamiento-items/list', ['items' => $items, 'clients' => $clients]);
    }

    public function agregar()
    {
        if (!$this->checkAccess()) return redirect()->to('/login');
        return view('admin/almacenamiento-items/form', ['item' => null]);
    }

    public function guardar()
    {
        if (!$this->checkAccess()) return redirect()->to('/login');
        $data = $this->collectPost();
        if (empty($data['nombre'])) return redirect()->back()->withInput()->with('error','Nombre obligatorio.');
        (new AlmacenamientoItemModel())->insert($data);
        return redirect()->to(base_url('admin/almacenamiento-items'))->with('msg','Item creado.');
    }

    public function editar(int $id)
    {
        if (!$this->checkAccess()) return redirect()->to('/login');
        $item = (new AlmacenamientoItemModel())->find($id);
        if (!$item) return redirect()->to(base_url('admin/almacenamiento-items'))->with('error','No encontrado.');
        return view('admin/almacenamiento-items/form', ['item' => $item]);
    }

    public function actualizar(int $id)
    {
        if (!$this->checkAccess()) return redirect()->to('/login');
        $model = new AlmacenamientoItemModel();
        if (!$model->find($id)) return redirect()->to(base_url('admin/almacenamiento-items'))->with('error','No encontrado.');
        $model->update($id, $this->collectPost());
        return redirect()->to(base_url('admin/almacenamiento-items'))->with('msg','Item actualizado.');
    }

    public function eliminar(int $id)
    {
        if (!$this->checkAccess()) return redirect()->to('/login');
        (new AlmacenamientoItemModel())->update($id, ['activo' => 0]);
        return redirect()->to(base_url('admin/almacenamiento-items'))->with('msg','Item desactivado (historial preservado).');
    }

    public function activar(int $id)
    {
        if (!$this->checkAccess()) return redirect()->to('/login');
        (new AlmacenamientoItemModel())->update($id, ['activo' => 1]);
        return redirect()->to(base_url('admin/almacenamiento-items'))->with('msg','Item reactivado.');
    }

    public function asignar(int $idCliente)
    {
        if (!$this->checkAccess()) return redirect()->to('/login');
        $cliente = (new ClientModel())->find($idCliente);
        if (!$cliente) return redirect()->to(base_url('admin/almacenamiento-items'))->with('error','Cliente no encontrado.');

        $model = new AlmacenamientoItemModel();
        return view('admin/asignar-items', [
            'slug'      => self::SLUG,
            'titulo'    => self::TITULO,
            'icono'     => self::ICONO,
            'headerBg'  => self::HEADER_BG,
            'items'     => $model->listarTodos(),
            'asignados' => $model->idItemsAsignados($idCliente),
            'cliente'   => $cliente,
        ]);
    }

    public function guardarAsignaciones(int $idCliente)
    {
        if (!$this->checkAccess()) return redirect()->to('/login');
        $cliente = (new ClientModel())->find($idCliente);
        if (!$cliente) return redirect()->to(base_url('admin/almacenamiento-items'))->with('error','Cliente no encontrado.');

        $idItems = array_filter(array_map('intval', (array)$this->request->getPost('id_items')));
        (new AlmacenamientoItemModel())->reemplazarAsignaciones($idCliente, $idItems);

        return redirect()->to(base_url('admin/almacenamiento-items/asignar/' . $idCliente))
            ->with('msg', count($idItems) . ' item(s) asignado(s) a ' . $cliente['nombre_cliente'] . '.');
    }

    private function collectPost(): array
    {
        return [
            'nombre'      => trim((string)$this->request->getPost('nombre')),
            'descripcion' => $this->request->getPost('descripcion') ?: null,
            'icono'       => $this->request->getPost('icono') ?: null,
            'orden'       => (int)$this->request->getPost('orden'),
            'activo'      => $this->request->getPost('activo') ? 1 : 0,
        ];
    }
}

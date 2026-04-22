<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\UserModel;
use CodeIgniter\Controller;
use Config\Database;

/**
 * CRUD del personal contratado por un cliente (tendero).
 * Los empleados son tbl_usuarios con tipo_usuario='employee' e id_entidad=id_cliente.
 * Solo pueden entrar al checklist de rutinas — no ven dashboard.
 */
class EmpleadosController extends Controller
{
    /**
     * Listado de empleados, filtrado por cliente.
     * Admin/Consultor: puede elegir cliente. Cliente (dueño): siempre su id_cliente.
     */
    public function index()
    {
        $role     = session()->get('role');
        $idCliente = (int) ($this->request->getGet('cliente') ?? 0);

        $clientModel = new ClientModel();

        // Dueño: solo su cliente
        if ($role === 'client') {
            $idCliente = (int) session()->get('user_id');
        }

        $data['clientes'] = $this->clientesAccesibles();
        if ($idCliente === 0 && !empty($data['clientes'])) {
            $idCliente = (int) $data['clientes'][0]['id_cliente'];
        }

        $data['idCliente'] = $idCliente;
        $data['cliente']   = $idCliente > 0 ? $clientModel->find($idCliente) : null;
        $data['empleados'] = [];

        if ($idCliente > 0) {
            $db = Database::connect();
            $data['empleados'] = $db->query(
                "SELECT id_usuario, nombre_completo, email, estado, created_at
                   FROM tbl_usuarios
                  WHERE tipo_usuario = 'employee' AND id_entidad = ?
                  ORDER BY nombre_completo",
                [$idCliente]
            )->getResultArray();
        }

        return view('empleados/index', $data);
    }

    public function add()
    {
        $role = session()->get('role');
        $idCliente = (int) ($this->request->getGet('cliente') ?? 0);

        if ($role === 'client') {
            $idCliente = (int) session()->get('user_id');
        }

        if ($idCliente === 0) {
            return redirect()->to('/empleados')->with('error', 'Debe seleccionar un cliente.');
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) {
            return redirect()->to('/empleados')->with('error', 'Cliente no encontrado.');
        }

        return view('empleados/add', ['cliente' => $cliente, 'idCliente' => $idCliente]);
    }

    public function addPost()
    {
        $role = session()->get('role');
        $idCliente = (int) $this->request->getPost('id_cliente');

        if ($role === 'client') {
            $idCliente = (int) session()->get('user_id');
        }

        $rules = [
            'nombre_completo' => 'required|min_length[3]|max_length[255]',
            'email'           => 'required|valid_email|is_unique[tbl_usuarios.email]',
            'password'        => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $userModel->createUser([
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'email'           => $this->request->getPost('email'),
            'password'        => $this->request->getPost('password'),
            'tipo_usuario'    => 'employee',
            'id_entidad'      => $idCliente,
            'estado'          => 'activo',
        ]);

        return redirect()->to('/empleados?cliente=' . $idCliente)->with('msg', 'Empleado creado.');
    }

    public function edit(int $id)
    {
        $userModel = new UserModel();
        $empleado  = $userModel->find($id);

        if (!$empleado || $empleado['tipo_usuario'] !== 'employee') {
            return redirect()->to('/empleados')->with('error', 'Empleado no encontrado.');
        }

        $this->verificarAcceso((int)$empleado['id_entidad']);

        return view('empleados/edit', ['empleado' => $empleado]);
    }

    public function editPost(int $id)
    {
        $userModel = new UserModel();
        $empleado  = $userModel->find($id);
        if (!$empleado || $empleado['tipo_usuario'] !== 'employee') {
            return redirect()->to('/empleados')->with('error', 'Empleado no encontrado.');
        }

        $this->verificarAcceso((int)$empleado['id_entidad']);

        $rules = [
            'nombre_completo' => 'required|min_length[3]|max_length[255]',
            'email'           => "required|valid_email|is_unique[tbl_usuarios.email,id_usuario,{$id}]",
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'email'           => $this->request->getPost('email'),
            'estado'          => $this->request->getPost('estado') ?: 'activo',
        ];

        $pwd = (string) $this->request->getPost('password');
        if ($pwd !== '') {
            $data['password'] = $pwd; // updateUser re-hashea
        }

        $userModel->updateUser($id, $data);

        return redirect()->to('/empleados?cliente=' . (int)$empleado['id_entidad'])->with('msg', 'Empleado actualizado.');
    }

    public function delete(int $id)
    {
        $userModel = new UserModel();
        $empleado  = $userModel->find($id);
        if (!$empleado || $empleado['tipo_usuario'] !== 'employee') {
            return redirect()->to('/empleados')->with('error', 'Empleado no encontrado.');
        }

        $this->verificarAcceso((int)$empleado['id_entidad']);
        $userModel->delete($id);

        return redirect()->to('/empleados?cliente=' . (int)$empleado['id_entidad'])->with('msg', 'Empleado eliminado.');
    }

    // ═══════════════ Helpers ═══════════════

    /** Clientes que el usuario logueado puede ver. */
    private function clientesAccesibles(): array
    {
        $role = session()->get('role');
        $userId = (int) session()->get('user_id');
        $db = Database::connect();

        if ($role === 'client') {
            return $db->query(
                "SELECT id_cliente, nombre_cliente FROM tbl_clientes WHERE id_cliente = ?",
                [$userId]
            )->getResultArray();
        }

        if ($role === 'consultant') {
            return $db->query(
                "SELECT id_cliente, nombre_cliente FROM tbl_clientes
                  WHERE id_consultor = ? AND (estado = 'activo' OR estado IS NULL)
                  ORDER BY nombre_cliente",
                [$userId]
            )->getResultArray();
        }

        return $db->query(
            "SELECT id_cliente, nombre_cliente FROM tbl_clientes
              WHERE estado = 'activo' OR estado IS NULL
              ORDER BY nombre_cliente"
        )->getResultArray();
    }

    private function verificarAcceso(int $idCliente): void
    {
        $role = session()->get('role');
        if ($role === 'client' && (int) session()->get('user_id') !== $idCliente) {
            throw new \RuntimeException('Acceso denegado: empleado no pertenece a este cliente.');
        }
    }
}

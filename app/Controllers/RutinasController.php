<?php

namespace App\Controllers;

use App\Libraries\NotificadorRutinas;
use App\Models\ClientModel;
use App\Models\RutinaActividadModel;
use App\Models\RutinaAsignacionModel;
use App\Models\RutinaRegistroModel;
use App\Models\UserModel;
use CodeIgniter\Controller;
use Config\Database;

class RutinasController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // CRUD de actividades
    // ══════════════════════════════════════════════════════════════

    public function listActividades()
    {
        $m = new RutinaActividadModel();
        $data['actividades'] = $m->orderBy('nombre', 'ASC')->findAll();
        return view('rutinas/list_actividades', $data);
    }

    public function addActividad()
    {
        return view('rutinas/add_actividad');
    }

    public function addActividadPost()
    {
        $rules = [
            'nombre'     => 'required|min_length[2]|max_length[255]',
            'frecuencia' => 'required|in_list[L-V,diaria]',
            'peso'       => 'required|decimal',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $m = new RutinaActividadModel();
        $m->insert([
            'nombre'      => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'frecuencia'  => $this->request->getPost('frecuencia'),
            'peso'        => $this->request->getPost('peso'),
            'activa'      => 1,
        ]);

        return redirect()->to('/rutinas/actividades')->with('msg', 'Actividad creada.');
    }

    public function editActividad(int $id)
    {
        $m = new RutinaActividadModel();
        $actividad = $m->find($id);
        if (!$actividad) {
            return redirect()->to('/rutinas/actividades')->with('error', 'Actividad no encontrada.');
        }
        return view('rutinas/edit_actividad', ['actividad' => $actividad]);
    }

    public function editActividadPost(int $id)
    {
        $rules = [
            'nombre'     => 'required|min_length[2]|max_length[255]',
            'frecuencia' => 'required|in_list[L-V,diaria]',
            'peso'       => 'required|decimal',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $m = new RutinaActividadModel();
        $m->update($id, [
            'nombre'      => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'frecuencia'  => $this->request->getPost('frecuencia'),
            'peso'        => $this->request->getPost('peso'),
            'activa'      => $this->request->getPost('activa') ? 1 : 0,
        ]);

        return redirect()->to('/rutinas/actividades')->with('msg', 'Actividad actualizada.');
    }

    public function deleteActividad(int $id)
    {
        (new RutinaActividadModel())->delete($id);
        return redirect()->to('/rutinas/actividades')->with('msg', 'Actividad eliminada.');
    }

    // ══════════════════════════════════════════════════════════════
    // Asignaciones — por Cliente → Empleados → Actividades
    // ══════════════════════════════════════════════════════════════

    public function listAsignaciones()
    {
        $role     = session()->get('role');
        $idCliente = (int) ($this->request->getGet('cliente') ?? 0);
        if ($role === 'client') {
            $idCliente = (int) session()->get('user_id');
        }

        $data['clientes']   = $this->clientesAccesibles();
        if ($idCliente === 0 && !empty($data['clientes'])) {
            $idCliente = (int) $data['clientes'][0]['id_cliente'];
        }
        $data['idCliente']  = $idCliente;

        $db = Database::connect();
        $data['empleados']   = [];
        $data['asignaciones'] = [];
        $data['actividades'] = (new RutinaActividadModel())->where('activa', 1)->orderBy('nombre')->findAll();

        if ($idCliente > 0) {
            // Incluye tanto empleados como al dueño (tipo=client): ambos pueden ejecutar rutinas.
            $data['empleados'] = $db->query(
                "SELECT id_usuario, nombre_completo, email, tipo_usuario
                   FROM tbl_usuarios
                  WHERE id_entidad = ? AND estado = 'activo'
                    AND tipo_usuario IN ('employee', 'client')
                  ORDER BY FIELD(tipo_usuario, 'client', 'employee'), nombre_completo",
                [$idCliente]
            )->getResultArray();

            if (!empty($data['empleados'])) {
                $ids = array_column($data['empleados'], 'id_usuario');
                $data['asignaciones'] = $db->query(
                    "SELECT ra.id_asignacion, ra.id_usuario, ra.id_actividad, ra.activa,
                            u.nombre_completo, u.email, u.tipo_usuario,
                            a.nombre AS actividad, a.frecuencia, a.peso
                       FROM rutinas_asignaciones ra
                       JOIN tbl_usuarios u         ON u.id_usuario   = ra.id_usuario
                       JOIN rutinas_actividades a  ON a.id_actividad = ra.id_actividad
                      WHERE ra.id_usuario IN (" . implode(',', array_map('intval', $ids)) . ")
                      ORDER BY u.nombre_completo, a.nombre"
                )->getResultArray();
            }
        }

        return view('rutinas/list_asignaciones', $data);
    }

    public function addAsignacionPost()
    {
        $idUsuario   = (int) $this->request->getPost('id_usuario');
        $actividades = (array) $this->request->getPost('actividades');
        $idCliente   = (int) $this->request->getPost('id_cliente');

        if ($idUsuario <= 0 || empty($actividades)) {
            return redirect()->back()->with('error', 'Selecciona empleado y al menos una actividad.');
        }

        // Valida: empleado O dueño (client) del cliente seleccionado
        $userModel = new UserModel();
        $u = $userModel->find($idUsuario);
        if (!$u || !in_array($u['tipo_usuario'], ['employee', 'client'], true)) {
            return redirect()->back()->with('error', 'Usuario inválido para asignación.');
        }
        if ($idCliente > 0 && (int)$u['id_entidad'] !== $idCliente) {
            return redirect()->back()->with('error', 'El usuario no pertenece al cliente seleccionado.');
        }

        $m = new RutinaAsignacionModel();
        $creadas = 0;
        foreach ($actividades as $idAct) {
            $idAct = (int) $idAct;
            if ($idAct <= 0) continue;
            $existe = $m->where('id_usuario', $idUsuario)->where('id_actividad', $idAct)->first();
            if ($existe) continue;
            $m->insert(['id_usuario' => $idUsuario, 'id_actividad' => $idAct, 'activa' => 1]);
            $creadas++;
        }

        return redirect()->to('/rutinas/asignaciones?cliente=' . ($idCliente ?: (int)$u['id_entidad']))
                         ->with('msg', "{$creadas} asignación(es) creada(s).");
    }

    public function deleteAsignacion(int $id)
    {
        $m = new RutinaAsignacionModel();
        $a = $m->find($id);
        $idCliente = 0;
        if ($a) {
            $userModel = new UserModel();
            $u = $userModel->find((int)$a['id_usuario']);
            $idCliente = (int)($u['id_entidad'] ?? 0);
            $m->delete($id);
        }
        return redirect()->to('/rutinas/asignaciones' . ($idCliente ? '?cliente='.$idCliente : ''))
                         ->with('msg', 'Asignación eliminada.');
    }

    // ══════════════════════════════════════════════════════════════
    // Calendario — vista dual (por cliente | por empleado)
    // ══════════════════════════════════════════════════════════════

    public function calendario()
    {
        $hoy        = date('Y-m-d');
        $role       = session()->get('role');
        $mes        = (int) ($this->request->getGet('mes')  ?? date('n'));
        $anio       = (int) ($this->request->getGet('anio') ?? date('Y'));
        $idCliente  = (int) ($this->request->getGet('cliente') ?? 0);
        $idEmpleado = (int) ($this->request->getGet('empleado') ?? 0);

        if ($role === 'client') {
            $idCliente = (int) session()->get('user_id');
        }

        $data['clientes'] = $this->clientesAccesibles();
        if ($idCliente === 0 && !empty($data['clientes'])) {
            $idCliente = (int) $data['clientes'][0]['id_cliente'];
        }

        $db = Database::connect();
        $data['empleados'] = $idCliente > 0
            ? $db->query(
                "SELECT id_usuario, nombre_completo, tipo_usuario FROM tbl_usuarios
                  WHERE id_entidad = ? AND estado = 'activo'
                    AND tipo_usuario IN ('employee', 'client')
                  ORDER BY FIELD(tipo_usuario, 'client', 'employee'), nombre_completo",
                [$idCliente]
            )->getResultArray()
            : [];

        $data['mes']        = $mes;
        $data['anio']       = $anio;
        $data['idCliente']  = $idCliente;
        $data['idEmpleado'] = $idEmpleado;
        $data['nombreMes']  = $this->nombreMes($mes);
        $data['diasHabiles'] = $this->calcularDiasHabiles($anio, $mes);

        $data['resumenPorEmpleado'] = [];
        $data['empleado']           = null;
        $data['actividades']        = [];
        $data['registros']          = [];
        $data['puntajeDiario']      = [];
        $data['puntajeSemanal']     = [];
        $data['puntajeMensual']     = 0;
        $data['pesoTotal']          = 0;

        if ($idEmpleado > 0) {
            // Modo por empleado (vista detallada)
            $data['empleado'] = $db->table('tbl_usuarios')->where('id_usuario', $idEmpleado)->get()->getRowArray();
            $this->cargarDetalleEmpleado($db, $idEmpleado, $mes, $anio, $hoy, $data);
        } elseif ($idCliente > 0) {
            // Modo por cliente (tabla resumen: fila por empleado, columna por día)
            foreach ($data['empleados'] as $emp) {
                $d = ['empleado' => $emp];
                $this->cargarDetalleEmpleado($db, (int)$emp['id_usuario'], $mes, $anio, $hoy, $d);
                $data['resumenPorEmpleado'][] = $d;
            }
        }

        return view('rutinas/calendario', $data);
    }

    private function cargarDetalleEmpleado($db, int $idEmpleado, int $mes, int $anio, string $hoy, array &$out): void
    {
        $actividades = $db->query(
            "SELECT a.id_actividad, a.nombre, a.frecuencia, a.peso
               FROM rutinas_asignaciones ra
               JOIN rutinas_actividades a ON a.id_actividad = ra.id_actividad
              WHERE ra.id_usuario = ? AND ra.activa = 1 AND a.activa = 1
              ORDER BY a.nombre",
            [$idEmpleado]
        )->getResultArray();

        $inicio = sprintf('%04d-%02d-01', $anio, $mes);
        $fin    = date('Y-m-t', strtotime($inicio));

        $regs = $db->query(
            "SELECT id_actividad, fecha, completada, hora_completado
               FROM rutinas_registros
              WHERE id_usuario = ? AND fecha BETWEEN ? AND ?",
            [$idEmpleado, $inicio, $fin]
        )->getResultArray();

        $registros = [];
        foreach ($regs as $r) {
            $registros[$r['fecha']][$r['id_actividad']] = $r;
        }

        $diasHabiles = $out['diasHabiles'] ?? $this->calcularDiasHabiles($anio, $mes);
        [$diario, $semanal, $mensual] = $this->calcularPuntajes($actividades, $registros, $diasHabiles, $hoy);

        $pesoTotal = 0;
        foreach ($actividades as $a) $pesoTotal += (float) $a['peso'];

        $out['actividades']    = $actividades;
        $out['registros']      = $registros;
        $out['puntajeDiario']  = $diario;
        $out['puntajeSemanal'] = $semanal;
        $out['puntajeMensual'] = $mensual;
        $out['pesoTotal']      = $pesoTotal;
    }

    // ══════════════════════════════════════════════════════════════
    // Atajo autenticado: redirige al checklist propio
    // ══════════════════════════════════════════════════════════════

    public function miChecklist()
    {
        $session = session();
        $userId  = (int) ($session->get('id_usuario') ?? 0);
        if ($userId <= 0) {
            return redirect()->to('/login');
        }

        $fecha = date('Y-m-d');
        $token = NotificadorRutinas::generarToken($userId, $fecha);

        return redirect()->to("/rutinas/checklist/{$userId}/{$fecha}/{$token}");
    }

    // ══════════════════════════════════════════════════════════════
    // Checklist público (token)
    // ══════════════════════════════════════════════════════════════

    public function checklistPublico(int $userId, string $fecha, string $token)
    {
        if (!$this->fechaValida($fecha)) {
            return view('rutinas/checklist_error', ['mensaje' => 'Fecha inválida.']);
        }
        $dow = (int) date('N', strtotime($fecha));
        if ($dow >= 6) {
            return view('rutinas/checklist_error', ['mensaje' => 'El checklist solo aplica de lunes a viernes.']);
        }
        if (!NotificadorRutinas::validarToken($userId, $fecha, $token)) {
            return view('rutinas/checklist_error', ['mensaje' => 'Token inválido o expirado.']);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!$user) {
            return view('rutinas/checklist_error', ['mensaje' => 'Usuario no encontrado.']);
        }

        $db = Database::connect();
        $actividades = $db->query(
            "SELECT a.id_actividad, a.nombre, a.descripcion, a.peso
               FROM rutinas_asignaciones ra
               JOIN rutinas_actividades a ON a.id_actividad = ra.id_actividad
              WHERE ra.id_usuario = ? AND ra.activa = 1 AND a.activa = 1
              ORDER BY a.nombre",
            [$userId]
        )->getResultArray();

        $registros = $db->query(
            "SELECT id_actividad FROM rutinas_registros
              WHERE id_usuario = ? AND fecha = ? AND completada = 1",
            [$userId, $fecha]
        )->getResultArray();

        $ya = [];
        foreach ($registros as $r) $ya[(int)$r['id_actividad']] = true;

        $pesoTotal = 0.0;
        foreach ($actividades as $a) $pesoTotal += (float) $a['peso'];

        return view('rutinas/checklist_publico', [
            'usuario'     => $user,
            'fecha'       => $fecha,
            'token'       => $token,
            'actividades' => $actividades,
            'ya'          => $ya,
            'pesoTotal'   => $pesoTotal,
        ]);
    }

    public function updateChecklistPublico()
    {
        $userId = (int) $this->request->getPost('user_id');
        $fecha  = (string) $this->request->getPost('fecha');
        $token  = (string) $this->request->getPost('token');
        $idAct  = (int) $this->request->getPost('id_actividad');

        if ($userId <= 0 || !$this->fechaValida($fecha) || $idAct <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Parámetros inválidos']);
        }
        if (!NotificadorRutinas::validarToken($userId, $fecha, $token)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Token inválido']);
        }

        $asig = (new RutinaAsignacionModel())
            ->where('id_usuario', $userId)
            ->where('id_actividad', $idAct)
            ->where('activa', 1)
            ->first();

        if (!$asig) {
            return $this->response->setJSON(['success' => false, 'message' => 'Actividad no asignada']);
        }

        $m = new RutinaRegistroModel();
        $existe = $m->where('id_usuario', $userId)->where('id_actividad', $idAct)->where('fecha', $fecha)->first();
        if ($existe) {
            return $this->response->setJSON(['success' => true, 'duplicate' => true]);
        }

        $m->insert([
            'id_usuario'      => $userId,
            'id_actividad'    => $idAct,
            'fecha'           => $fecha,
            'completada'      => 1,
            'hora_completado' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Disparador manual "Terminar reporte" → email a consultor + dueño.
     */
    public function reportarChecklist()
    {
        $userId = (int) $this->request->getPost('user_id');
        $fecha  = (string) $this->request->getPost('fecha');
        $token  = (string) $this->request->getPost('token');

        if ($userId <= 0 || !$this->fechaValida($fecha)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Parámetros inválidos']);
        }
        if (!NotificadorRutinas::validarToken($userId, $fecha, $token)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Token inválido']);
        }

        $notif = new NotificadorRutinas();
        $res   = $notif->enviarResumenCierre($userId, $fecha);

        return $this->response->setJSON($res);
    }

    // ══════════════════════════════════════════════════════════════
    // Helpers
    // ══════════════════════════════════════════════════════════════

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

    private function fechaValida(string $fecha): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) return false;
        $ts = strtotime($fecha);
        return $ts !== false && date('Y-m-d', $ts) === $fecha;
    }

    private function calcularDiasHabiles(int $anio, int $mes): array
    {
        $dias = [];
        $inicio = sprintf('%04d-%02d-01', $anio, $mes);
        $lastDay = (int) date('t', strtotime($inicio));
        for ($d = 1; $d <= $lastDay; $d++) {
            $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $d);
            $dow = (int) date('N', strtotime($fecha));
            if ($dow <= 5) {
                $dias[] = ['fecha' => $fecha, 'dia' => $d, 'dow' => $dow, 'semana' => (int) date('W', strtotime($fecha))];
            }
        }
        return $dias;
    }

    private function nombreMes(int $mes): string
    {
        $nombres = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                    7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
        return $nombres[$mes] ?? '';
    }

    private function calcularPuntajes(array $actividades, array $registros, array $diasHabiles, string $hoy): array
    {
        $pesoTotal = 0.0;
        foreach ($actividades as $a) $pesoTotal += (float) $a['peso'];

        $diario = [];
        foreach ($diasHabiles as $d) {
            $fecha = $d['fecha'];
            if ($pesoTotal <= 0) { $diario[$fecha] = 0; continue; }
            $sum = 0.0;
            foreach ($actividades as $a) {
                if (isset($registros[$fecha][$a['id_actividad']]) && (int)$registros[$fecha][$a['id_actividad']]['completada'] === 1) {
                    $sum += (float)$a['peso'];
                }
            }
            $diario[$fecha] = (int) round(($sum / $pesoTotal) * 100);
        }

        $porSemana = [];
        foreach ($diasHabiles as $d) {
            if ($d['fecha'] > $hoy) continue;
            $porSemana[$d['semana']][] = $diario[$d['fecha']];
        }
        $semanal = [];
        foreach ($porSemana as $sem => $vals) {
            $semanal[$sem] = (int) round(array_sum($vals) / max(count($vals), 1));
        }

        $pasados = [];
        foreach ($diasHabiles as $d) {
            if ($d['fecha'] > $hoy) continue;
            $pasados[] = $diario[$d['fecha']];
        }
        $mensual = count($pasados) > 0 ? (int) round(array_sum($pasados) / count($pasados)) : 0;

        return [$diario, $semanal, $mensual];
    }
}

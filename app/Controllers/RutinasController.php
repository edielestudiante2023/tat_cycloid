<?php

namespace App\Controllers;

use App\Libraries\NotificadorRutinas;
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
    // CRUD de asignaciones
    // ══════════════════════════════════════════════════════════════

    public function listAsignaciones()
    {
        $db = Database::connect();
        $data['asignaciones'] = $db->query(
            "SELECT ra.id_asignacion, ra.id_usuario, ra.id_actividad, ra.activa,
                    u.nombre_completo, u.email,
                    a.nombre AS actividad, a.frecuencia, a.peso
               FROM rutinas_asignaciones ra
               JOIN tbl_usuarios u         ON u.id_usuario   = ra.id_usuario
               JOIN rutinas_actividades a  ON a.id_actividad = ra.id_actividad
              ORDER BY u.nombre_completo, a.nombre"
        )->getResultArray();

        // Usuarios activos con email (pool para formulario)
        $data['usuarios'] = $db->query(
            "SELECT id_usuario, nombre_completo, email, tipo_usuario
               FROM tbl_usuarios
              WHERE estado = 'activo' AND email IS NOT NULL AND email <> ''
              ORDER BY nombre_completo"
        )->getResultArray();

        $data['actividades'] = (new RutinaActividadModel())
            ->where('activa', 1)->orderBy('nombre')->findAll();

        return view('rutinas/list_asignaciones', $data);
    }

    public function addAsignacionPost()
    {
        $idUsuario   = (int) $this->request->getPost('id_usuario');
        $actividades = (array) $this->request->getPost('actividades');

        if ($idUsuario <= 0 || empty($actividades)) {
            return redirect()->back()->with('error', 'Selecciona usuario y al menos una actividad.');
        }

        $m = new RutinaAsignacionModel();
        $creadas = 0;
        foreach ($actividades as $idAct) {
            $idAct = (int) $idAct;
            if ($idAct <= 0) continue;
            $existe = $m->where('id_usuario', $idUsuario)->where('id_actividad', $idAct)->first();
            if ($existe) continue;
            $m->insert([
                'id_usuario'   => $idUsuario,
                'id_actividad' => $idAct,
                'activa'       => 1,
            ]);
            $creadas++;
        }

        return redirect()->to('/rutinas/asignaciones')->with('msg', "{$creadas} asignación(es) creada(s).");
    }

    public function deleteAsignacion(int $id)
    {
        (new RutinaAsignacionModel())->delete($id);
        return redirect()->to('/rutinas/asignaciones')->with('msg', 'Asignación eliminada.');
    }

    // ══════════════════════════════════════════════════════════════
    // Calendario (matriz actividades × días hábiles)
    // ══════════════════════════════════════════════════════════════

    public function calendario()
    {
        $hoy     = date('Y-m-d');
        $mes     = (int) ($this->request->getGet('mes') ?? date('n'));
        $anio    = (int) ($this->request->getGet('anio') ?? date('Y'));
        $usuario = (int) ($this->request->getGet('usuario') ?? 0);

        $db = Database::connect();
        $data['usuarios'] = $db->query(
            "SELECT DISTINCT u.id_usuario, u.nombre_completo
               FROM tbl_usuarios u
               JOIN rutinas_asignaciones ra ON ra.id_usuario = u.id_usuario AND ra.activa = 1
              WHERE u.estado = 'activo'
              ORDER BY u.nombre_completo"
        )->getResultArray();

        if ($usuario === 0 && !empty($data['usuarios'])) {
            $usuario = (int) $data['usuarios'][0]['id_usuario'];
        }

        $data['mes']         = $mes;
        $data['anio']        = $anio;
        $data['usuarioId']   = $usuario;
        $data['nombreMes']   = $this->nombreMes($mes);
        $data['diasHabiles'] = $this->calcularDiasHabiles($anio, $mes);

        $data['actividades']    = [];
        $data['registros']      = [];
        $data['puntajeDiario']  = [];
        $data['puntajeSemanal'] = [];
        $data['puntajeMensual'] = 0;

        if ($usuario > 0) {
            $data['actividades'] = $db->query(
                "SELECT a.id_actividad, a.nombre, a.frecuencia, a.peso
                   FROM rutinas_asignaciones ra
                   JOIN rutinas_actividades a ON a.id_actividad = ra.id_actividad
                  WHERE ra.id_usuario = ? AND ra.activa = 1 AND a.activa = 1
                  ORDER BY a.nombre",
                [$usuario]
            )->getResultArray();

            $inicio = sprintf('%04d-%02d-01', $anio, $mes);
            $fin    = date('Y-m-t', strtotime($inicio));

            $regs = $db->query(
                "SELECT id_actividad, fecha, completada, hora_completado
                   FROM rutinas_registros
                  WHERE id_usuario = ? AND fecha BETWEEN ? AND ?",
                [$usuario, $inicio, $fin]
            )->getResultArray();

            foreach ($regs as $r) {
                $data['registros'][$r['fecha']][$r['id_actividad']] = $r;
            }

            [$data['puntajeDiario'], $data['puntajeSemanal'], $data['puntajeMensual']] =
                $this->calcularPuntajes($data['actividades'], $data['registros'], $data['diasHabiles'], $hoy);
        }

        return view('rutinas/calendario', $data);
    }

    // ══════════════════════════════════════════════════════════════
    // Checklist público (sin login, con token)
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
            "SELECT a.id_actividad, a.nombre, a.descripcion
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

        return view('rutinas/checklist_publico', [
            'usuario'     => $user,
            'fecha'       => $fecha,
            'token'       => $token,
            'actividades' => $actividades,
            'ya'          => $ya,
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

    // ══════════════════════════════════════════════════════════════
    // Helpers internos
    // ══════════════════════════════════════════════════════════════

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

    /**
     * @return array{0:array<string,float>, 1:array<int,float>, 2:float}
     */
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

        // Agregar por semana ISO
        $porSemana = [];
        foreach ($diasHabiles as $d) {
            if ($d['fecha'] > $hoy) continue;
            $porSemana[$d['semana']][] = $diario[$d['fecha']];
        }
        $semanal = [];
        foreach ($porSemana as $sem => $vals) {
            $semanal[$sem] = (int) round(array_sum($vals) / max(count($vals), 1));
        }

        // Mensual (solo días hasta hoy)
        $pasados = [];
        foreach ($diasHabiles as $d) {
            if ($d['fecha'] > $hoy) continue;
            $pasados[] = $diario[$d['fecha']];
        }
        $mensual = count($pasados) > 0 ? (int) round(array_sum($pasados) / count($pasados)) : 0;

        return [$diario, $semanal, $mensual];
    }
}

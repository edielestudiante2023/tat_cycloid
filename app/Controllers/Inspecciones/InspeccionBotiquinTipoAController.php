<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionBotiquinTipoAModel;
use App\Models\ElementoBotiquinTipoAModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\MantenimientoModel;
use App\Models\VencimientosMantenimientoModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class InspeccionBotiquinTipoAController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    protected InspeccionBotiquinTipoAModel $inspeccionModel;
    protected ElementoBotiquinTipoAModel $elementoModel;

    /**
     * 13 elementos fijos del botiquín Tipo A (Resolución 0705).
     * Total 14 unidades. Establecimientos comerciales < 2000 m2.
     */
    public const ELEMENTOS = [
        // Grupo 1: Materiales de curación
        'gasas_limpias'      => ['label' => 'Gasas limpias (Paq x20)',             'min' => 1, 'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 1],
        'esparadrapo'        => ['label' => 'Esparadrapo de tela rollo 4"',        'min' => 1, 'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 2],
        'bajalenguas'        => ['label' => 'Bajalenguas (Paq x20)',               'min' => 1, 'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 3],
        'venda_elastica_2x5' => ['label' => 'Venda elastica 2x5 yardas',           'min' => 1, 'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 4],
        'venda_elastica_3x5' => ['label' => 'Venda elastica 3x5 yardas',           'min' => 1, 'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 5],
        'venda_elastica_5x5' => ['label' => 'Venda elastica 5x5 yardas',           'min' => 1, 'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 6],
        'venda_algodon_3x5'  => ['label' => 'Venda de algodon 3x5 yardas',         'min' => 1, 'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 7],
        'venda_algodon_5x5'  => ['label' => 'Venda de algodon 5x5 yardas',         'min' => 1, 'venc' => false, 'grupo' => 'Materiales de curacion',    'orden' => 8],
        // Grupo 2: Antisépticos y soluciones (con vencimiento)
        'yodopovidona'       => ['label' => 'Yodopovidona (Frasco 120 ml)',        'min' => 1, 'venc' => true,  'grupo' => 'Antisepticos y soluciones', 'orden' => 9],
        'solucion_salina'    => ['label' => 'Solucion salina 250/500 cc',          'min' => 2, 'venc' => true,  'grupo' => 'Antisepticos y soluciones', 'orden' => 10],
        'alcohol'            => ['label' => 'Alcohol antiseptico 275 ml',          'min' => 1, 'venc' => true,  'grupo' => 'Antisepticos y soluciones', 'orden' => 11],
        // Grupo 3: Protección e instrumental
        'guantes_latex'      => ['label' => 'Guantes latex examen (Caja x100)',    'min' => 1, 'venc' => false, 'grupo' => 'Proteccion e instrumental', 'orden' => 12],
        'termometro'         => ['label' => 'Termometro mercurio/digital',         'min' => 1, 'venc' => false, 'grupo' => 'Proteccion e instrumental', 'orden' => 13],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionBotiquinTipoAModel();
        $this->elementoModel = new ElementoBotiquinTipoAModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_botiquin_tipo_a.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_botiquin_tipo_a.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_botiquin_tipo_a.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_botiquin_tipo_a.fecha_inspeccion', 'DESC')
            ->findAll();

        $data = [
            'title'        => 'Inspeccion de Botiquin Tipo A',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/botiquin_tipo_a/list', $data),
            'title'   => 'Botiquin Tipo A',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion de Botiquin Tipo A',
            'inspeccion'  => null,
            'idCliente'  => $idCliente,
            'elementos'  => self::ELEMENTOS,
            'elementosData' => [],
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/botiquin_tipo_a/form', $data),
            'title'   => 'Nuevo Botiquin Tipo A',
        ]);
    }

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_inspeccion', '/inspecciones/botiquin-tipo-a/edit/');
        if ($existing) return $existing;

        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $inspeccionData = $this->getInspeccionPostData($userId);
        $inspeccionData['estado'] = 'borrador';

        $inspeccionData['foto_1'] = $this->uploadFoto('foto_1', 'uploads/inspecciones/botiquin_tipo_a/fotos/');
        $inspeccionData['foto_2'] = $this->uploadFoto('foto_2', 'uploads/inspecciones/botiquin_tipo_a/fotos/');

        $this->inspeccionModel->insert($inspeccionData);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        $this->saveElementos($idInspeccion);

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion);
        }

        return redirect()->to('/inspecciones/botiquin-tipo-a/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/botiquin-tipo-a')->with('error', 'Inspeccion no encontrada');
        }
        $elementosRaw = $this->elementoModel->getByInspeccion($id);
        $elementosData = [];
        foreach ($elementosRaw as $elem) {
            $elementosData[$elem['clave']] = $elem;
        }

        $data = [
            'title'         => 'Editar Inspeccion de Botiquin Tipo A',
            'inspeccion'     => $inspeccion,
            'idCliente'     => $inspeccion['id_cliente'],
            'elementos'     => self::ELEMENTOS,
            'elementosData' => $elementosData,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/botiquin_tipo_a/form', $data),
            'title'   => 'Editar Botiquin Tipo A',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/botiquin-tipo-a')->with('error', 'No se puede editar');
        }

        $userId = session()->get('user_id');
        $updateData = $this->getInspeccionPostData($userId);

        $campos_foto = ['foto_1', 'foto_2'];
        foreach ($campos_foto as $campo) {
            $nuevaFoto = $this->uploadFoto($campo, 'uploads/inspecciones/botiquin_tipo_a/fotos/');
            if ($nuevaFoto) {
                if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                    unlink(FCPATH . $inspeccion[$campo]);
                }
                $updateData[$campo] = $nuevaFoto;
            }
        }

        $this->inspeccionModel->update($id, $updateData);
        $this->saveElementos($id);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if (($inspeccion['estado'] ?? '') === 'completo') {
            $this->generarPendientes($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        return redirect()->to('/inspecciones/botiquin-tipo-a/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/botiquin-tipo-a')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $elementosRaw = $this->elementoModel->getByInspeccion($id);
        $elementosData = [];
        foreach ($elementosRaw as $elem) {
            $elementosData[$elem['clave']] = $elem;
        }

        $data = [
            'title'         => 'Ver Inspeccion de Botiquin Tipo A',
            'inspeccion'     => $inspeccion,
            'cliente'       => $clientModel->find($inspeccion['id_cliente']),
            'consultor'     => $consultantModel->find($inspeccion['id_consultor']),
            'elementos'     => self::ELEMENTOS,
            'elementosData' => $elementosData,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/botiquin_tipo_a/view', $data),
            'title'   => 'Ver Botiquin Tipo A',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/botiquin-tipo-a')->with('error', 'No encontrada');
        }

        $this->generarPendientes($id);

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->inspeccionModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);
        $this->syncVencimiento($inspeccion);

        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN BOTIQUÍN TIPO A',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionBotiquinTipoA'
        );
        $msg = 'Inspección finalizada y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/botiquin-tipo-a/view/' . $id)
            ->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/botiquin-tipo-a')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        $this->servirPdf($fullPath, 'botiquin_tipo_a_' . $id . '.pdf');
        return;
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/botiquin-tipo-a')->with('error', 'No encontrada');
        }
        $campos_foto = ['foto_1', 'foto_2'];
        foreach ($campos_foto as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/botiquin-tipo-a')->with('msg', 'Inspeccion eliminada');
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/botiquin-tipo-a')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $this->generarPendientes($id);

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/botiquin-tipo-a/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function getInspeccionPostData(int $userId): array
    {
        return [
            'id_cliente'         => $this->request->getPost('id_cliente'),
            'id_consultor'       => $userId,
            'fecha_inspeccion'   => $this->request->getPost('fecha_inspeccion'),
            'ubicacion_botiquin' => $this->request->getPost('ubicacion_botiquin'),
            'instalado_pared'    => $this->request->getPost('instalado_pared') ?: 'SI',
            'libre_obstaculos'   => $this->request->getPost('libre_obstaculos') ?: 'SI',
            'lugar_visible'      => $this->request->getPost('lugar_visible') ?: 'SI',
            'con_senalizacion'   => $this->request->getPost('con_senalizacion') ?: 'SI',
            'tipo_botiquin'      => $this->request->getPost('tipo_botiquin') ?: 'LONA',
            'estado_botiquin'    => $this->request->getPost('estado_botiquin') ?: 'BUEN ESTADO',
            'recomendaciones'    => $this->request->getPost('recomendaciones'),
        ];
    }

    private function uploadFoto(string $fieldName, string $dir): ?string
    {
        $file = $this->request->getFile($fieldName);
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        $fileName = $file->getRandomName();
        $file->move(FCPATH . $dir, $fileName);
        $this->comprimirImagen(FCPATH . $dir . $fileName);
        return $dir . $fileName;
    }

    private function saveElementos(int $idInspeccion): void
    {
        $this->elementoModel->deleteByInspeccion($idInspeccion);

        foreach (self::ELEMENTOS as $clave => $config) {
            $cantidad = (int)($this->request->getPost('elem_' . $clave . '_cantidad') ?? 0);
            $estado = $this->request->getPost('elem_' . $clave . '_estado') ?? 'BUEN ESTADO';
            $vencimiento = null;
            if ($config['venc']) {
                $vencimiento = $this->request->getPost('elem_' . $clave . '_vencimiento') ?: null;
            }

            $this->elementoModel->insert([
                'id_inspeccion'    => $idInspeccion,
                'clave'            => $clave,
                'cantidad'         => $cantidad,
                'estado'           => $estado,
                'fecha_vencimiento'=> $vencimiento,
                'orden'            => $config['orden'],
            ]);
        }
    }

    private function generarPendientes(int $id): void
    {
        $elementos = $this->elementoModel->getByInspeccion($id);
        $pendientes = [];
        $hoy = date('Y-m-d');

        foreach ($elementos as $elem) {
            $clave = $elem['clave'];
            $config = self::ELEMENTOS[$clave] ?? null;
            if (!$config) continue;

            $label    = $config['label'];
            $min      = $config['min'];
            $cantidad = (int)$elem['cantidad'];
            $estado   = $elem['estado'];
            $venc     = $elem['fecha_vencimiento'] ?? null;

            $issues = [];

            if ($cantidad < $min) {
                $issues[] = "Cantidad insuficiente: {$cantidad} (min. {$min})";
            }

            if (in_array($estado, ['ESTADO REGULAR', 'MAL ESTADO']) ||
                ($estado === 'SIN EXISTENCIAS' && $cantidad >= $min)) {
                $issues[] = "Estado: {$estado}";
            }

            if ($config['venc'] && !empty($venc) && $venc < $hoy) {
                $issues[] = 'Vencido (' . date('d/m/Y', strtotime($venc)) . ')';
            }

            if (!empty($issues)) {
                $pendientes[] = [
                    'elemento' => $label,
                    'cantidad' => $cantidad,
                    'min'      => $min,
                    'detalle'  => implode(' — ', $issues),
                ];
            }
        }

        $resultado = [
            'items'          => $pendientes,
            'sin_pendientes' => empty($pendientes),
        ];

        $this->inspeccionModel->update($id, [
            'pendientes_generados' => json_encode($resultado, JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        $elementosRaw = $this->elementoModel->getByInspeccion($id);
        $elementosData = [];
        foreach ($elementosRaw as $elem) {
            $elementosData[$elem['clave']] = $elem;
        }

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        $fotosBase64 = [];
        $camposFoto = ['foto_1', 'foto_2'];
        foreach ($camposFoto as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $fotosBase64[$campo] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        $data = [
            'inspeccion'    => $inspeccion,
            'cliente'       => $cliente,
            'consultor'     => $consultor,
            'elementos'     => self::ELEMENTOS,
            'elementosData' => $elementosData,
            'logoBase64'    => $logoBase64,
            'fotosBase64'   => $fotosBase64,
        ];

        $html = view('inspecciones/botiquin_tipo_a/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/botiquin_tipo_a/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'botiquin_tipo_a_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/botiquin-tipo-a/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN BOTIQUÍN TIPO A',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionBotiquinTipoA'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/botiquin-tipo-a/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/botiquin-tipo-a/view/{$id}")->with('error', $result['error']);
    }

    private function uploadToReportes(array $inspeccion, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 13)
            ->like('observaciones', 'insp_bot_tipo_a_id:' . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_CLIENTES . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'botiquin_tipo_a_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION BOTIQUIN TIPO A - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 13,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones Tipo A. insp_bot_tipo_a_id:' . $inspeccion['id'],
            'enlace'          => base_url('uploads/clientes/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    /**
     * Sincroniza la fecha de vencimiento más próxima con tbl_vencimientos_mantenimientos.
     */
    private function syncVencimiento(array $inspeccion): void
    {
        $elementos = $this->elementoModel->getByInspeccion($inspeccion['id']);
        $fechaMin = null;

        foreach ($elementos as $elem) {
            $config = self::ELEMENTOS[$elem['clave']] ?? null;
            if (!$config || !$config['venc']) continue;
            if (empty($elem['fecha_vencimiento'])) continue;

            if ($fechaMin === null || $elem['fecha_vencimiento'] < $fechaMin) {
                $fechaMin = $elem['fecha_vencimiento'];
            }
        }

        if (empty($fechaMin)) {
            return;
        }

        $mantenimientoModel = new MantenimientoModel();
        $vencimientoModel = new VencimientosMantenimientoModel();

        $mantenimiento = $mantenimientoModel
            ->like('detalle_mantenimiento', 'botiqu', 'both')
            ->first();

        if (!$mantenimiento) {
            log_message('warning', "syncVencimiento botiquin tipo A: No se encontro tipo de mantenimiento con keyword 'botiqu' en tbl_mantenimientos");
            return;
        }

        $idMantenimiento = $mantenimiento['id_mantenimiento'];

        $existente = $vencimientoModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_mantenimiento', $idMantenimiento)
            ->where('estado_actividad', 'sin ejecutar')
            ->first();

        if ($existente) {
            $vencimientoModel->update($existente['id_vencimientos_mmttos'], [
                'fecha_vencimiento' => $fechaMin,
                'id_consultor'      => $inspeccion['id_consultor'],
                'observaciones'     => 'Actualizado desde inspeccion botiquin Tipo A #' . $inspeccion['id'],
            ]);
        } else {
            $vencimientoModel->insert([
                'id_mantenimiento'  => $idMantenimiento,
                'id_cliente'        => $inspeccion['id_cliente'],
                'id_consultor'      => $inspeccion['id_consultor'],
                'fecha_vencimiento' => $fechaMin,
                'estado_actividad'  => 'sin ejecutar',
                'observaciones'     => 'Auto-generado desde inspeccion botiquin Tipo A #' . $inspeccion['id'],
            ]);
        }
    }
}

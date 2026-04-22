<?php

namespace App\Controllers;

use App\Libraries\AnulacionHelper;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\SolicitudAnulacionModel;
use App\Models\ReporteModel;
use CodeIgniter\Controller;
use Config\Database;

class AnulacionController extends Controller
{
    /**
     * Vista pública del consultor (solo token requerido).
     * Muestra datos del registro + justificación + botones aprobar/rechazar.
     */
    public function detalle(string $token)
    {
        $model = new SolicitudAnulacionModel();
        $solicitud = $model->porToken($token);
        if (!$solicitud) return $this->paginaNoEncontrada();

        $cliente   = (new ClientModel())->find($solicitud['id_cliente']);
        $consultor = (new ConsultantModel())->find($solicitud['id_consultor']);
        $detalle   = $this->obtenerDetalleRegistro($solicitud['tipo_registro'], (int)$solicitud['id_registro'], $solicitud['id_registro_secundario'] !== null ? (int)$solicitud['id_registro_secundario'] : null);

        return view('anulacion/detalle', [
            'solicitud' => $solicitud,
            'cliente'   => $cliente,
            'consultor' => $consultor,
            'detalle'   => $detalle,
            'etiqueta'  => AnulacionHelper::etiquetaTipo($solicitud['tipo_registro']),
        ]);
    }

    public function aprobar(string $token)
    {
        return $this->responder($token, 'aprobada');
    }

    public function rechazar(string $token)
    {
        return $this->responder($token, 'rechazada');
    }

    private function responder(string $token, string $nuevoEstado)
    {
        $model = new SolicitudAnulacionModel();
        $solicitud = $model->porToken($token);
        if (!$solicitud) return $this->paginaNoEncontrada();

        if ($solicitud['estado'] !== 'pendiente') {
            return redirect()->to(base_url('anular/' . $token))
                ->with('error', 'Esta solicitud ya fue ' . $solicitud['estado'] . '.');
        }

        $nota = trim((string)$this->request->getPost('nota_respuesta'));

        if ($nuevoEstado === 'aprobada') {
            $ok = $this->ejecutarAnulacion($solicitud['tipo_registro'], (int)$solicitud['id_registro'], $solicitud['id_registro_secundario'] !== null ? (int)$solicitud['id_registro_secundario'] : null);
            if (!$ok) {
                return redirect()->to(base_url('anular/' . $token))
                    ->with('error', 'No se pudo ejecutar la anulación. Contacte al administrador.');
            }
        }

        $model->update($solicitud['id_solicitud'], [
            'estado'          => $nuevoEstado,
            'nota_respuesta'  => $nota ?: null,
            'fecha_respuesta' => date('Y-m-d H:i:s'),
        ]);

        $solicitud = $model->porToken($token);
        $cliente   = (new ClientModel())->find($solicitud['id_cliente']);
        $detalleDesc = $this->descripcionCorta($solicitud['tipo_registro'], (int)$solicitud['id_registro']);
        AnulacionHelper::notificarCliente($solicitud, $cliente ?: [], $solicitud['tipo_registro'], $detalleDesc);

        return view('anulacion/respondida', [
            'solicitud' => $solicitud,
            'etiqueta'  => AnulacionHelper::etiquetaTipo($solicitud['tipo_registro']),
        ]);
    }

    /**
     * Ejecuta la eliminación real del registro según el tipo.
     * Mantiene el comportamiento actual de cada módulo (soft o hard).
     */
    private function ejecutarAnulacion(string $tipo, int $id, ?int $idSecundario): bool
    {
        $db = Database::connect();
        try {
            switch ($tipo) {
                case 'nevera':
                    $n = $db->table('tbl_nevera')->where('id_nevera', $id)->get()->getRowArray();
                    if (!$n) return false;
                    $this->unlinkFoto($n['foto_equipo'] ?? null);
                    $inspecciones = $db->table('tbl_inspeccion_nevera')->where('id_nevera', $id)->get()->getResultArray();
                    foreach ($inspecciones as $i) {
                        $this->unlinkFoto($i['foto_temperatura'] ?? null);
                        $this->unlinkFoto($i['foto_humedad'] ?? null);
                    }
                    $db->table('tbl_nevera')->update(['activo' => 0], ['id_nevera' => $id]);
                    return true;

                case 'nevera-medicion':
                    $m = $db->table('tbl_inspeccion_nevera')->where('id', $id)->get()->getRowArray();
                    if (!$m) return false;
                    $this->unlinkFoto($m['foto_temperatura'] ?? null);
                    $this->unlinkFoto($m['foto_humedad'] ?? null);
                    if (!empty($m['id_reporte'])) (new ReporteModel())->delete($m['id_reporte']);
                    $db->table('tbl_inspeccion_nevera')->delete(['id' => $id]);
                    return true;

                case 'limpieza-local':
                    $detalles = $db->table('tbl_inspeccion_limpieza_detalle')->where('id_inspeccion', $id)->get()->getResultArray();
                    foreach ($detalles as $d) $this->unlinkFoto($d['foto'] ?? null);
                    $header = $db->table('tbl_inspeccion_limpieza_local')->where('id', $id)->get()->getRowArray();
                    if (!empty($header['id_reporte'])) (new ReporteModel())->delete($header['id_reporte']);
                    $db->table('tbl_inspeccion_limpieza_detalle')->delete(['id_inspeccion' => $id]);
                    $db->table('tbl_inspeccion_limpieza_local')->delete(['id' => $id]);
                    return true;

                case 'equipos':
                    $detalles = $db->table('tbl_inspeccion_equipos_detalle')->where('id_inspeccion', $id)->get()->getResultArray();
                    foreach ($detalles as $d) $this->unlinkFoto($d['foto'] ?? null);
                    $header = $db->table('tbl_inspeccion_equipos')->where('id', $id)->get()->getRowArray();
                    if (!empty($header['id_reporte'])) (new ReporteModel())->delete($header['id_reporte']);
                    $db->table('tbl_inspeccion_equipos_detalle')->delete(['id_inspeccion' => $id]);
                    $db->table('tbl_inspeccion_equipos')->delete(['id' => $id]);
                    return true;

                case 'recepcion-mp':
                    $r = $db->table('tbl_recepcion_mp')->where('id', $id)->get()->getRowArray();
                    if (!$r) return false;
                    foreach (['foto_producto','foto_factura','foto_temperatura'] as $campo) {
                        $this->unlinkFoto($r[$campo] ?? null);
                    }
                    if (!empty($r['id_reporte'])) (new ReporteModel())->delete($r['id_reporte']);
                    $db->table('tbl_recepcion_mp')->delete(['id' => $id]);
                    return true;

                case 'proveedor':
                    $db->table('tbl_proveedor')->update(['activo' => 0], ['id_proveedor' => $id]);
                    return true;

                case 'contaminacion':
                    $detalles = $db->table('tbl_inspeccion_contaminacion_detalle')->where('id_inspeccion', $id)->get()->getResultArray();
                    foreach ($detalles as $d) $this->unlinkFoto($d['foto'] ?? null);
                    $header = $db->table('tbl_inspeccion_contaminacion')->where('id', $id)->get()->getRowArray();
                    if (!empty($header['id_reporte'])) (new ReporteModel())->delete($header['id_reporte']);
                    $db->table('tbl_inspeccion_contaminacion_detalle')->delete(['id_inspeccion' => $id]);
                    $db->table('tbl_inspeccion_contaminacion')->delete(['id' => $id]);
                    return true;

                case 'almacenamiento':
                    $detalles = $db->table('tbl_inspeccion_almacenamiento_detalle')->where('id_inspeccion', $id)->get()->getResultArray();
                    foreach ($detalles as $d) $this->unlinkFoto($d['foto'] ?? null);
                    $header = $db->table('tbl_inspeccion_almacenamiento')->where('id', $id)->get()->getRowArray();
                    if (!empty($header['id_reporte'])) (new ReporteModel())->delete($header['id_reporte']);
                    $db->table('tbl_inspeccion_almacenamiento_detalle')->delete(['id_inspeccion' => $id]);
                    $db->table('tbl_inspeccion_almacenamiento')->delete(['id' => $id]);
                    return true;

                case 'trabajador':
                    $soportes = $db->table('tbl_trabajador_soporte')->where('id_trabajador', $id)->get()->getResultArray();
                    foreach ($soportes as $s) {
                        $this->unlinkFoto($s['archivo'] ?? null);
                        if (!empty($s['id_reporte'])) (new ReporteModel())->delete($s['id_reporte']);
                    }
                    $db->table('tbl_trabajadores')->update(['activo' => 0], ['id_trabajador' => $id]);
                    return true;

                case 'trabajador-soporte':
                    $s = $db->table('tbl_trabajador_soporte')->where('id', $id)->get()->getRowArray();
                    if (!$s) return false;
                    $this->unlinkFoto($s['archivo'] ?? null);
                    if (!empty($s['id_reporte'])) (new ReporteModel())->delete($s['id_reporte']);
                    $db->table('tbl_trabajador_soporte')->delete(['id' => $id]);
                    return true;

                case 'bomberos-doc':
                    $d = $db->table('tbl_bomberos_documento')->where('id', $id)->get()->getRowArray();
                    if (!$d) return false;
                    $this->unlinkFoto($d['archivo'] ?? null);
                    if (!empty($d['id_reporte'])) (new ReporteModel())->delete($d['id_reporte']);
                    $db->table('tbl_bomberos_documento')->delete(['id' => $id]);
                    return true;
            }
        } catch (\Throwable $e) {
            log_message('error', 'AnulacionController::ejecutarAnulacion error: ' . $e->getMessage());
            return false;
        }
        return false;
    }

    private function unlinkFoto(?string $ruta): void
    {
        if (!$ruta) return;
        $path = ROOTPATH . 'public/uploads/' . $ruta;
        if (file_exists($path)) @unlink($path);
    }

    /**
     * Retorna descripción + campos + fotos del registro para mostrar al consultor.
     */
    private function obtenerDetalleRegistro(string $tipo, int $id, ?int $idSecundario): array
    {
        $db = Database::connect();
        $campos = [];
        $fotos  = [];
        $descripcion = AnulacionHelper::etiquetaTipo($tipo);

        try {
            switch ($tipo) {
                case 'nevera':
                    $r = $db->table('tbl_nevera')->where('id_nevera', $id)->get()->getRowArray();
                    if ($r) {
                        $descripcion = 'Nevera "' . ($r['nombre'] ?? '—') . '"';
                        $campos = [
                            'Nombre' => $r['nombre'] ?? '—',
                            'Tipo'   => $r['tipo'] ?? '—',
                            'Rango temperatura' => ($r['rango_temp_min'] ?? '—') . '°C a ' . ($r['rango_temp_max'] ?? '—') . '°C',
                            'Fecha registro'    => $r['creado_en'] ?? '—',
                        ];
                        if (!empty($r['foto_equipo'])) $fotos[] = $r['foto_equipo'];
                    }
                    break;

                case 'nevera-medicion':
                    $m = $db->table('tbl_inspeccion_nevera')->where('id', $id)->get()->getRowArray();
                    if ($m) {
                        $nev = $db->table('tbl_nevera')->where('id_nevera', $m['id_nevera'])->get()->getRowArray();
                        $descripcion = 'Medición del ' . date('d/m/Y H:i', strtotime($m['fecha_hora'] ?? 'now')) . ' — Nevera "' . ($nev['nombre'] ?? '—') . '"';
                        $campos = [
                            'Fecha y hora' => $m['fecha_hora'] ?? '—',
                            'Temperatura'  => ($m['temperatura'] ?? '—') . '°C',
                            'Humedad'      => ($m['humedad'] ?? '—'),
                            'Observaciones'=> $m['observaciones'] ?? '—',
                        ];
                        if (!empty($m['foto_temperatura'])) $fotos[] = $m['foto_temperatura'];
                        if (!empty($m['foto_humedad'])) $fotos[] = $m['foto_humedad'];
                    }
                    break;

                case 'limpieza-local':
                case 'equipos':
                case 'contaminacion':
                case 'almacenamiento':
                    $tableMap = [
                        'limpieza-local' => ['tbl_inspeccion_limpieza_local', 'tbl_inspeccion_limpieza_detalle'],
                        'equipos'        => ['tbl_inspeccion_equipos', 'tbl_inspeccion_equipos_detalle'],
                        'contaminacion'  => ['tbl_inspeccion_contaminacion', 'tbl_inspeccion_contaminacion_detalle'],
                        'almacenamiento' => ['tbl_inspeccion_almacenamiento', 'tbl_inspeccion_almacenamiento_detalle'],
                    ];
                    [$tHeader, $tDetalle] = $tableMap[$tipo];
                    $h = $db->table($tHeader)->where('id', $id)->get()->getRowArray();
                    if ($h) {
                        $descripcion = AnulacionHelper::etiquetaTipo($tipo) . ' del ' . ($h['fecha_hora'] ?? $h['creado_en'] ?? '—');
                        $campos = [
                            'Fecha' => $h['fecha_hora'] ?? $h['creado_en'] ?? '—',
                            'Observaciones' => $h['observaciones'] ?? '—',
                        ];
                        $detalles = $db->table($tDetalle)->where('id_inspeccion', $id)->get()->getResultArray();
                        foreach ($detalles as $d) {
                            if (!empty($d['foto'])) $fotos[] = $d['foto'];
                        }
                    }
                    break;

                case 'recepcion-mp':
                    $r = $db->table('tbl_recepcion_mp')->where('id', $id)->get()->getRowArray();
                    if ($r) {
                        $descripcion = 'Recepción MP del ' . ($r['fecha_hora'] ?? '—');
                        $campos = [
                            'Fecha'        => $r['fecha_hora'] ?? '—',
                            'Categoría'    => $r['categoria'] ?? '—',
                            'Cantidad'     => $r['cantidad'] ?? '—',
                            'Temperatura'  => $r['temperatura'] ?? '—',
                            'Observaciones'=> $r['observaciones'] ?? '—',
                        ];
                        foreach (['foto_producto','foto_factura','foto_temperatura'] as $campo) {
                            if (!empty($r[$campo])) $fotos[] = $r[$campo];
                        }
                    }
                    break;

                case 'proveedor':
                    $p = $db->table('tbl_proveedor')->where('id_proveedor', $id)->get()->getRowArray();
                    if ($p) {
                        $descripcion = 'Proveedor: ' . ($p['nombre'] ?? '—');
                        $campos = [
                            'Nombre'     => $p['nombre'] ?? '—',
                            'NIT/CC'     => $p['nit'] ?? '—',
                            'Categoría'  => $p['categorias'] ?? '—',
                            'Contacto'   => $p['contacto'] ?? '—',
                        ];
                    }
                    break;

                case 'trabajador':
                    $t = $db->table('tbl_trabajadores')->where('id_trabajador', $id)->get()->getRowArray();
                    if ($t) {
                        $descripcion = 'Trabajador: ' . ($t['nombre_completo'] ?? '—');
                        $campos = [
                            'Nombre'       => $t['nombre_completo'] ?? '—',
                            'Documento'    => $t['documento'] ?? '—',
                            'Cargo'        => $t['cargo'] ?? '—',
                            'Fecha ingreso'=> $t['fecha_ingreso'] ?? '—',
                        ];
                    }
                    break;

                case 'trabajador-soporte':
                    $s = $db->table('tbl_trabajador_soporte')->where('id', $id)->get()->getRowArray();
                    if ($s) {
                        $descripcion = 'Soporte de trabajador: ' . ($s['tipo'] ?? '—');
                        $campos = [
                            'Tipo soporte' => $s['tipo'] ?? '—',
                            'Archivo'      => $s['archivo'] ?? '—',
                            'Fecha'        => $s['creado_en'] ?? '—',
                        ];
                        if (!empty($s['archivo'])) $fotos[] = $s['archivo'];
                    }
                    break;

                case 'bomberos-doc':
                    $d = $db->table('tbl_bomberos_documento')->where('id', $id)->get()->getRowArray();
                    if ($d) {
                        $descripcion = 'Documento Bomberos: ' . ($d['tipo_documento'] ?? '—');
                        $campos = [
                            'Tipo documento' => $d['tipo_documento'] ?? '—',
                            'Archivo'        => $d['archivo'] ?? '—',
                            'Fecha subida'   => $d['creado_en'] ?? '—',
                        ];
                        if (!empty($d['archivo'])) $fotos[] = $d['archivo'];
                    }
                    break;
            }
        } catch (\Throwable $e) {
            log_message('error', 'obtenerDetalleRegistro error: ' . $e->getMessage());
        }

        return ['descripcion' => $descripcion, 'campos' => $campos, 'fotos' => $fotos];
    }

    private function descripcionCorta(string $tipo, int $id): string
    {
        $d = $this->obtenerDetalleRegistro($tipo, $id, null);
        return $d['descripcion'] ?? AnulacionHelper::etiquetaTipo($tipo);
    }

    private function paginaNoEncontrada()
    {
        return view('anulacion/respondida', [
            'solicitud' => null,
            'etiqueta'  => '',
            'noEncontrada' => true,
        ]);
    }
}

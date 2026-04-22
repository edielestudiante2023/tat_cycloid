<?php

namespace App\Libraries;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\SolicitudAnulacionModel;

/**
 * Crea solicitudes de anulación (eliminaciones con aprobación del consultor)
 * y envía las notificaciones por SendGrid al consultor y al cliente.
 */
class AnulacionHelper
{
    /**
     * Crea una solicitud pendiente y notifica al consultor asignado.
     *
     * @return array ['success' => bool, 'token' => string|null, 'error' => string|null]
     */
    public static function crearSolicitud(
        int $idCliente,
        string $tipoRegistro,
        int $idRegistro,
        ?int $idRegistroSecundario,
        string $justificacion,
        string $descripcionRegistro = ''
    ): array {
        $justificacion = trim($justificacion);
        if (mb_strlen($justificacion) < 20) {
            return ['success' => false, 'error' => 'La justificación debe tener al menos 20 caracteres.'];
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) return ['success' => false, 'error' => 'Cliente no encontrado.'];

        $idConsultor = (int)($cliente['id_consultor'] ?? 0);
        if (!$idConsultor) return ['success' => false, 'error' => 'El cliente no tiene consultor asignado.'];

        $model = new SolicitudAnulacionModel();
        $token = $model->generarToken();

        $model->insert([
            'token'                  => $token,
            'tipo_registro'          => $tipoRegistro,
            'id_registro'            => $idRegistro,
            'id_registro_secundario' => $idRegistroSecundario,
            'id_cliente'             => $idCliente,
            'id_consultor'           => $idConsultor,
            'justificacion'          => $justificacion,
            'estado'                 => 'pendiente',
            'fecha_solicitud'        => date('Y-m-d H:i:s'),
        ]);

        $envio = self::enviarEmailConsultor($cliente, $idConsultor, $token, $tipoRegistro, $descripcionRegistro, $justificacion);
        $destinatarios = $envio['destinatarios'] ?? [];
        $emailOk = !empty($envio['success']);

        if (!empty($destinatarios)) {
            $lista = implode(', ', $destinatarios);
            $flashMsg = $emailOk
                ? 'Solicitud de anulación enviada a: ' . $lista . '. Recibirás un correo con la respuesta.'
                : 'Solicitud de anulación creada para: ' . $lista . ', pero el correo no pudo enviarse (' . ($envio['error'] ?? 'error desconocido') . '). Contacta al consultor para que revise la solicitud.';
        } else {
            $flashMsg = 'Solicitud de anulación creada, pero no hay correo de consultor configurado para notificar. Contacta al consultor manualmente.';
        }

        return [
            'success'       => true,
            'token'         => $token,
            'destinatarios' => $destinatarios,
            'email_enviado' => $emailOk,
            'flash_msg'     => $flashMsg,
        ];
    }

    /**
     * Notifica al cliente el resultado de la solicitud (aprobada o rechazada).
     */
    public static function notificarCliente(array $solicitud, array $cliente, string $tipoRegistro, string $descripcionRegistro): array
    {
        $correoCliente = $cliente['correo_cliente'] ?? '';
        if (!$correoCliente) return ['success' => false, 'error' => 'Cliente sin correo.'];

        $estado = $solicitud['estado'];
        $nota = trim($solicitud['nota_respuesta'] ?? '');
        $fecha = $solicitud['fecha_respuesta'] ?? date('Y-m-d H:i:s');

        $esAprobada = $estado === 'aprobada';
        $titulo = $esAprobada ? 'Solicitud de anulación APROBADA' : 'Solicitud de anulación RECHAZADA';
        $color  = $esAprobada ? '#198754' : '#dc3545';

        $subject = "[TAT] {$titulo} — " . self::etiquetaTipo($tipoRegistro);

        $notaBlock = '';
        if ($nota !== '') {
            $notaEsc = htmlspecialchars($nota);
            $notaBlock = "<tr><td style='padding:10px;background:#fff;border:1px solid #ddd;'><strong>Nota del consultor:</strong></td><td style='padding:10px;background:#fff;border:1px solid #ddd;'>{$notaEsc}</td></tr>";
        }

        $descEsc = htmlspecialchars($descripcionRegistro ?: self::etiquetaTipo($tipoRegistro));

        $html = "
        <div style='font-family:Segoe UI,Arial,sans-serif;max-width:600px;margin:0 auto;'>
            <div style='background:{$color};padding:20px;text-align:center;border-radius:10px 10px 0 0;'>
                <h1 style='color:#fff;margin:0;font-size:20px;'>{$titulo}</h1>
            </div>
            <div style='padding:25px;background:#f8f9fa;border-radius:0 0 10px 10px;'>
                <p>El consultor ha revisado tu solicitud de anulación.</p>
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr><td style='padding:10px;background:#fff;border:1px solid #ddd;'><strong>Registro:</strong></td><td style='padding:10px;background:#fff;border:1px solid #ddd;'>{$descEsc}</td></tr>
                    <tr><td style='padding:10px;background:#fff;border:1px solid #ddd;'><strong>Resultado:</strong></td><td style='padding:10px;background:#fff;border:1px solid #ddd;color:{$color};font-weight:bold;'>" . strtoupper($estado) . "</td></tr>
                    <tr><td style='padding:10px;background:#fff;border:1px solid #ddd;'><strong>Fecha:</strong></td><td style='padding:10px;background:#fff;border:1px solid #ddd;'>" . date('d/m/Y H:i', strtotime($fecha)) . "</td></tr>
                    {$notaBlock}
                </table>
                <p style='color:#999;font-size:11px;margin-top:30px;'>TAT Cycloid — Seguridad y Salud en el Trabajo.</p>
            </div>
        </div>";

        return self::enviarSendGrid($subject, $html, [[$correoCliente, $cliente['nombre_cliente'] ?? 'Cliente']]);
    }

    private static function enviarEmailConsultor(array $cliente, int $idConsultor, string $token, string $tipoRegistro, string $descripcionRegistro, string $justificacion): array
    {
        $consultantModel = new ConsultantModel();
        $consultor = $consultantModel->find($idConsultor);
        if (!$consultor) return ['success' => false, 'error' => 'Consultor no encontrado.'];

        $correoConsultor       = $consultor['correo_consultor'] ?? '';
        $nombreConsultor       = $consultor['nombre_consultor'] ?? 'Consultor';
        $correoExterno         = $cliente['email_consultor_externo'] ?? '';
        $nombreExterno         = $cliente['consultor_externo'] ?? '';
        $nombreCliente         = $cliente['nombre_cliente'] ?? 'Cliente';

        $destinatarios = [];
        if ($correoConsultor) $destinatarios[] = [$correoConsultor, $nombreConsultor];
        if ($correoExterno)   $destinatarios[] = [$correoExterno, $nombreExterno ?: 'Consultor externo'];

        $soloEmails = array_column($destinatarios, 0);

        if (empty($destinatarios)) return ['success' => false, 'error' => 'No hay correos de consultor configurados.', 'destinatarios' => []];

        $urlBase = rtrim(base_url(), '/');
        $linkAprobacion = "{$urlBase}/anular/{$token}";

        $etiqueta = self::etiquetaTipo($tipoRegistro);
        $descEsc  = htmlspecialchars($descripcionRegistro ?: $etiqueta);
        $justEsc  = nl2br(htmlspecialchars($justificacion));
        $cliEsc   = htmlspecialchars($nombreCliente);

        $subject = "[TAT] Solicitud de anulación — {$nombreCliente} — {$etiqueta}";

        $html = "
        <div style='font-family:Segoe UI,Arial,sans-serif;max-width:600px;margin:0 auto;'>
            <div style='background:#ee6c21;padding:20px;text-align:center;border-radius:10px 10px 0 0;'>
                <h1 style='color:#fff;margin:0;font-size:20px;'>Solicitud de anulación pendiente</h1>
            </div>
            <div style='padding:25px;background:#f8f9fa;border-radius:0 0 10px 10px;'>
                <p>El cliente <strong>{$cliEsc}</strong> ha solicitado la anulación de un registro en la plataforma TAT y requiere tu aprobación.</p>
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr><td style='padding:10px;background:#fff;border:1px solid #ddd;'><strong>Cliente:</strong></td><td style='padding:10px;background:#fff;border:1px solid #ddd;'>{$cliEsc}</td></tr>
                    <tr><td style='padding:10px;background:#fff;border:1px solid #ddd;'><strong>Tipo de registro:</strong></td><td style='padding:10px;background:#fff;border:1px solid #ddd;'>{$etiqueta}</td></tr>
                    <tr><td style='padding:10px;background:#fff;border:1px solid #ddd;'><strong>Registro:</strong></td><td style='padding:10px;background:#fff;border:1px solid #ddd;'>{$descEsc}</td></tr>
                </table>
                <div style='background:#fff3cd;border:1px solid #ffeaa7;padding:15px;border-radius:6px;margin:15px 0;'>
                    <strong>Justificación del cliente:</strong><br>
                    <div style='margin-top:8px;color:#555;'>{$justEsc}</div>
                </div>
                <p style='text-align:center;margin:25px 0;'>
                    <a href='{$linkAprobacion}' style='background:#ee6c21;color:#fff;padding:14px 28px;text-decoration:none;border-radius:6px;font-weight:bold;display:inline-block;'>
                        Revisar solicitud
                    </a>
                </p>
                <p style='color:#888;font-size:12px;'>Si el botón no funciona, copia este enlace:<br><span style='word-break:break-all;'>{$linkAprobacion}</span></p>
                <p style='color:#999;font-size:11px;margin-top:30px;'>TAT Cycloid — Seguridad y Salud en el Trabajo.</p>
            </div>
        </div>";

        $r = self::enviarSendGrid($subject, $html, $destinatarios);
        $r['destinatarios'] = $soloEmails;
        return $r;
    }

    private static function enviarSendGrid(string $subject, string $html, array $destinatarios): array
    {
        if (env('DISABLE_REPORT_EMAILS', false)) {
            log_message('info', "AnulacionHelper: email desactivado (DISABLE_REPORT_EMAILS).");
            return ['success' => true, 'message' => 'Email desactivado.'];
        }

        $apiKey = getenv('SENDGRID_API_KEY');
        if (!$apiKey) return ['success' => false, 'error' => 'SENDGRID_API_KEY no configurada.'];

        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom('notificacion.cycloidtalent@cycloidtalent.com', 'TAT Cycloid — SG-SST');
        $email->setSubject($subject);
        foreach ($destinatarios as [$to, $name]) {
            $email->addTo($to, $name ?: 'Destinatario');
        }
        $email->addContent('text/html', $html);

        $sendgrid = new \SendGrid($apiKey);
        try {
            $resp = $sendgrid->send($email);
            if ($resp->statusCode() >= 200 && $resp->statusCode() < 300) {
                $emails = array_column($destinatarios, 0);
                log_message('info', 'AnulacionHelper: email enviado a ' . implode(', ', $emails));
                return ['success' => true];
            }
            log_message('error', 'AnulacionHelper: SendGrid status ' . $resp->statusCode() . ' body ' . $resp->body());
            return ['success' => false, 'error' => 'SendGrid status ' . $resp->statusCode()];
        } catch (\Exception $e) {
            log_message('error', 'AnulacionHelper exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function etiquetaTipo(string $tipo): string
    {
        static $map = [
            'nevera'             => 'Nevera',
            'nevera-medicion'    => 'Medición de nevera',
            'limpieza-local'     => 'Inspección de limpieza del local',
            'equipos'            => 'Inspección de equipos y utensilios',
            'recepcion-mp'       => 'Recepción de materias primas',
            'proveedor'          => 'Proveedor',
            'contaminacion'      => 'Inspección de contaminación cruzada',
            'almacenamiento'    => 'Inspección de almacenamiento',
            'trabajador'         => 'Trabajador',
            'trabajador-soporte' => 'Soporte de trabajador',
            'bomberos-doc'       => 'Documento de bomberos',
        ];
        return $map[$tipo] ?? $tipo;
    }
}

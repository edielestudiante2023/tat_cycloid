<?php

namespace App\Libraries;

use App\Models\RutinaActividadModel;
use App\Models\RutinaAsignacionModel;
use Config\Database;
use SendGrid\Mail\Mail;

class NotificadorRutinas
{
    private const FROM_EMAIL = 'notificacion.cycloidtalent@cycloidtalent.com';
    private const FROM_NAME  = 'Cycloid Talent - Rutinas SST';
    private const SECRET_FALLBACK = 'rutinas2026';

    /**
     * Envía recordatorio diario a cada usuario con al menos una asignación activa.
     * Aborta en sábado/domingo (devuelve omitidos = -1).
     *
     * @param string|null $fecha Formato YYYY-MM-DD. Si null usa hoy.
     * @return array{enviados:int, fallidos:int, omitidos:int, detalles:array}
     */
    public function enviarRecordatoriosDiarios(?string $fecha = null): array
    {
        $fecha = $fecha ?: date('Y-m-d');
        $dow   = (int) date('N', strtotime($fecha)); // 1..7

        if ($dow >= 6) {
            return ['enviados' => 0, 'fallidos' => 0, 'omitidos' => -1, 'detalles' => ['fin de semana']];
        }

        $db = Database::connect();

        // Usuarios activos con ≥1 asignación activa
        $usuarios = $db->query(
            "SELECT DISTINCT u.id_usuario, u.nombre_completo, u.email
               FROM tbl_usuarios u
               JOIN rutinas_asignaciones ra ON ra.id_usuario = u.id_usuario AND ra.activa = 1
              WHERE u.estado = 'activo' AND u.email IS NOT NULL AND u.email <> ''"
        )->getResultArray();

        $enviados = 0;
        $fallidos = 0;
        $detalles = [];

        foreach ($usuarios as $user) {
            $actividades = $db->query(
                "SELECT a.id_actividad, a.nombre, a.descripcion, a.frecuencia
                   FROM rutinas_asignaciones ra
                   JOIN rutinas_actividades a ON a.id_actividad = ra.id_actividad
                  WHERE ra.id_usuario = ?
                    AND ra.activa = 1
                    AND a.activa = 1",
                [$user['id_usuario']]
            )->getResultArray();

            if (empty($actividades)) {
                continue;
            }

            $token = self::generarToken((int)$user['id_usuario'], $fecha);
            $baseUrl = rtrim(env('app.baseURL', ''), '/');
            $link  = $baseUrl . '/rutinas/checklist/' . $user['id_usuario'] . '/' . $fecha . '/' . $token;

            $html = $this->generarHTML($user, $actividades, $fecha, $link);

            if ($this->enviarEmail($user['email'], $user['nombre_completo'] ?? 'Usuario', $fecha, $html)) {
                $enviados++;
                $detalles[] = "OK → {$user['email']}";
            } else {
                $fallidos++;
                $detalles[] = "FAIL → {$user['email']}";
            }
        }

        return ['enviados' => $enviados, 'fallidos' => $fallidos, 'omitidos' => 0, 'detalles' => $detalles];
    }

    public static function generarToken(int $userId, string $fecha): string
    {
        $secret = env('RUTINAS_SECRET', self::SECRET_FALLBACK);
        return substr(hash('sha256', $userId . '|' . $fecha . '|' . $secret), 0, 24);
    }

    public static function validarToken(int $userId, string $fecha, string $token): bool
    {
        return hash_equals(self::generarToken($userId, $fecha), $token);
    }

    private function generarHTML(array $user, array $actividades, string $fecha, string $link): string
    {
        $nombre = htmlspecialchars($user['nombre_completo'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
        $fechaHuman = date('d/m/Y', strtotime($fecha));
        $items = '';
        foreach ($actividades as $a) {
            $items .= '<li style="margin:6px 0;color:#2c3e50;"><strong>' .
                htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') . '</strong>';
            if (!empty($a['descripcion'])) {
                $items .= ' <span style="color:#666;">— ' . htmlspecialchars($a['descripcion'], ENT_QUOTES, 'UTF-8') . '</span>';
            }
            $items .= '</li>';
        }

        return "
        <div style=\"font-family: Segoe UI, Arial, sans-serif; max-width:600px; margin:0 auto;\">
            <div style=\"background:#1c2437; padding:22px; text-align:center; border-radius:10px 10px 0 0;\">
                <h1 style=\"color:#bd9751; margin:0; font-size:22px;\">Rutinas del día</h1>
                <p style=\"color:#fff; margin:6px 0 0 0;\">{$fechaHuman}</p>
            </div>
            <div style=\"padding:22px; background:#f8f9fa; border-radius:0 0 10px 10px;\">
                <p style=\"color:#2c3e50;\">Hola <strong>{$nombre}</strong>,</p>
                <p style=\"color:#2c3e50;\">Estas son tus actividades de hoy:</p>
                <ul style=\"padding-left:18px;\">{$items}</ul>
                <div style=\"text-align:center; margin:25px 0;\">
                    <a href=\"{$link}\" style=\"background:#bd9751; color:#1c2437; text-decoration:none; padding:12px 28px; border-radius:6px; font-weight:bold;\">Abrir checklist</a>
                </div>
                <p style=\"color:#999; font-size:11px; text-align:center;\">Enlace válido solo para hoy. No compartir.</p>
            </div>
        </div>";
    }

    private function enviarEmail(string $to, string $toName, string $fecha, string $html): bool
    {
        $apiKey = getenv('SENDGRID_API_KEY') ?: env('SENDGRID_API_KEY');
        if (!$apiKey) {
            log_message('error', 'NotificadorRutinas: falta SENDGRID_API_KEY');
            return false;
        }

        try {
            $mail = new Mail();
            $mail->setFrom(self::FROM_EMAIL, self::FROM_NAME);
            $mail->setSubject('Rutinas del día — ' . date('d/m/Y', strtotime($fecha)));
            $mail->addTo($to, $toName);
            $mail->addContent('text/html', $html);

            // Evitar reescritura del enlace tokenizado
            $trackingSettings = new \SendGrid\Mail\TrackingSettings();
            $clickTracking = new \SendGrid\Mail\ClickTracking();
            $clickTracking->setEnable(false);
            $clickTracking->setEnableText(false);
            $trackingSettings->setClickTracking($clickTracking);
            $mail->setTrackingSettings($trackingSettings);

            $sg = new \SendGrid($apiKey);
            $res = $sg->send($mail);

            return $res->statusCode() >= 200 && $res->statusCode() < 300;
        } catch (\Throwable $e) {
            log_message('error', 'NotificadorRutinas: ' . $e->getMessage());
            return false;
        }
    }
}

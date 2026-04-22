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

    /**
     * Enviar reporte de cierre del día al consultor + dueño del cliente.
     * Lo dispara el empleado al presionar "Terminar reporte".
     *
     * @return array{success:bool, message?:string, enviados?:int}
     */
    public function enviarResumenCierre(int $empleadoId, string $fecha): array
    {
        $db = Database::connect();

        $emp = $db->query(
            "SELECT u.id_usuario, u.nombre_completo, u.email, u.id_entidad AS id_cliente,
                    c.nombre_cliente, c.id_consultor
               FROM tbl_usuarios u
               LEFT JOIN tbl_clientes c ON c.id_cliente = u.id_entidad
              WHERE u.id_usuario = ?",
            [$empleadoId]
        )->getRowArray();

        if (!$emp) {
            return ['success' => false, 'message' => 'Empleado no encontrado.'];
        }

        // Destinatarios: consultor + usuario dueño del cliente
        $destinatarios = [];

        if (!empty($emp['id_consultor'])) {
            $consult = $db->query(
                "SELECT email, nombre_completo FROM tbl_usuarios
                  WHERE tipo_usuario = 'consultant' AND id_entidad = ? AND estado = 'activo'
                  LIMIT 1",
                [(int)$emp['id_consultor']]
            )->getRowArray();
            if ($consult && !empty($consult['email'])) {
                $destinatarios[] = ['email' => $consult['email'], 'nombre' => $consult['nombre_completo'] ?? 'Consultor'];
            }
        }

        if (!empty($emp['id_cliente'])) {
            $dueno = $db->query(
                "SELECT email, nombre_completo FROM tbl_usuarios
                  WHERE tipo_usuario = 'client' AND id_entidad = ? AND estado = 'activo'
                  LIMIT 1",
                [(int)$emp['id_cliente']]
            )->getRowArray();
            if ($dueno && !empty($dueno['email'])) {
                $destinatarios[] = ['email' => $dueno['email'], 'nombre' => $dueno['nombre_completo'] ?? 'Propietario'];
            }
        }

        if (empty($destinatarios)) {
            return ['success' => false, 'message' => 'Sin destinatarios (consultor o dueño).'];
        }

        // Detalle de actividades y estado
        $detalle = $db->query(
            "SELECT a.id_actividad, a.nombre, a.peso,
                    r.completada, r.hora_completado
               FROM rutinas_asignaciones ra
               JOIN rutinas_actividades a ON a.id_actividad = ra.id_actividad
               LEFT JOIN rutinas_registros r
                      ON r.id_actividad = ra.id_actividad
                     AND r.id_usuario   = ra.id_usuario
                     AND r.fecha        = ?
              WHERE ra.id_usuario = ? AND ra.activa = 1 AND a.activa = 1
              ORDER BY a.nombre",
            [$fecha, $empleadoId]
        )->getResultArray();

        $totalPeso = 0.0;
        $cumpPeso  = 0.0;
        foreach ($detalle as $d) {
            $totalPeso += (float)$d['peso'];
            if ((int)($d['completada'] ?? 0) === 1) $cumpPeso += (float)$d['peso'];
        }
        $pct = $totalPeso > 0 ? (int) round(($cumpPeso / $totalPeso) * 100) : 0;

        $html = $this->htmlResumenCierre($emp, $fecha, $detalle, $pct);

        $apiKey = getenv('SENDGRID_API_KEY') ?: env('SENDGRID_API_KEY');
        if (!$apiKey) {
            return ['success' => false, 'message' => 'SENDGRID_API_KEY no configurada.'];
        }

        $enviados = 0;
        try {
            $mail = new Mail();
            $mail->setFrom(self::FROM_EMAIL, self::FROM_NAME);
            $mail->setSubject(sprintf(
                'Reporte Rutinas — %s — %s (%d%%)',
                $emp['nombre_cliente'] ?? 'Cliente',
                date('d/m/Y', strtotime($fecha)),
                $pct
            ));
            foreach ($destinatarios as $d) {
                $mail->addTo($d['email'], $d['nombre']);
            }
            $mail->addContent('text/html', $html);

            $trackingSettings = new \SendGrid\Mail\TrackingSettings();
            $clickTracking = new \SendGrid\Mail\ClickTracking();
            $clickTracking->setEnable(false);
            $clickTracking->setEnableText(false);
            $trackingSettings->setClickTracking($clickTracking);
            $mail->setTrackingSettings($trackingSettings);

            $sg = new \SendGrid($apiKey);
            $res = $sg->send($mail);
            if ($res->statusCode() >= 200 && $res->statusCode() < 300) {
                $enviados = count($destinatarios);
            } else {
                log_message('error', 'NotificadorRutinas cierre: statusCode=' . $res->statusCode() . ' body=' . $res->body());
                return ['success' => false, 'message' => 'Fallo SendGrid (status ' . $res->statusCode() . ')'];
            }
        } catch (\Throwable $e) {
            log_message('error', 'NotificadorRutinas cierre: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Excepción al enviar: ' . $e->getMessage()];
        }

        return ['success' => true, 'enviados' => $enviados, 'pct' => $pct];
    }

    private function htmlResumenCierre(array $emp, string $fecha, array $detalle, int $pct): string
    {
        $empNom = htmlspecialchars($emp['nombre_completo'] ?? 'Empleado', ENT_QUOTES, 'UTF-8');
        $cliNom = htmlspecialchars($emp['nombre_cliente'] ?? 'Cliente', ENT_QUOTES, 'UTF-8');
        $fechaH = date('d/m/Y', strtotime($fecha));
        $pctColor = $pct >= 90 ? '#28a745' : ($pct >= 60 ? '#ffc107' : '#dc3545');

        $rows = '';
        foreach ($detalle as $d) {
            $done = (int)($d['completada'] ?? 0) === 1;
            $hora = $done && !empty($d['hora_completado']) ? date('H:i', strtotime($d['hora_completado'])) : '—';
            $icono = $done ? '✅' : '❌';
            $bg = $done ? '#e6f7ec' : '#fdecea';
            $rows .= sprintf(
                '<tr style="background:%s;"><td style="padding:8px;border:1px solid #ddd;">%s</td><td style="padding:8px;border:1px solid #ddd;">%s</td><td style="padding:8px;border:1px solid #ddd;text-align:center;">%s</td><td style="padding:8px;border:1px solid #ddd;text-align:center;">%s</td></tr>',
                $bg, $icono, htmlspecialchars($d['nombre'], ENT_QUOTES, 'UTF-8'), number_format((float)$d['peso'], 2), $hora
            );
        }

        return "
        <div style=\"font-family: Segoe UI, Arial, sans-serif; max-width:640px; margin:0 auto;\">
            <div style=\"background:#1c2437; padding:20px; text-align:center; border-radius:10px 10px 0 0;\">
                <h1 style=\"color:#bd9751; margin:0; font-size:20px;\">Reporte de Rutinas del día</h1>
                <p style=\"color:#fff; margin:6px 0 0 0;\">{$cliNom} · {$fechaH}</p>
            </div>
            <div style=\"padding:22px; background:#f8f9fa;\">
                <p style=\"color:#2c3e50;\">Empleado que diligenció: <strong>{$empNom}</strong></p>
                <div style=\"text-align:center; margin:18px 0;\">
                    <div style=\"display:inline-block; background:{$pctColor}; color:#fff; padding:14px 28px; border-radius:8px; font-size:24px; font-weight:bold;\">
                        Cumplimiento: {$pct}%
                    </div>
                </div>
                <table style=\"width:100%; border-collapse:collapse; font-size:14px; color:#2c3e50;\">
                    <thead>
                        <tr style=\"background:#1c2437; color:#bd9751;\">
                            <th style=\"padding:8px; border:1px solid #1c2437;\"></th>
                            <th style=\"padding:8px; border:1px solid #1c2437; text-align:left;\">Actividad</th>
                            <th style=\"padding:8px; border:1px solid #1c2437;\">Peso</th>
                            <th style=\"padding:8px; border:1px solid #1c2437;\">Hora</th>
                        </tr>
                    </thead>
                    <tbody>{$rows}</tbody>
                </table>
                <p style=\"color:#999; font-size:11px; text-align:center; margin-top:18px;\">Generado por Cycloid Talent — Rutinas SST</p>
            </div>
        </div>";
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

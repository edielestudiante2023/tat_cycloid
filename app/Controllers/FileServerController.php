<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * FileServerController — BC-shim.
 *
 * Post-consolidación de uploads, los soportes viven en public/uploads/ y se
 * sirven directo por Apache. Este controlador se mantiene solo para:
 *  - Redirigir URLs viejas `/serve-file/...` a `/uploads/...` (301 permanente).
 *  - Servir archivos que aún no fueron migrados desde UPLOADS_PATH (fallback).
 *
 * Ruta: GET /serve-file/(:any) → FileServerController::serve/$1
 */
class FileServerController extends BaseController
{
    public function serve(string ...$segments): ResponseInterface
    {
        $relativePath = implode('/', $segments);
        $relativePath = str_replace(['..', "\0"], '', $relativePath);
        $relativePath = ltrim($relativePath, '/');

        if ($relativePath === '') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Renombres conocidos para URLs legacy:
        //  - firmas_consultores/       → consultores/firmas/
        //  - planillas-seguridad-social/ → planillas-ss/
        //  - {NIT}/                    → clientes/{NIT}/
        $nuevo = preg_replace('#^firmas_consultores/#', 'consultores/firmas/', $relativePath);
        $nuevo = preg_replace('#^planillas-seguridad-social/#', 'planillas-ss/', $nuevo);
        $nuevo = preg_replace('#^(\d+)/#', 'clientes/$1/', $nuevo);

        // Si ya existe en la nueva ubicación pública → 301
        if (is_file(FCPATH . 'uploads/' . $nuevo)) {
            return redirect()->to(base_url('uploads/' . $nuevo), 301);
        }

        // Fallback: archivo aún en UPLOADS_PATH vieja (pre-migración)
        $session = session();
        if (! $session->get('isLoggedIn') && ! $session->get('client_logged_in')) {
            return redirect()->to('/login');
        }

        $filePath = rtrim(UPLOADS_PATH, '/\\') . '/' . $relativePath;
        if (! is_file($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Archivo no encontrado: ' . $relativePath
            );
        }

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $inlineTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ];
        $disposition = in_array($mimeType, $inlineTypes, true) ? 'inline' : 'attachment';

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . basename($filePath) . '"')
            ->setHeader('Content-Length', (string) filesize($filePath))
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($filePath));
    }
}

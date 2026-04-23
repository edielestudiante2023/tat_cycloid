<?php

/**
 * Helpers de URLs y rutas para archivos bajo public/uploads/.
 *
 * Uso: `upload_url('logo', $cliente['logo'])` en lugar de
 * `base_url('uploads/' . $cliente['logo'])` cuando el archivo guardado en BD
 * sea solo el nombre (sin subcarpeta) y haya que prefijarlo.
 *
 * Si el valor en BD ya incluye subcarpeta (ej. 'consultores/firmas/x.png'),
 * pasar tipo 'generic' y se respeta tal cual.
 */

if (! function_exists('upload_url')) {
    function upload_url(string $tipo, ?string $file): string
    {
        if (empty($file)) {
            return '';
        }

        // URL absoluta ya resuelta
        if (preg_match('#^(https?:)?//#i', $file)) {
            return $file;
        }
        $file = ltrim($file, '/');
        // Ya viene con 'uploads/...' o con subcarpeta (ej. 'clientes-docs/xxx.png', 'consultores/firmas/xxx.png')
        if (str_starts_with($file, 'uploads/')) {
            return base_url($file);
        }
        if (str_contains($file, '/')) {
            return base_url('uploads/' . $file);
        }

        $mapa = [
            'logo'            => 'clientes-docs/',
            'firma_rl'        => 'clientes-docs/',
            'rut'             => 'clientes-docs/',
            'camara'          => 'clientes-docs/',
            'cedula_rl'       => 'clientes-docs/',
            'oferta'          => 'clientes-docs/',
            'firma_consultor' => 'consultores/firmas/',
            'foto_consultor'  => 'consultores/fotos/',
            'contrato'        => 'contratos/',
            'informe'         => 'informes/pdf/',
            'evidencia'       => 'clientes/',
            'inspeccion'      => 'inspecciones/',
            'firma'           => 'firmas/',
            'branding'        => 'branding/',
            'generic'         => '',
        ];

        $prefix = $mapa[$tipo] ?? '';

        return base_url('uploads/' . $prefix . $file);
    }
}

if (! function_exists('upload_path')) {
    /**
     * Devuelve la ruta absoluta (filesystem) para un tipo/archivo dado.
     * Útil en controladores al leer archivos para embebido en PDFs.
     */
    function upload_path(string $tipo, ?string $file): string
    {
        if (empty($file)) {
            return '';
        }

        $file = ltrim($file, '/');
        if (str_starts_with($file, 'uploads/')) {
            return FCPATH . $file;
        }
        if (str_contains($file, '/')) {
            return FCPATH . 'uploads/' . $file;
        }

        $mapa = [
            'logo'            => UPLOADS_CLIENTES_DOCS,
            'firma_rl'        => UPLOADS_CLIENTES_DOCS,
            'rut'             => UPLOADS_CLIENTES_DOCS,
            'camara'          => UPLOADS_CLIENTES_DOCS,
            'cedula_rl'       => UPLOADS_CLIENTES_DOCS,
            'oferta'          => UPLOADS_CLIENTES_DOCS,
            'firma_consultor' => UPLOADS_CONSULTORES . 'firmas/',
            'foto_consultor'  => UPLOADS_CONSULTORES . 'fotos/',
            'contrato'        => UPLOADS_CONTRATOS,
            'informe'         => UPLOADS_INFORMES,
            'evidencia'       => UPLOADS_CLIENTES,
            'inspeccion'      => UPLOADS_INSPECCIONES,
            'firma'           => UPLOADS_FIRMAS,
            'branding'        => UPLOADS_BRANDING,
            'generic'         => UPLOADS_BASE,
        ];

        $base = $mapa[$tipo] ?? UPLOADS_BASE;

        return $base . $file;
    }
}

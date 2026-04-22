<?php

/**
 * TAT Cycloid — Image Helper
 *
 * Safety net server-side para compresion de imagenes subidas. Complementa
 * public/js/image-compress.js (client-side). Pensado para ejecutarse justo
 * despues de $file->move(...) en controladores con uploads de imagen.
 *
 * - Usa GD (extension nativa de PHP en XAMPP/DigitalOcean)
 * - Respeta EXIF orientation (fotos de movil rotadas)
 * - No toca PDFs u otros formatos
 * - Preserva PNG con alpha <500KB (iconos, screenshots)
 * - Skip si el archivo ya es liviano y pequeno
 *
 * Registrado en app/Config/Autoload.php como helper 'image'.
 */

if (! function_exists('_image_png_has_alpha')) {
    function _image_png_has_alpha(string $path): bool
    {
        // Lee los primeros bytes: byte 25 es el color type en PNG header
        // Color types con alpha: 4 (gray+alpha), 6 (RGBA)
        $fh = @fopen($path, 'rb');
        if (! $fh) {
            return false;
        }
        $header = fread($fh, 26);
        fclose($fh);
        if (strlen($header) < 26) {
            return false;
        }
        $colorType = ord($header[25]);
        return in_array($colorType, [4, 6], true);
    }
}

if (! function_exists('_image_apply_exif_orientation')) {
    /**
     * Rota un GdImage segun el valor de Orientation de EXIF.
     * Valores: 1=normal, 3=180, 6=90CW, 8=90CCW (con flips en 2,4,5,7).
     * Nota: En PHP 8.0+ no hace falta llamar imagedestroy — GC libera.
     */
    function _image_apply_exif_orientation($img, int $orientation)
    {
        switch ($orientation) {
            case 2:
                imageflip($img, IMG_FLIP_HORIZONTAL);
                return $img;
            case 3:
                return imagerotate($img, 180, 0);
            case 4:
                imageflip($img, IMG_FLIP_VERTICAL);
                return $img;
            case 5:
                $r = imagerotate($img, -90, 0);
                imageflip($r, IMG_FLIP_HORIZONTAL);
                return $r;
            case 6:
                return imagerotate($img, -90, 0);
            case 7:
                $r = imagerotate($img, 90, 0);
                imageflip($r, IMG_FLIP_HORIZONTAL);
                return $r;
            case 8:
                return imagerotate($img, 90, 0);
            case 1:
            default:
                return $img;
        }
    }
}

if (! function_exists('compress_uploaded_image')) {

    /**
     * Comprime in-place una imagen subida. Retorna true si la transforma
     * (reescribio el archivo) o si decidio no tocarla (ambos casos OK).
     * Retorna false solo ante errores (archivo inexistente, GD no disponible,
     * formato no soportado, fallo al escribir).
     *
     * @param string $path        Ruta absoluta al archivo
     * @param int    $maxDim      Dimension maxima (lado mayor)
     * @param int    $jpegQuality Calidad JPEG 0-100
     */
    function compress_uploaded_image(string $path, int $maxDim = 1600, int $jpegQuality = 78): bool
    {
        if (! is_file($path) || ! is_readable($path)) {
            log_message('warning', "[image_helper] archivo no legible: {$path}");
            return false;
        }

        if (! function_exists('imagecreatefromjpeg')) {
            log_message('error', '[image_helper] GD no disponible');
            return false;
        }

        $info = @getimagesize($path);
        if ($info === false) {
            // No es imagen (PDF, CSV, etc.) — no tocar, no es error
            return true;
        }

        [$origW, $origH] = $info;
        $origSize   = filesize($path) ?: 0;
        $hasAlpha   = false;
        $sourceType = $info[2] ?? 0;

        // Cargar imagen segun tipo
        $src = null;
        switch ($sourceType) {
            case IMAGETYPE_JPEG:
                $src = @imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $src = @imagecreatefrompng($path);
                $hasAlpha = _image_png_has_alpha($path);
                break;
            case IMAGETYPE_GIF:
                $src = @imagecreatefromgif($path);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $src = @imagecreatefromwebp($path);
                }
                break;
            default:
                // BMP, TIFF, etc. — no soportados, dejar tal cual
                return true;
        }

        if (! $src) {
            log_message('warning', "[image_helper] no se pudo cargar imagen: {$path}");
            return false;
        }

        // Leer EXIF orientation (solo JPEG tiene)
        $orientation = 1;
        if ($sourceType === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            if ($exif !== false && isset($exif['Orientation'])) {
                $orientation = (int) $exif['Orientation'];
            }
        }

        // Rotar segun EXIF
        $src = _image_apply_exif_orientation($src, $orientation);
        $w   = imagesx($src);
        $h   = imagesy($src);

        // Preservar PNG con alpha pequeno (iconos, screenshots transparentes)
        if ($sourceType === IMAGETYPE_PNG && $hasAlpha && $origSize < 500 * 1024) {
            return true;
        }

        // Skip si ya es chica y liviana y no hubo rotacion EXIF
        if ($orientation === 1 && $w <= $maxDim && $h <= $maxDim && $origSize < 400 * 1024) {
            return true;
        }

        // Redimensionar si excede
        $newW = $w;
        $newH = $h;
        if ($w > $maxDim || $h > $maxDim) {
            $ratio = min($maxDim / $w, $maxDim / $h);
            $newW  = (int) round($w * $ratio);
            $newH  = (int) round($h * $ratio);
        }

        $dst = imagecreatetruecolor($newW, $newH);

        // Preservar alpha si aplica (PNG con transparencia grande la guardaremos como PNG)
        $outputAsPng = ($sourceType === IMAGETYPE_PNG && $hasAlpha);

        if ($outputAsPng) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
        } else {
            // Fondo blanco para evitar fondo negro al pasar PNG transparente a JPEG
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $white);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

        // Escribir a archivo temporal y reemplazar atomic-ish
        $tmp = $path . '.tmpcompress';
        $ok  = false;
        if ($outputAsPng) {
            $ok = @imagepng($dst, $tmp, 6);
        } else {
            $ok = @imagejpeg($dst, $tmp, $jpegQuality);
        }

        if (! $ok || ! is_file($tmp)) {
            @unlink($tmp);
            log_message('warning', "[image_helper] fallo al escribir: {$path}");
            return false;
        }

        $newSize = filesize($tmp) ?: 0;

        // Si la "compresion" resulto mas grande (raro pero posible), descartar
        if ($newSize >= $origSize && $orientation === 1 && $w === $newW && $h === $newH) {
            @unlink($tmp);
            return true;
        }

        // Reemplazar original
        if (! @rename($tmp, $path)) {
            @unlink($tmp);
            log_message('warning', "[image_helper] fallo rename: {$path}");
            return false;
        }

        log_message('info', sprintf(
            '[image_helper] %s: %dKB -> %dKB (%dx%d -> %dx%d, orientation=%d)',
            basename($path),
            (int) round($origSize / 1024),
            (int) round($newSize / 1024),
            $origW,
            $origH,
            $newW,
            $newH,
            $orientation
        ));

        return true;
    }
}

<?php
/**
 * Genera favicon.ico desde cycloid_sqe.jpg
 * Uso: php tools/generate_favicon.php
 */

$source = __DIR__ . '/../writable/uploads/cycloid_sqe.jpg';
$output = __DIR__ . '/../public/favicon.ico';

$img = imagecreatefromjpeg($source);
if (!$img) {
    die("No se pudo cargar la imagen\n");
}

$srcW = imagesx($img);
$srcH = imagesy($img);

// Hacer cuadrada tomando el centro
$side = min($srcW, $srcH);
$srcX = (int)(($srcW - $side) / 2);
$srcY = (int)(($srcH - $side) / 2);

// Generar ICO con múltiples tamaños
$sizes = [16, 32, 48];
$icoData = '';
$imageCount = count($sizes);

// ICO header: 6 bytes
$icoData .= pack('vvv', 0, 1, $imageCount);

// Calcular offset inicial de datos de imagen (header + entries)
$dataOffset = 6 + ($imageCount * 16);
$imageDataArray = [];

foreach ($sizes as $size) {
    $resized = imagecreatetruecolor($size, $size);

    // Fondo blanco
    $white = imagecolorallocate($resized, 255, 255, 255);
    imagefill($resized, 0, 0, $white);

    imagecopyresampled($resized, $img, 0, 0, $srcX, $srcY, $size, $size, $side, $side);

    // Capturar PNG en memoria
    ob_start();
    imagepng($resized);
    $pngData = ob_get_clean();
    imagedestroy($resized);

    $imageDataArray[] = $pngData;
}

// Escribir entries del directorio
foreach ($sizes as $i => $size) {
    $pngSize = strlen($imageDataArray[$i]);
    $w = ($size >= 256) ? 0 : $size;
    $h = ($size >= 256) ? 0 : $size;

    $icoData .= pack('CCCCvvVV',
        $w,           // width
        $h,           // height
        0,            // color palette
        0,            // reserved
        1,            // color planes
        32,           // bits per pixel
        $pngSize,     // image data size
        $dataOffset   // offset
    );

    $dataOffset += $pngSize;
}

// Agregar datos de imagen
foreach ($imageDataArray as $pngData) {
    $icoData .= $pngData;
}

imagedestroy($img);

file_put_contents($output, $icoData);
echo "Favicon generado: {$output} (" . strlen($icoData) . " bytes)\n";
echo "Tamaños: " . implode('x, ', $sizes) . "px\n";

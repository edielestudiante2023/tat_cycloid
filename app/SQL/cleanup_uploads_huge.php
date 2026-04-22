<?php
/**
 * Cleanup retroactivo: comprime imagenes >1MB en public/uploads/
 *
 * Uso:
 *   php app/SQL/cleanup_uploads_huge.php          # dry-run (muestra lo que haria)
 *   php app/SQL/cleanup_uploads_huge.php --apply  # aplica la compresion
 *   php app/SQL/cleanup_uploads_huge.php --apply --min=500  # min KB (default 1024)
 *   php app/SQL/cleanup_uploads_huge.php --apply --dir=uploads/inspecciones  # subdir
 *
 * Usa el helper image_helper (GD + EXIF + preserva PNG con alpha).
 */

// Bootstrap CI4 para tener log_message() y el helper image
define('FCPATH', realpath(__DIR__ . '/../../public') . DIRECTORY_SEPARATOR);
define('ROOTPATH', realpath(__DIR__ . '/../..') . DIRECTORY_SEPARATOR);
define('APPPATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

// Cargar el helper (standalone, sin CI4 bootstrap completo para evitar tirar de la BD)
// log_message() no estara disponible; lo stub-eamos
if (!function_exists('log_message')) {
    function log_message(string $level, string $message): void
    {
        // silencioso por default; solo imprimimos en modo verbose (no implementado aqui)
    }
}
require_once APPPATH . 'Helpers/image_helper.php';

// Parsear argumentos
$apply   = false;
$minKB   = 1024;
$subdir  = '';
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--apply') {
        $apply = true;
    } elseif (str_starts_with($arg, '--min=')) {
        $minKB = (int) substr($arg, 6);
    } elseif (str_starts_with($arg, '--dir=')) {
        $subdir = trim(substr($arg, 6), '/\\');
    } elseif ($arg === '--help' || $arg === '-h') {
        fwrite(STDOUT, "Uso: php app/SQL/cleanup_uploads_huge.php [--apply] [--min=KB] [--dir=subdir]\n");
        exit(0);
    }
}

$uploadsDir = FCPATH . 'uploads' . ($subdir ? DIRECTORY_SEPARATOR . $subdir : '');
if (!is_dir($uploadsDir)) {
    fwrite(STDERR, "ERROR: No existe el directorio: {$uploadsDir}\n");
    exit(1);
}

$mode = $apply ? 'APPLY' : 'DRY-RUN';
fwrite(STDOUT, "=== cleanup_uploads_huge ({$mode}) ===\n");
fwrite(STDOUT, "Directorio : {$uploadsDir}\n");
fwrite(STDOUT, "Umbral min : {$minKB} KB\n");
fwrite(STDOUT, str_repeat('-', 60) . "\n");

$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($uploadsDir, FilesystemIterator::SKIP_DOTS)
);

$exts = ['jpg', 'jpeg', 'png', 'webp'];
$totalArchivos  = 0;
$totalCandidatos = 0;
$totalOriginal   = 0;
$totalFinal      = 0;
$fallidos        = 0;

foreach ($iter as $fileInfo) {
    if (!$fileInfo->isFile()) continue;
    $totalArchivos++;

    $ext = strtolower($fileInfo->getExtension());
    if (!in_array($ext, $exts, true)) continue;

    $size = $fileInfo->getSize();
    if ($size < $minKB * 1024) continue;

    $totalCandidatos++;
    $path = $fileInfo->getPathname();
    $rel  = substr($path, strlen(FCPATH));

    if (!$apply) {
        fwrite(STDOUT, sprintf(
            "  [DRY] %8d KB  %s\n",
            (int) round($size / 1024),
            $rel
        ));
        $totalOriginal += $size;
        continue;
    }

    $ok = compress_uploaded_image($path);
    clearstatcache(true, $path);
    $nuevoSize = @filesize($path) ?: $size;

    $totalOriginal += $size;
    $totalFinal    += $nuevoSize;

    if (!$ok) {
        $fallidos++;
        fwrite(STDOUT, sprintf(
            "  [ERR] %s\n",
            $rel
        ));
        continue;
    }

    $ahorro = $size - $nuevoSize;
    fwrite(STDOUT, sprintf(
        "  [OK]  %8d -> %8d KB  (-%6d KB)  %s\n",
        (int) round($size / 1024),
        (int) round($nuevoSize / 1024),
        (int) round($ahorro / 1024),
        $rel
    ));
}

fwrite(STDOUT, str_repeat('-', 60) . "\n");
fwrite(STDOUT, sprintf("Total archivos revisados : %d\n", $totalArchivos));
fwrite(STDOUT, sprintf("Candidatos (> %d KB)      : %d\n", $minKB, $totalCandidatos));

if ($apply) {
    $ahorro = $totalOriginal - $totalFinal;
    fwrite(STDOUT, sprintf("Peso original             : %.2f MB\n", $totalOriginal / 1048576));
    fwrite(STDOUT, sprintf("Peso final                : %.2f MB\n", $totalFinal / 1048576));
    fwrite(STDOUT, sprintf("Espacio ahorrado          : %.2f MB\n", $ahorro / 1048576));
    fwrite(STDOUT, sprintf("Fallidos                  : %d\n", $fallidos));
} else {
    fwrite(STDOUT, sprintf("Peso actual de candidatos : %.2f MB\n", $totalOriginal / 1048576));
    fwrite(STDOUT, "Ejecute con --apply para comprimir.\n");
}

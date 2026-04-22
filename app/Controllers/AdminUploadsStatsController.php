<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * /admin/uploads-stats — estadisticas del directorio public/uploads/.
 *
 * Muestra total de archivos/bytes, top 20 pesados, crecimiento mensual.
 * Solo accesible por admin.
 */
class AdminUploadsStatsController extends Controller
{
    public function index()
    {
        if (!in_array(session()->get('role'), ['admin'], true)) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $uploadsDir = FCPATH . 'uploads';
        if (!is_dir($uploadsDir)) {
            return view('admin/uploads/stats', [
                'error'        => 'No existe el directorio public/uploads/.',
                'totalFiles'   => 0,
                'totalBytes'   => 0,
                'topHeavy'     => [],
                'monthly'      => [],
                'byExt'        => [],
            ]);
        }

        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadsDir, \FilesystemIterator::SKIP_DOTS)
        );

        $totalFiles = 0;
        $totalBytes = 0;
        $heap       = []; // nombre => size
        $monthly    = []; // YYYY-MM => ['count'=>, 'bytes'=>]
        $byExt      = []; // ext => ['count'=>, 'bytes'=>]

        foreach ($iter as $fileInfo) {
            if (!$fileInfo->isFile()) continue;

            $size = $fileInfo->getSize();
            $totalFiles++;
            $totalBytes += $size;

            $rel = substr($fileInfo->getPathname(), strlen(FCPATH));
            $rel = str_replace('\\', '/', $rel);
            $heap[$rel] = $size;

            $ym = date('Y-m', $fileInfo->getMTime());
            if (!isset($monthly[$ym])) {
                $monthly[$ym] = ['count' => 0, 'bytes' => 0];
            }
            $monthly[$ym]['count']++;
            $monthly[$ym]['bytes'] += $size;

            $ext = strtolower($fileInfo->getExtension() ?: '(sin extension)');
            if (!isset($byExt[$ext])) {
                $byExt[$ext] = ['count' => 0, 'bytes' => 0];
            }
            $byExt[$ext]['count']++;
            $byExt[$ext]['bytes'] += $size;
        }

        // Top 20 mas pesados
        arsort($heap);
        $topHeavy = array_slice($heap, 0, 20, true);

        // Orden cronologico ascendente (ultimos 12 meses)
        ksort($monthly);
        $monthly = array_slice($monthly, -12, 12, true);

        // Ordenar extensiones por peso
        uasort($byExt, fn($a, $b) => $b['bytes'] <=> $a['bytes']);

        return view('admin/uploads/stats', [
            'error'      => null,
            'totalFiles' => $totalFiles,
            'totalBytes' => $totalBytes,
            'topHeavy'   => $topHeavy,
            'monthly'    => $monthly,
            'byExt'      => $byExt,
        ]);
    }
}

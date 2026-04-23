<?php
// TAT — Manifest PWA dinámico. Genera las rutas correctas según el entorno
// (local bajo /tat_cycloid/public/ o prod bajo el root del dominio).

header('Content-Type: application/manifest+json; charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate');

// Detectar base URL desde la ruta del script actual.
// Este archivo vive en /public/, así que dirname($_SERVER['SCRIPT_NAME'])
// da exactamente la base (ej: "/tat_cycloid/public" en local, "" en prod).
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$v = 'v=5';

$manifest = [
    'id'               => $basePath . '/client/dashboard',
    'name'             => 'Cycloid TAT - Panel del Cliente',
    'short_name'       => 'Mi SST',
    'description'      => 'Panel de gestion SST para tenderos - Cycloid Tienda a Tienda',
    'start_url'        => $basePath . '/client/dashboard',
    'scope'            => $basePath . '/',
    'display'          => 'standalone',
    'orientation'      => 'portrait',
    'background_color' => '#c9541a',
    'theme_color'      => '#c9541a',
    'categories'       => ['business', 'productivity'],
    'icons' => [
        ['src' => "{$basePath}/icons/icon-192.png?{$v}", 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => "{$basePath}/icons/icon-192.png?{$v}", 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
        ['src' => "{$basePath}/icons/icon-512.png?{$v}", 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => "{$basePath}/icons/icon-512.png?{$v}", 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
];

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

<?php

/**
 * Helper para el módulo Rutinas.
 * Expone el botón flotante (FAB) "volver" que aparece en TODAS las vistas.
 */

if (! function_exists('rutinas_floating_back')) {
    /**
     * Devuelve el HTML del botón FAB "volver al dashboard" adaptado al rol activo.
     * - employee: va a /logout (no tiene dashboard propio más allá del checklist)
     * - client  : va a /dashboard
     * - admin / consultant: va a /admindashboard
     * - sin sesión (checklist público vía email): muestra botón a /login
     */
    function rutinas_floating_back(): string
    {
        $role = session()->get('role');
        $baseUrl = rtrim(base_url(), '/');

        [$href, $title, $icon] = match ($role) {
            'employee'            => [$baseUrl . '/logout',         'Cerrar sesión',      'logout'],
            'client'              => [$baseUrl . '/dashboard',      'Ir al dashboard',    'home'],
            'consultant', 'admin' => [$baseUrl . '/admindashboard', 'Ir al dashboard',    'home'],
            default               => [$baseUrl . '/login',          'Ir al login',        'login'],
        };

        $svgHome = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" viewBox="0 0 576 512"><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40h-16c-1.1 0-2.2 0-3.3-.1-1.4 .1-2.8 .1-4.2 .1H392c-22.1 0-40-17.9-40-40V360c0-17.7-14.3-32-32-32h-64c-17.7 0-32 14.3-32 32v112c0 22.1-17.9 40-40 40h-12c-1.5 0-3-.1-4.5-.2-1 .1-2.1 .2-3.1 .2H88c-22.1 0-40-17.9-40-40v-78.2c0-2.6-.2-5.2-.5-7.8V288H32c-18 0-32-14-32-32.1 0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7l255.4 224.5c8 7 12 15 11 24z"/></svg>';
        $svgLogout = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="white" viewBox="0 0 512 512"><path d="M352 96l64 0c17.7 0 32 14.3 32 32l0 256c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l64 0c53 0 96-43 96-96l0-256c0-53-43-96-96-96l-64 0c-17.7 0-32 14.3-32 32s14.3 32 32 32zm-41.4 201.4c12.5-12.5 12.5-32.8 0-45.3l-128-128c-9.2-9.2-22.9-11.9-34.9-6.9s-19.8 16.6-19.8 29.6l0 64-96 0c-17.7 0-32 14.3-32 32l0 64c0 17.7 14.3 32 32 32l96 0 0 64c0 12.9 7.8 24.6 19.8 29.6s25.7 2.2 34.9-6.9l128-128z"/></svg>';
        $svgLogin  = $svgHome;

        $svg = match ($icon) {
            'logout' => $svgLogout,
            'login'  => $svgLogin,
            default  => $svgHome,
        };

        return '
<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" id="btnRutinasBack" title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" aria-label="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"
   style="position:fixed;bottom:24px;left:24px;z-index:9998;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#c9541a,#ee6c21);color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 15px rgba(0,0,0,0.3);text-decoration:none;transition:transform 0.2s,box-shadow 0.2s;border:2px solid rgba(255,255,255,0.2);">
    ' . $svg . '
</a>
<style>
#btnRutinasBack:hover{transform:scale(1.1);box-shadow:0 6px 20px rgba(0,0,0,0.4);}
#btnRutinasBack:active{transform:scale(0.95);}
@media(max-width:768px){#btnRutinasBack{bottom:20px;left:16px;width:50px;height:50px;}}
</style>';
    }
}

<?php

/**
 * PWA Helper - Funciones reutilizables para soporte PWA en el modulo client.
 */

if (! function_exists('pwa_client_head')) {
    /**
     * Retorna las meta tags PWA para el <head> de las vistas client.
     */
    function pwa_client_head(): string
    {
        $baseUrl = base_url();
        return '
    <meta name="theme-color" content="#c9541a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Mi SST">
    <link rel="manifest" href="' . $baseUrl . 'manifest_client.json?v=2">
    <link rel="apple-touch-icon" href="' . $baseUrl . 'icons/icon-192.png">';
    }
}

if (! function_exists('pwa_client_scripts')) {
    /**
     * Retorna el script de registro del Service Worker, banner offline, boton flotante de volver y listeners.
     */
    function pwa_client_scripts(): string
    {
        $baseUrl = base_url();
        $dashboardUrl = $baseUrl . 'client/dashboard';
        return '
<div id="offlineBanner" style="display:none;position:fixed;top:0;left:0;right:0;background:#ee6c21;color:#fff;text-align:center;padding:8px;z-index:9999;font-weight:600;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:6px;"><path d="M1 1l22 22M16.72 11.06A10.94 10.94 0 0 1 19 12.55M5 12.55a10.94 10.94 0 0 1 5.17-2.39M10.71 5.05A16 16 0 0 1 22.58 9M1.42 9a15.91 15.91 0 0 1 4.7-2.88M8.53 16.11a6 6 0 0 1 6.95 0M12 20h.01"/></svg> Sin conexi&oacute;n - Modo offline
</div>
<a href="' . $dashboardUrl . '" id="btnVolverDashboard" title="Volver al Dashboard" style="position:fixed;bottom:24px;left:24px;z-index:9998;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#c9541a,#ee6c21);color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 15px rgba(0,0,0,0.3);text-decoration:none;font-size:22px;transition:transform 0.2s,box-shadow 0.2s;border:2px solid rgba(255,255,255,0.2);">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" viewBox="0 0 576 512"><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40h-16c-1.1 0-2.2 0-3.3-.1-1.4 .1-2.8 .1-4.2 .1H392c-22.1 0-40-17.9-40-40V360c0-17.7-14.3-32-32-32h-64c-17.7 0-32 14.3-32 32v112c0 22.1-17.9 40-40 40h-12c-1.5 0-3-.1-4.5-.2-1 .1-2.1 .2-3.1 .2H88c-22.1 0-40-17.9-40-40v-78.2c0-2.6-.2-5.2-.5-7.8V288H32c-18 0-32-14-32-32.1 0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7l255.4 224.5c8 7 12 15 11 24z"/></svg>
</a>
<style>
#btnVolverDashboard:hover{transform:scale(1.1);box-shadow:0 6px 20px rgba(0,0,0,0.4);}
#btnVolverDashboard:active{transform:scale(0.95);}
@media(max-width:768px){#btnVolverDashboard{bottom:20px;left:16px;width:50px;height:50px;font-size:20px;}}
</style>
<script>
if("serviceWorker" in navigator){
    window.addEventListener("load",function(){
        navigator.serviceWorker.register("' . $baseUrl . 'sw_client.js",{scope:"' . $baseUrl . '"})
        .then(function(r){console.log("Client SW registered, scope:",r.scope)})
        .catch(function(e){console.log("Client SW error:",e)});
    });
}
window.addEventListener("online",function(){document.getElementById("offlineBanner").style.display="none"});
window.addEventListener("offline",function(){document.getElementById("offlineBanner").style.display="block"});
</script>';
    }
}

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
    <meta name="theme-color" content="#1b4332">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Mi SST">
    <link rel="manifest" href="' . $baseUrl . 'manifest_client.json?v=1">
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
<div id="offlineBanner" style="display:none;position:fixed;top:0;left:0;right:0;background:#e76f51;color:#fff;text-align:center;padding:8px;z-index:9999;font-weight:600;">
    <i class="fas fa-wifi-slash"></i> Sin conexi&oacute;n - Modo offline
</div>
<a href="' . $dashboardUrl . '" id="btnVolverDashboard" title="Volver al Dashboard" style="position:fixed;bottom:24px;left:24px;z-index:9998;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#1b4332,#2d6a4f);color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 15px rgba(0,0,0,0.3);text-decoration:none;font-size:22px;transition:transform 0.2s,box-shadow 0.2s;border:2px solid rgba(255,255,255,0.2);">
    <i class="fas fa-home"></i>
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

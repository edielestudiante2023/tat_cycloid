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
     * Retorna el script de registro del Service Worker, banner offline y listeners.
     */
    function pwa_client_scripts(): string
    {
        $baseUrl = base_url();
        return '
<div id="offlineBanner" style="display:none;position:fixed;top:0;left:0;right:0;background:#e76f51;color:#fff;text-align:center;padding:8px;z-index:9999;font-weight:600;">
    <i class="fas fa-wifi-slash"></i> Sin conexi&oacute;n - Modo offline
</div>
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

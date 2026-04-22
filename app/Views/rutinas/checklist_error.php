<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checklist — Error</title>
<style>
body{margin:0;background:#f4f6f9;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
.box{background:#fff;max-width:420px;width:100%;padding:30px;border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.08);text-align:center}
.icon{font-size:3rem;color:#dc3545}
h1{color:#1c2437;font-size:1.3rem;margin:15px 0 8px}
p{color:#666}
</style>
</head>
<body>
<div class="box">
    <div class="icon">⚠️</div>
    <h1>No se pudo abrir el checklist</h1>
    <p><?= esc($mensaje ?? 'Enlace inválido.') ?></p>
</div>
<?php helper('rutinas'); echo rutinas_floating_back(); ?>
</body>
</html>

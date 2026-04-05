<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1b4332">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Mi SST">
    <link rel="manifest" href="<?= base_url('manifest_client.json?v=1') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('icons/icon-192.png') ?>">
    <title><?= esc($title ?? 'Inspecciones') ?> - Cycloid TAT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #1b4332;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 3px solid #e76f51;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
        }
        .navbar-content img {
            max-height: 70px;
            transition: transform 0.3s ease;
        }
        .navbar-content img:hover {
            transform: scale(1.05);
        }
        .content-wrapper {
            margin-top: 120px;
            padding-bottom: 60px;
        }
        .page-header {
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .page-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
        }
        .btn-back {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.25);
            color: white;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .card-title {
            font-size: 14px;
            color: #999;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-pdf {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
            color: white;
        }
        footer {
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            color: #ffffff;
            padding: 15px 0;
            margin-top: 2rem;
        }
        @media (max-width: 768px) {
            .navbar-content img { max-height: 50px; }
            .content-wrapper { margin-top: 100px; }
            .page-header h1 { font-size: 1.3rem; }
            .page-header { padding: 1rem 1.2rem; flex-direction: column; gap: 10px; text-align: center; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="https://dashboard.cycloidtalent.com/login" target="_blank">
                <img src="<?= base_url('uploads/logocycloid_tatblancoslogan.png') ?>" alt="Logo Cycloid TAT">
            </a>
            <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank">
                <img src="<?= base_url('uploads/logosst.png') ?>" alt="Logo SST">
            </a>
            <a href="https://cycloidtalent.com/" target="_blank">
                <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Logo Cycloid">
            </a>
        </div>
    </nav>

    <div class="content-wrapper">
        <div class="container">
            <?= $content ?>
        </div>
    </div>

    <footer class="text-center">
        <p class="mb-0">&copy; <?= date('Y') ?> Cycloid Talent SAS. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- PWA: Banner offline + Boton volver + Service Worker -->
    <div id="offlineBanner" style="display:none;position:fixed;top:0;left:0;right:0;background:#e76f51;color:#fff;text-align:center;padding:8px;z-index:9999;font-weight:600;">
        <i class="fas fa-wifi-slash"></i> Sin conexi&oacute;n - Modo offline
    </div>
    <a href="<?= base_url('client/dashboard') ?>" id="btnVolverDashboard" title="Volver al Dashboard" style="position:fixed;bottom:24px;left:24px;z-index:9998;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#1b4332,#2d6a4f);color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 15px rgba(0,0,0,0.3);text-decoration:none;font-size:22px;transition:transform 0.2s,box-shadow 0.2s;border:2px solid rgba(255,255,255,0.2);">
        <i class="fas fa-home"></i>
    </a>
    <style>
    #btnVolverDashboard:hover{transform:scale(1.1);box-shadow:0 6px 20px rgba(0,0,0,0.4);}
    #btnVolverDashboard:active{transform:scale(0.95);}
    @media(max-width:768px){#btnVolverDashboard{bottom:20px;left:16px;width:50px;height:50px;font-size:20px;}}
    </style>
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('<?= base_url("sw_client.js") ?>', {
                scope: '<?= base_url() ?>'
            });
        });
    }
    window.addEventListener('online', function() { document.getElementById('offlineBanner').style.display = 'none'; });
    window.addEventListener('offline', function() { document.getElementById('offlineBanner').style.display = 'block'; });
    </script>
</body>
</html>

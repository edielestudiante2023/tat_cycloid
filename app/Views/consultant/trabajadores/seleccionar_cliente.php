<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trabajadores — Seleccionar Cliente</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .main-card {
            max-width: 700px; margin: 60px auto;
            border: none; border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            overflow: hidden;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #c9541a, #ee6c21);
            color: white; padding: 28px 32px; text-align: center;
        }
        .card-header-custom h2 { margin: 0; font-weight: 700; font-size: 1.5rem; }
        .card-header-custom p { margin: 8px 0 0; opacity: .8; font-size: .9rem; }
        .card-body-custom { padding: 32px; }
        .btn-ir {
            background: linear-gradient(135deg, #ee6c21, #ff8d4e);
            border: none; color: #fff; font-weight: 600;
            padding: 12px 32px; border-radius: 8px; font-size: 1rem;
            transition: all .3s;
        }
        .btn-ir:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(238,108,33,.4); color: #fff; }
        .btn-ir:disabled { opacity: .5; transform: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card card">
            <div class="card-header-custom">
                <h2><i class="fas fa-users me-2"></i>Trabajadores</h2>
                <p>Gestión de personal por establecimiento comercial</p>
            </div>
            <div class="card-body-custom">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                <?php endif; ?>

                <div class="mb-4">
                    <label for="selectCliente" class="form-label fw-semibold">
                        <i class="fas fa-building me-1"></i> Seleccionar Cliente
                    </label>
                    <select id="selectCliente" class="form-select" style="width:100%;">
                        <option value="">Buscar cliente...</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>">
                                <?= esc($c['nombre_cliente']) ?>
                                <?php if (!empty($c['nit_cliente'])): ?> — NIT: <?= esc($c['nit_cliente']) ?><?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="<?= base_url('dashboardconsultant') ?>" class="text-muted text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
                    </a>
                    <button id="btnIr" type="button" class="btn btn-ir" disabled>
                        <i class="fas fa-arrow-right me-1"></i> Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(function() {
        $('#selectCliente').select2({ theme: 'bootstrap-5', placeholder: 'Buscar cliente...' });
        $('#selectCliente').on('change', function() {
            $('#btnIr').prop('disabled', !this.value);
        });
        $('#btnIr').on('click', function() {
            var idCliente = $('#selectCliente').val();
            if (idCliente) {
                window.location.href = '<?= base_url('client/trabajadores/cliente') ?>/' + idCliente;
            }
        });
    });
    </script>
</body>
</html>

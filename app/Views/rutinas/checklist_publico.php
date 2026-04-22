<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
<title>Checklist de rutinas</title>
<link rel="manifest" href="<?= base_url('manifest_rutinas.json') ?>">
<meta name="theme-color" content="#1c2437">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
*{box-sizing:border-box}
html,body{margin:0;padding:0;background:#f4f6f9;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.wrap{max-width:540px;margin:0 auto;padding:14px}
.header{background:linear-gradient(135deg,#1c2437,#2d3a5e);color:#fff;padding:24px 20px;border-radius:14px;text-align:center;margin-bottom:14px}
.header h1{color:#bd9751;font-size:1.4rem;margin:0 0 4px}
.header .sub{color:#e0e0e0;font-size:.9rem}
.card{background:#fff;border-radius:12px;padding:18px;margin-bottom:10px;box-shadow:0 2px 8px rgba(0,0,0,.05);display:flex;align-items:flex-start;gap:12px}
.card input[type=checkbox]{width:26px;height:26px;flex-shrink:0;accent-color:#bd9751;margin-top:2px}
.card .body{flex:1}
.card .name{font-weight:600;color:#1c2437;font-size:1rem;margin-bottom:3px}
.card .desc{color:#666;font-size:.85rem}
.card .peso{display:inline-block;background:#f6f2e8;color:#bd9751;font-weight:700;font-size:.72rem;padding:2px 8px;border-radius:10px;margin-top:4px;border:1px solid #e7dfc6}
.universo-badge{background:#bd9751;color:#1c2437;font-weight:700;font-size:.78rem;padding:6px 12px;border-radius:18px;display:inline-block;margin-top:10px}
.card.done{background:#e6f7ec;border-left:4px solid #28a745}
.card.done .name{text-decoration:line-through;color:#155724}
.card.pending-sync{border-left:4px solid #ffc107}
.card.pending-sync .sync-label{color:#856404;font-size:.75rem;margin-top:4px;font-weight:600}
.toast{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#1c2437;color:#fff;padding:12px 20px;border-radius:8px;font-size:.9rem;opacity:0;transition:opacity .3s;z-index:9999}
.toast.show{opacity:1}
.offline-bar{background:#ffc107;color:#1c2437;padding:10px;text-align:center;font-size:.85rem;font-weight:600;border-radius:8px;margin-bottom:10px;display:none}
.offline-bar.show{display:block}
.actions{display:flex;gap:8px;margin-top:14px;flex-wrap:wrap}
.actions .btn-report{flex:1;padding:16px 14px;border-radius:10px;border:0;background:#bd9751;color:#1c2437;text-align:center;font-size:1rem;font-weight:700;transition:transform .15s,background .2s;cursor:pointer}
.actions .btn-report:active{transform:scale(.97)}
.actions .btn-report:disabled{background:#b0b0b0;color:#fff;cursor:not-allowed}
.actions .btn-report.sent{background:#28a745;color:#fff}
.report-hint{font-size:.8rem;color:#666;text-align:center;margin-top:6px}
.progress-box{background:#fff;border-radius:12px;padding:14px;margin-top:10px;box-shadow:0 2px 8px rgba(0,0,0,.05);text-align:center}
.progress-box .pct{font-size:1.6rem;font-weight:700;color:#1c2437}
.progress-box .pct.done{color:#28a745}
.progress-box .lbl{font-size:.8rem;color:#666;margin-top:2px}
.footer{text-align:center;color:#999;font-size:.75rem;padding:20px 0}
@media (min-width: 576px) { .wrap{padding:20px} .header{padding:28px} }
</style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1><i class="fa-solid fa-list-check"></i> Rutinas del día</h1>
        <div class="sub"><?= esc($usuario['nombre_completo'] ?? 'Usuario') ?></div>
        <div class="sub"><?= date('d/m/Y', strtotime($fecha)) ?></div>
        <?php if (!empty($pesoTotal)): ?>
            <div class="universo-badge">
                Meta del día: <?= number_format($pesoTotal, 2) ?> pts = 100%
            </div>
        <?php endif; ?>
    </div>

    <div id="offlineBar" class="offline-bar">
        <i class="fa-solid fa-wifi"></i> Sin conexión — tus marcas se guardan y se sincronizan al recuperar red.
    </div>

    <?php if (empty($actividades)): ?>
        <div class="card"><div class="body">No tienes actividades asignadas.</div></div>
    <?php else: ?>
        <?php foreach ($actividades as $a): $done = !empty($ya[(int)$a['id_actividad']]); ?>
            <div class="card <?= $done ? 'done' : '' ?>" data-id="<?= (int)$a['id_actividad'] ?>" data-peso="<?= esc($a['peso']) ?>">
                <input type="checkbox" class="chk" <?= $done ? 'checked disabled' : '' ?>>
                <div class="body">
                    <div class="name"><?= esc($a['nombre']) ?></div>
                    <?php if (!empty($a['descripcion'])): ?>
                        <div class="desc"><?= esc($a['descripcion']) ?></div>
                    <?php endif; ?>
                    <div class="peso"><i class="fa-solid fa-weight-scale"></i> <?= number_format((float)$a['peso'], 2) ?> pts</div>
                    <div class="sync-label" style="display:none;"><i class="fa-solid fa-clock"></i> Pendiente de sincronizar</div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="progress-box">
            <div class="pct" id="progressPct">0%</div>
            <div class="lbl" id="progressLbl">
                <span id="pctPts">0.00</span> / <?= number_format($pesoTotal, 2) ?> pts
                · <span id="pctCount">0</span> / <?= count($actividades) ?> actividades
            </div>
        </div>
    <?php endif; ?>

    <div class="actions">
        <button type="button" id="btnReportar" class="btn-report">
            <i class="fa-solid fa-paper-plane"></i> <span id="btnReportarLabel">Terminar y reportar rutina</span>
        </button>
    </div>
    <p class="report-hint">
        <i class="fa-solid fa-circle-info"></i>
        Al terminar, presiona el botón para notificar al consultor y al propietario.
    </p>

    <div class="footer">Cycloid Talent · Checklist seguro con token del día</div>
</div>

<div id="toast" class="toast"></div>

<script src="<?= base_url('js/offline_queue.js') ?>"></script>
<script>
const USER_ID    = <?= (int)$usuario['id_usuario'] ?>;
const FECHA      = <?= json_encode($fecha) ?>;
const TOKEN      = <?= json_encode($token) ?>;
const UPDATE_URL = <?= json_encode(base_url('rutinas/checklist/update')) ?>;
const REPORT_URL = <?= json_encode(base_url('rutinas/checklist/reportar')) ?>;

function toast(msg, isError){
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = isError ? '#dc3545' : '#1c2437';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

function updateOfflineBar(){
    document.getElementById('offlineBar').classList.toggle('show', !navigator.onLine);
}
window.addEventListener('online', updateOfflineBar);
window.addEventListener('offline', updateOfflineBar);
updateOfflineBar();

function actualizarProgreso(){
    const cards = document.querySelectorAll('.card[data-id]');
    const total = cards.length;
    if (total === 0) return;
    let pesoTotal = 0, pesoHechas = 0, hechas = 0;
    cards.forEach(c => {
        const p = parseFloat(c.dataset.peso || '0') || 0;
        pesoTotal += p;
        if (c.classList.contains('done')) { pesoHechas += p; hechas++; }
    });
    const pct = pesoTotal > 0 ? Math.round((pesoHechas / pesoTotal) * 100) : 0;
    const pctEl = document.getElementById('progressPct');
    if (pctEl) {
        pctEl.textContent = pct + '%';
        pctEl.classList.toggle('done', pct === 100);
    }
    const ptsEl = document.getElementById('pctPts');
    const cntEl = document.getElementById('pctCount');
    if (ptsEl) ptsEl.textContent = pesoHechas.toFixed(2);
    if (cntEl) cntEl.textContent = hechas;
    if (pct === 100 && !window._completedShown) {
        window._completedShown = true;
        setTimeout(() => toast('✅ ¡Rutinas del día completas!'), 500);
    }
}
actualizarProgreso();

async function enviarCheck(idActividad, cardEl){
    const body = new FormData();
    body.append('user_id', USER_ID);
    body.append('fecha', FECHA);
    body.append('token', TOKEN);
    body.append('id_actividad', idActividad);

    try {
        const res = await fetch(UPDATE_URL, { method: 'POST', body });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        if (data.success) {
            cardEl.classList.add('done');
            cardEl.classList.remove('pending-sync');
            cardEl.querySelector('.sync-label').style.display = 'none';
            cardEl.querySelector('.chk').disabled = true;
            toast(data.duplicate ? 'Ya estaba marcada' : '¡Actividad marcada!');
            actualizarProgreso();
            return true;
        }
        toast(data.message || 'Error', true);
        return false;
    } catch (e) {
        // Offline o error de red → encolar
        await OfflineQueue.add({
            type: 'rutina_check',
            url: UPDATE_URL,
            payload: { user_id: USER_ID, fecha: FECHA, token: TOKEN, id_actividad: idActividad },
            id_asistencia: -1,
            meta: { idActividad, fecha: FECHA }
        });
        cardEl.classList.add('pending-sync');
        cardEl.querySelector('.sync-label').style.display = 'block';
        toast('Sin red — guardado en cola local');
        return false;
    }
}

document.querySelectorAll('.chk').forEach(chk => {
    chk.addEventListener('change', async function(){
        if (!this.checked) return;
        this.disabled = true;
        const card = this.closest('.card');
        const id = parseInt(card.dataset.id, 10);
        const ok = await enviarCheck(id, card);
        if (!ok && !card.classList.contains('pending-sync')) {
            this.disabled = false;
            this.checked = false;
        }
    });
});

// Botón "Terminar y reportar rutina"
const btnReportar = document.getElementById('btnReportar');
if (btnReportar) {
    btnReportar.addEventListener('click', async function(){
        // Si hay pendientes offline, intentar sincronizar primero
        if (!navigator.onLine) {
            toast('Sin red — conecta para reportar', true);
            return;
        }
        const pending = await OfflineQueue.count();
        if (pending > 0) {
            toast('Sincronizando ' + pending + ' pendientes…');
            await OfflineQueue.syncAll();
        }

        btnReportar.disabled = true;
        const label = document.getElementById('btnReportarLabel');
        label.textContent = 'Enviando reporte…';

        try {
            const body = new FormData();
            body.append('user_id', USER_ID);
            body.append('fecha', FECHA);
            body.append('token', TOKEN);

            const res = await fetch(REPORT_URL, { method: 'POST', body });
            const data = await res.json();

            if (data.success) {
                btnReportar.classList.add('sent');
                label.textContent = '✓ Reporte enviado (' + (data.pct ?? '--') + '%)';
                toast('Reporte enviado al consultor y propietario');
            } else {
                label.textContent = 'Terminar y reportar rutina';
                btnReportar.disabled = false;
                toast(data.message || 'No se pudo reportar', true);
            }
        } catch (e) {
            label.textContent = 'Terminar y reportar rutina';
            btnReportar.disabled = false;
            toast('Error de red al reportar', true);
        }
    });
}

// Sincronizar automáticamente al recuperar conexión
OfflineQueue.startOnlineListener(function(result){
    if (result.synced > 0) {
        toast(result.synced + ' actividad(es) sincronizada(s)');
        // Repintar las que se marcaron offline
        document.querySelectorAll('.card.pending-sync').forEach(card => {
            card.classList.remove('pending-sync');
            card.classList.add('done');
            card.querySelector('.sync-label').style.display = 'none';
        });
    }
});

// Intentar sincronizar al cargar si hay pendientes
(async function(){
    const pending = await OfflineQueue.count();
    if (pending > 0 && navigator.onLine) {
        const result = await OfflineQueue.syncAll();
        if (result.synced > 0) toast(result.synced + ' sincronizada(s) al abrir');
    }
})();

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= base_url('sw_rutinas.js') ?>').catch(()=>{});
}
</script>
<?php helper('rutinas'); echo rutinas_floating_back(); ?>
</body>
</html>

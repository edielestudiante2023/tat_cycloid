<div class="page-header">
    <h1><i class="fas fa-clipboard-check me-2"></i> Mis Inspecciones</h1>
    <a href="<?= base_url('/dashboard') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<!-- Barra de busqueda -->
<div class="ins-search-wrapper mb-3">
    <div class="ins-search-box">
        <i class="fas fa-search ins-search-icon"></i>
        <input type="text" id="insSearchInput" class="ins-search-input" placeholder="Buscar tipo de inspección..." autocomplete="off" />
        <button type="button" id="insSearchClear" class="ins-search-clear" aria-label="Limpiar busqueda" style="display:none;">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<!-- Mensaje sin resultados -->
<div id="insNoResults" class="alert alert-warning text-center" style="display:none;">
    <i class="fas fa-search-minus me-2"></i> No se encontraron resultados para "<span id="insNoResultsTerm"></span>".
</div>

<div class="row g-3" id="insCardsGrid">
    <?php foreach ($tipos as $tipo): ?>
    <div class="col-12 col-md-6 col-lg-4 ins-card-col">
        <a href="<?= $tipo['url'] ?>" class="ins-card-link">
            <div class="ins-card" style="border-left: 4px solid <?= $tipo['color'] ?>;">
                <div class="ins-icon" style="background:<?= $tipo['color'] ?>;">
                    <i class="fas <?= $tipo['icono'] ?>"></i>
                </div>
                <div class="ins-body">
                    <h5 class="ins-title"><?= esc($tipo['nombre']) ?></h5>
                    <?php if (!empty($tipo['es_dashboard'])): ?>
                        <div class="ins-meta ins-meta-secondary" style="color:<?= $tipo['color'] ?>;">
                            <i class="fas fa-table me-1"></i> Ver consolidado
                        </div>
                        <small class="ins-sub">8 indicadores de saneamiento</small>
                    <?php else: ?>
                        <div class="ins-meta" style="color:<?= $tipo['color'] ?>;">
                            <?= $tipo['conteo'] ?>
                        </div>
                        <small class="ins-sub">
                            inspecciones completadas<?php if ($tipo['ultima']): ?> <span class="ins-date-sep">·</span> <i class="fas fa-calendar-alt"></i> Última: <?= date('d/m/Y', strtotime($tipo['ultima'][$tipo['campo_fecha']])) ?><?php endif; ?>
                        </small>
                    <?php endif; ?>
                </div>
                <div class="ins-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<?php if (array_sum(array_filter(array_column($tipos, 'conteo'))) === 0): ?>
<div class="text-center mt-4">
    <div class="card">
        <div class="card-body py-5">
            <i class="fas fa-clipboard-list" style="font-size:3rem; color:#ccc;"></i>
            <h5 class="mt-3 text-muted">Aún no hay inspecciones completadas</h5>
            <p class="text-muted">Cuando su consultor finalice inspecciones, aparecerán aquí.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Barra de busqueda */
.ins-search-wrapper { max-width: 500px; margin-left: auto; margin-right: auto; }
.ins-search-box {
    position: relative;
    display: flex;
    align-items: center;
    background: #fff;
    border: 2px solid #e9ecef;
    border-radius: 30px;
    padding: 0.55rem 1rem 0.55rem 2.5rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.ins-search-box:focus-within {
    border-color: #ee6c21;
    box-shadow: 0 0 0 0.15rem rgba(238, 108, 33, 0.15);
}
.ins-search-icon {
    position: absolute;
    left: 1rem;
    color: #ee6c21;
    font-size: 0.95rem;
}
.ins-search-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.95rem;
    color: #333;
    padding: 0;
}
.ins-search-input::placeholder { color: #adb5bd; }
.ins-search-clear {
    background: #f1f3f5;
    border: none;
    color: #6c757d;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.75rem;
    transition: all 0.2s;
    flex-shrink: 0;
}
.ins-search-clear:hover { background: #e9ecef; color: #c9541a; }

/* Desktop (default): card vertical, centrado */
.ins-card-link {
    text-decoration: none;
    display: block;
    height: 100%;
}
.ins-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    padding: 1.5rem 1rem;
    height: 100%;
    transition: all 0.3s ease;
    text-align: center;
    cursor: pointer;
}
.ins-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}
.ins-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem auto;
}
.ins-icon i { color: #fff; font-size: 1.5rem; }
.ins-body { min-width: 0; }
.ins-title {
    color: #c9541a;
    font-weight: 700;
    font-size: 1.15rem;
    margin: 0 0 0.5rem 0;
}
.ins-meta {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}
.ins-meta-secondary {
    font-size: 1rem;
    font-weight: 600;
    margin-top: 0.5rem;
}
.ins-sub { color: #999; font-size: 0.85rem; display: block; }
.ins-arrow { display: none; }

/* Mobile: layout horizontal tipo lista */
@media (max-width: 767.98px) {
    .ins-card {
        display: flex;
        flex-direction: row;
        align-items: center;
        text-align: left;
        padding: 0.75rem 0.9rem;
        gap: 0.85rem;
    }
    .ins-card:hover { transform: none; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
    .ins-icon {
        width: 44px;
        height: 44px;
        margin: 0;
        flex-shrink: 0;
    }
    .ins-icon i { font-size: 1.05rem; }
    .ins-body { flex: 1; min-width: 0; }
    .ins-title {
        font-size: 0.98rem;
        margin: 0 0 1px 0;
        line-height: 1.2;
    }
    .ins-meta, .ins-meta-secondary {
        display: inline;
        font-size: 0.82rem;
        font-weight: 600;
        margin: 0;
    }
    .ins-sub { font-size: 0.72rem; line-height: 1.2; }
    .ins-arrow {
        display: flex;
        align-items: center;
        color: #c9541a;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
}
</style>

<script>
(function() {
    var input = document.getElementById('insSearchInput');
    var clearBtn = document.getElementById('insSearchClear');
    var grid = document.getElementById('insCardsGrid');
    var noResults = document.getElementById('insNoResults');
    var noResultsTerm = document.getElementById('insNoResultsTerm');
    if (!input || !grid) return;

    var cols = grid.querySelectorAll('.ins-card-col');

    function normalize(s) {
        return (s || '').toString().toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, ''); // quita tildes
    }

    function filter() {
        var term = normalize(input.value.trim());
        clearBtn.style.display = input.value.length > 0 ? 'flex' : 'none';

        var visibles = 0;
        cols.forEach(function(col) {
            var titleEl = col.querySelector('.ins-title');
            var title = normalize(titleEl ? titleEl.textContent : '');
            var match = !term || title.indexOf(term) !== -1;
            col.style.display = match ? '' : 'none';
            if (match) visibles++;
        });

        if (term && visibles === 0) {
            noResultsTerm.textContent = input.value.trim();
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    }

    input.addEventListener('input', filter);
    clearBtn.addEventListener('click', function() {
        input.value = '';
        input.focus();
        filter();
    });
})();
</script>

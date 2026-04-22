<?php
/**
 * Partial reutilizable: modal de solicitud de anulación + script.
 * Incluir UNA sola vez por vista. Bootstrap 5 JS bundle requerido.
 *
 * Uso en los botones de eliminar:
 *   <button class="btn btn-sm btn-outline-danger" data-anular-url="<?= base_url('client/xxx/' . $id . '/eliminar') ?>"
 *           data-anular-titulo="Registro del 12/05">
 *     <i class="fas fa-ban"></i>
 *   </button>
 */
?>
<div class="modal fade" id="modalAnulacionTat" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" id="formAnulacionTat">
      <div class="modal-content">
        <div class="modal-header" style="background:#ee6c21;color:#fff;">
          <h5 class="modal-title"><i class="fas fa-ban me-1"></i> Solicitar anulación</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning small mb-3">
            <i class="fas fa-info-circle me-1"></i>
            Tu solicitud será enviada al consultor asignado. El registro
            <strong>no se eliminará</strong> hasta que el consultor apruebe la anulación.
            Recibirás un correo con la respuesta.
          </div>
          <div class="mb-2">
            <small class="text-muted">Registro:</small>
            <div id="anulacionTituloRegistro" class="fw-bold"></div>
          </div>
          <label class="form-label">Justificación <span class="text-danger">*</span>
            <small class="text-muted">(mínimo 20 caracteres)</small>
          </label>
          <textarea name="justificacion" class="form-control" rows="4" required minlength="20"
                    placeholder="Explica al consultor por qué debe anularse este registro…"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn" style="background:#ee6c21;color:#fff;">
            <i class="fas fa-paper-plane me-1"></i> Enviar solicitud
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-anular-url]');
    if (!btn) return;
    e.preventDefault();
    document.getElementById('formAnulacionTat').action = btn.dataset.anularUrl;
    document.getElementById('anulacionTituloRegistro').textContent = btn.dataset.anularTitulo || '';
    new bootstrap.Modal(document.getElementById('modalAnulacionTat')).show();
});
</script>

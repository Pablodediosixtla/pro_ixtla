<section class="page-head">
  <div>
    <p class="muted">Administra categorías de <b>entrada</b> y <b>salida</b>. Cada sub-item queda ligado a un solo tipo de movimiento.</p>
  </div>
  <button class="btn btn-primary" id="newSubitemBtn">Nuevo sub-item</button>
</section>
<section class="panel expandable-module-panel subitem-module-panel">
  <div class="panel-head">
    <div><span class="eyebrow">CATÁLOGO</span><h2>Sub-items presupuestales</h2><p class="muted">Toca un registro para consultar su detalle o editarlo.</p></div>
    <span class="badge neutral" id="subitemCount">0 registros</span>
  </div>
  <div id="subitemList" class="expandable-list subitem-expandable-list"><div class="empty-state">Cargando sub-items…</div></div>
</section>
<div class="modal hidden" id="subitemModal">
  <div class="modal-card">
    <div class="modal-head"><div><span class="eyebrow">CATÁLOGO</span><h2>Sub-item presupuestal</h2></div><button class="icon-btn" data-close-modal>✕</button></div>
    <form id="subitemForm" class="form-grid">
      <input type="hidden" name="subitem_id">
      <label>Código<input name="codigo" required></label>
      <label>Nombre<input name="nombre" required></label>
      <label>Tipo de sub-item
        <select name="tipo" required>
          <option value="SALIDA">Salida</option>
          <option value="ENTRADA">Entrada</option>
        </select>
      </label>
      <label>Departamento<select name="departamento_id" id="subitemDepartment"><option value="">Global / Todos</option></select></label>
      <label class="full">Descripción<textarea name="descripcion" rows="3"></textarea></label>
      <label>Estatus<select name="estatus"><option>ACTIVO</option><option>INACTIVO</option></select></label>
      <div class="info-box full"><b>Entrada</b> se utiliza únicamente en Pagos. <b>Salida</b> se utiliza en Solicitudes, registro de salidas y resumen de gasto por subcategoría.</div>
      <div class="form-actions full"><button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button><button class="btn btn-primary" type="submit">Guardar</button></div>
    </form>
  </div>
</div>

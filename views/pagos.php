<section class="page-head payments-page-head">
  <div>
    <span class="eyebrow">ENTRADAS DE RECURSO</span>
    <p class="muted">Registra ingresos de dinero por departamento y clasifícalos por sub-item de entrada para mantener trazabilidad presupuestal.</p>
  </div>
  <div class="page-actions">
    <label class="compact-control">Ejercicio<select id="paymentYear"></select></label>
    <button class="btn btn-primary" id="newPaymentBtn">Registrar entrada</button>
  </div>
</section>

<div class="payment-kpi-grid">
  <article><span class="payment-kpi-icon">↗</span><div><small>Entradas vigentes</small><strong id="paymentTotal">$0.00</strong></div></article>
  <article><span class="payment-kpi-icon">▤</span><div><small>Registros</small><strong id="paymentCount">0</strong></div></article>
  <article><span class="payment-kpi-icon"><?=nav_icon('building')?></span><div><small>Departamentos con entrada</small><strong id="paymentDepartmentCount">0</strong></div></article>
</div>

<section class="panel expandable-module-panel">
  <div class="panel-head">
    <div><span class="eyebrow">PAGOS</span><h2>Entradas registradas</h2></div>
    <span class="badge success">Tesorería · Presidencia · Admin</span>
  </div>
  <div class="filter-row">
    <label>Departamento<select id="paymentDepartment"><option value="">Todos</option></select></label>
    <label class="filter-grow">Buscar<input id="paymentSearch" placeholder="Folio, concepto o referencia"></label>
  </div>
  <div id="paymentList" class="expandable-list"></div>
</section>

<div class="modal hidden" id="paymentModal">
  <div class="modal-card modal-wide">
    <div class="modal-head"><div><span class="eyebrow">NUEVA ENTRADA</span><h2>Registrar ingreso de recurso</h2></div><button class="icon-btn" data-close-modal>✕</button></div>
    <form id="paymentForm" class="form-grid">
      <label>Departamento<select name="departamento_id" id="paymentFormDepartment" required></select></label>
      <label>Sub-item de entrada<select name="subitem_id" id="paymentSubitem"><option value="">Sin sub-item</option></select></label>
      <label>Fecha<input type="date" name="fecha" id="paymentDate" required></label>
      <label>Monto<input type="number" name="monto" min="0.01" step="0.01" required></label>
      <label class="full">Origen / concepto<input name="concepto" required placeholder="Ej. Aportación extraordinaria, recuperación, convenio..."></label>
      <label>Método<select name="metodo_pago"><option value="TRANSFERENCIA">Transferencia</option><option value="EFECTIVO">Efectivo</option><option value="CHEQUE">Cheque</option><option value="TARJETA">Tarjeta</option><option value="OTRO">Otro</option></select></label>
      <label>Referencia<input name="referencia" placeholder="Folio bancario o referencia"></label>
      <div class="info-box full">La entrada incrementa el disponible del departamento seleccionado y queda registrada en movimientos y bitácora.</div>
      <div class="form-actions full"><button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button><button type="submit" class="btn btn-primary">Guardar entrada</button></div>
    </form>
  </div>
</div>

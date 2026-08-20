<?php $preDepartment=(int)($_GET['departamento_id']??0); $preType=strtoupper((string)($_GET['tipo']??'SALIDA')); ?>
<div class="movement-layout">
  <section class="panel form-panel">
    <div class="panel-head"><div><h2>Nueva entrada / salida</h2><p class="muted">Completa los datos y el sistema generará el folio automáticamente.</p></div></div>
    <form id="movementForm" class="form-grid" enctype="multipart/form-data" data-pre-department="<?= $preDepartment ?>" data-pre-type="<?= htmlspecialchars($preType) ?>">
      <label>Tipo de movimiento<select name="tipo" required><option value="SALIDA">Salida de recurso</option><option value="ENTRADA">Entrada de presupuesto</option></select></label>
      <label>Fecha<input name="fecha" type="date" value="<?= date('Y-m-d') ?>" required></label>
      <label class="span-2">Departamento<select name="departamento_id" required><option value="">Selecciona un departamento</option></select></label>
      <label>Monto<input name="monto" type="number" min="0.01" step="0.01" required placeholder="0.00"></label>
      <label class="subitem-field">Sub-item<select name="subitem_id"><option value="">Selecciona una opción</option></select></label>
      <label class="span-2">Concepto / Uso<textarea name="concepto" rows="3" required placeholder="Describe el motivo del movimiento..."></textarea></label>
      <label class="output-field">Entregado a<input name="entregado_a" placeholder="Nombre de quien recibe"></label>
      <label class="output-field">Área solicitante<input name="area_solicitante" placeholder="Área solicitante"></label>
      <label class="output-field">Método de pago<select name="metodo_pago"><option value="EFECTIVO">Efectivo</option><option value="TRANSFERENCIA">Transferencia</option><option value="CHEQUE">Cheque</option><option value="TARJETA">Tarjeta</option><option value="OTRO">Otro</option></select></label>
      <label>Referencia<input name="referencia" placeholder="Factura, oficio, referencia..."></label>
      <label class="span-2">Comprobante / Evidencia<input name="evidencia" type="file" accept="application/pdf,image/jpeg,image/png"><small>PDF, JPG o PNG · Máx. 10 MB</small></label>
      <div class="span-2 available-box"><span>Disponible del departamento</span><strong id="formAvailable">$0.00</strong></div>
      <div class="form-actions span-2"><a class="btn ghost" href="index.php?view=movimientos">Cancelar</a><button class="btn primary">Guardar y generar folio</button></div>
    </form>
  </section>
  <aside class="panel movement-help">
    <div class="flow-step active"><span>1</span><div><strong>Captura</strong><small>Departamento, monto y concepto.</small></div></div>
    <div class="flow-step"><span>2</span><div><strong>Clasificación</strong><small>Sub-item y datos operativos.</small></div></div>
    <div class="flow-step"><span>3</span><div><strong>Folio</strong><small>Se genera al confirmar.</small></div></div>
    <div class="flow-step"><span>4</span><div><strong>Bitácora</strong><small>El movimiento queda trazable.</small></div></div>
  </aside>
</div>
<div class="modal hidden" id="folioModal">
  <div class="modal-card success-card">
    <div class="success-icon">✓</div><h2>Registro guardado correctamente</h2><p>Folio generado</p><strong class="folio-big" id="generatedFolio">FOL-0000</strong>
    <div class="form-actions"><a class="btn ghost" href="index.php?view=nuevo-movimiento">Registrar otro</a><a class="btn primary" href="index.php?view=bitacora">Ir a bitácora</a></div>
  </div>
</div>

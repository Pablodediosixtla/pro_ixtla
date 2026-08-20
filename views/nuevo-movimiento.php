<section class="page-head"><div><p class="muted">Registro financiero real. Las salidas quedan reservadas para Tesorería, Presidencia y Administración.</p></div><a class="btn btn-ghost" href="?view=movimientos">← Volver</a></section>
<div class="screen-flow" aria-label="Flujo de registro">
  <div class="screen-step active"><span>1</span><b>Datos</b></div><i class="flow-arrow">→</i>
  <div class="screen-step"><span>2</span><b>Sub-item</b></div><i class="flow-arrow">→</i>
  <div class="screen-step"><span>3</span><b>Beneficiario</b></div><i class="flow-arrow">→</i>
  <div class="screen-step"><span>4</span><b>Evidencia</b></div><i class="flow-arrow">→</i>
  <div class="screen-step"><span>5</span><b>Folio</b></div>
</div>
<div class="movement-layout">
  <section class="panel form-panel">
    <div class="panel-head"><div><span class="eyebrow">NUEVO MOVIMIENTO</span><h2>Registro de recurso</h2></div><span class="badge success">Auditable</span></div>
    <form id="movementForm" class="movement-form" enctype="multipart/form-data">
      <section class="form-section">
        <div class="form-section-head"><span class="form-section-number">1</span><div><strong>Información principal</strong><small>Indica qué tipo de movimiento se registra y a qué departamento corresponde.</small></div></div>
        <div class="form-grid">
          <label>Tipo<select name="tipo" id="movementFormType" required><?php if(nav_ok($perms,'MOVIMIENTO_SALIDA_CREAR')):?><option value="SALIDA">Salida</option><?php endif;?><?php if(nav_ok($perms,'MOVIMIENTO_ENTRADA_CREAR')):?><option value="ENTRADA">Entrada</option><?php endif;?></select></label>
          <label>Solicitud autorizada<select name="solicitud_id" id="authorizedRequest"><option value="">Movimiento directo</option></select></label>
          <label>Departamento<select name="departamento_id" id="movementFormDepartment" required></select></label>
          <label>Fecha<input name="fecha" type="date" required></label>
          <label>Monto<input name="monto" type="number" min="0.01" step="0.01" required></label>
          <label>Sub-item<select name="subitem_id" id="movementFormSubitem"><option value="">Sin sub-item</option></select></label>
          <label class="full">Concepto / uso<textarea name="concepto" rows="3" placeholder="Describe de forma breve y clara el uso del recurso..." required></textarea></label>
        </div>
      </section>
      <section class="form-section">
        <div class="form-section-head"><span class="form-section-number">2</span><div><strong>Persona y área</strong><small>Registra quién solicitó el recurso y quién lo recibe.</small></div></div>
        <div class="form-grid">
          <label>Solicitado por<select name="solicitado_por_usuario_id" id="movementRequester"><option value="">Usuario actual</option></select></label>
          <label>Otorgado a<select name="otorgado_a_usuario_id" id="movementBeneficiary"><option value="">Persona externa</option></select></label>
          <label>Beneficiario externo<input name="beneficiario_nombre" placeholder="Nombre completo, si aplica"></label>
          <label>Área solicitante<input name="area_solicitante" placeholder="Ej. Mantenimiento"></label>
        </div>
      </section>
      <section class="form-section">
        <div class="form-section-head"><span class="form-section-number">3</span><div><strong>Pago y evidencia</strong><small>Completa la referencia financiera y adjunta el comprobante.</small></div></div>
        <div class="form-grid">
          <label>Método de pago<select name="metodo_pago"><option value="">Seleccionar</option><option>TRANSFERENCIA</option><option>CHEQUE</option><option>EFECTIVO</option><option>TARJETA</option><option>OTRO</option></select></label>
          <label>Referencia<input name="referencia" placeholder="Transferencia, cheque o referencia"></label>
          <label class="full">Evidencia<input name="evidencia" type="file" accept=".pdf,.jpg,.jpeg,.png"><small>PDF, JPG o PNG · Máx. 10 MB</small></label>
        </div>
      </section>
      <input type="hidden" name="ejercicio" id="movementFormYear">
      <div class="form-actions"><button class="btn btn-primary btn-lg" type="submit">Guardar y generar folio</button></div>
    </form>
  </section>
  <aside class="panel movement-help">
    <span class="eyebrow">FLUJO CONTROLADO</span>
    <div class="flow-step active"><span>1</span><div><b>Validación</b><small>Permisos y presupuesto disponible.</small></div></div>
    <div class="flow-step"><span>2</span><div><b>Registro</b><small>Se conserva quién realizó el movimiento.</small></div></div>
    <div class="flow-step"><span>3</span><div><b>Folio</b><small>Se genera automáticamente al guardar.</small></div></div>
    <div class="flow-step"><span>4</span><div><b>Seguimiento</b><small>Aclaraciones y evidencia quedan ligadas.</small></div></div>
    <div class="available-box"><span>Disponible del departamento</span><strong id="movementAvailable">$0.00</strong><small>El sistema valida el saldo antes de registrar una salida.</small></div>
  </aside>
</div>

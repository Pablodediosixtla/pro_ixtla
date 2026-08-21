<section class="page-head"><div><p class="muted">Los departamentos solicitan; Tesorería registra la salida real una vez autorizada.</p></div><div class="page-actions"><label class="compact-control">Ejercicio<select id="requestYear"></select></label><?php if(nav_ok($perms,'SOLICITUD_CREAR')):?><button class="btn btn-primary" id="newRequestBtn">Nueva solicitud</button><?php endif;?></div></section>

<div class="request-workflow" aria-label="Flujo de solicitudes">
  <div class="request-step"><span>1</span><div><b>Departamento solicita</b><small>Captura el requerimiento</small></div></div>
  <div class="request-step"><span>2</span><div><b>Se autoriza</b><small>Validación del recurso</small></div></div>
  <div class="request-step"><span>3</span><div><b>Tesorería registra</b><small>Genera la salida real</small></div></div>
  <div class="request-step"><span>4</span><div><b>Folio y bitácora</b><small>Trazabilidad completa</small></div></div>
</div>

<section class="panel expandable-module-panel">
  <div class="panel-head"><div><span class="eyebrow">CONTROL DE SOLICITUDES</span><h2>Solicitudes de recurso</h2></div></div>
  <div class="filter-row"><label>Estatus<select id="requestStatus"><option value="">Todos los estatus</option><option>PENDIENTE</option><option>AUTORIZADA</option><option>RECHAZADA</option><option>PAGADA</option><option>CANCELADA</option></select></label><label class="filter-grow">Departamento<select id="requestDepartment"><option value="">Todos los departamentos</option></select></label></div>
  <div id="requestList" class="expandable-list request-list"></div>
</section>

<div class="modal hidden" id="requestModal"><div class="modal-card modal-wide"><div class="modal-head"><div><span class="eyebrow">NUEVA SOLICITUD</span><h2>Solicitar salida de recurso</h2></div><button class="icon-btn" data-close-modal>✕</button></div><form id="requestForm" class="form-grid"><label>Departamento<select name="departamento_id" id="requestFormDepartment" required></select></label><label>Sub-item de salida<select name="subitem_id" id="requestSubitem"><option value="">Sin sub-item</option></select></label><label>Monto<input name="monto" type="number" min="0.01" step="0.01" required></label><label>Área solicitante<input name="area_solicitante"></label><label class="full">Concepto / uso<textarea name="concepto" rows="3" required></textarea></label><label>Se otorga a usuario<select name="otorgado_a_usuario_id" id="requestBeneficiaryUser"><option value="">Persona externa / sin usuario</option></select></label><label>Beneficiario externo<input name="beneficiario_nombre" placeholder="Opcional"></label><input type="hidden" name="ejercicio" id="requestFormYear"><div class="info-box full">Esta solicitud <b>no descuenta presupuesto todavía</b>. La salida real la registra Tesorería y genera el folio financiero.</div><div class="form-actions full"><button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button><button class="btn btn-primary" type="submit">Enviar a Tesorería</button></div></form></div></div>

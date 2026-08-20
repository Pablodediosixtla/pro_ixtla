<?php $preDepartment=(int)($_GET['departamento_id']??0); ?>
<div class="page-head-row"><div><h2>Bitácora de movimientos</h2><p class="muted">Historial auditable de entradas y salidas.</p></div></div>
<div class="panel">
  <div class="filter-grid" id="logFilters" data-pre-department="<?= $preDepartment ?>">
    <label>Fecha desde<input type="date" id="logFrom"></label><label>Fecha hasta<input type="date" id="logTo"></label><label>Departamento<select id="logDepartment"><option value="">Todos</option></select></label><label>Tipo<select id="logType"><option value="">Todos</option><option value="ENTRADA">Entrada</option><option value="SALIDA">Salida</option></select></label><label>Estado<select id="logStatus"><option value="">Todos</option><option value="REGISTRADO">Registrado</option><option value="CANCELADO">Cancelado</option></select></label><label>Búsqueda<input id="logQuery" placeholder="Folio o concepto"></label><button id="logSearchBtn" class="btn primary">Buscar</button>
  </div>
  <div class="table-wrap"><table><thead><tr><th>Folio</th><th>Fecha</th><th>Departamento</th><th>Sub-item</th><th>Tipo</th><th>Monto</th><th>Usuario</th><th>Estado</th><th></th></tr></thead><tbody id="logTable"></tbody></table></div>
</div>
<div class="modal hidden" id="movementDetailModal"><div class="modal-card wide"><div class="modal-head"><h3>Detalle del movimiento</h3><button class="icon-btn" data-close-modal>×</button></div><div id="movementDetailContent"></div></div></div>

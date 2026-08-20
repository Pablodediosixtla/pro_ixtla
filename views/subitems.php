<div class="page-head-row">
  <div><h2>Sub-items presupuestales</h2><p class="muted">Catálogo para clasificar entradas y salidas. Puede ser global o específico por departamento.</p></div>
  <button id="newSubitemBtn" class="btn primary admin-only">+ Nuevo sub-item</button>
</div>
<div class="panel">
  <div class="filter-row"><label>Departamento<select id="subitemDepartmentFilter"><option value="">Todos</option></select></label><label>Estado<select id="subitemStatusFilter"><option value="all">Todos</option><option value="1">Activos</option></select></label></div>
  <div class="table-wrap"><table><thead><tr><th>Código</th><th>Nombre</th><th>Departamento</th><th>Descripción</th><th>Estado</th><th></th></tr></thead><tbody id="subitemTable"></tbody></table></div>
</div>
<div class="modal hidden" id="subitemModal">
  <div class="modal-card">
    <div class="modal-head"><h3>Sub-item</h3><button class="icon-btn" data-close-modal>×</button></div>
    <form id="subitemForm" class="form-grid">
      <input type="hidden" name="id">
      <label>Código<input name="codigo" maxlength="30" required placeholder="FERRETERIA"></label>
      <label>Estado<select name="status"><option value="1">Activo</option><option value="0">Inactivo</option></select></label>
      <label class="span-2">Nombre<input name="nombre" required></label>
      <label class="span-2">Departamento<select name="departamento_id"><option value="">Global / Todos</option></select></label>
      <label class="span-2">Descripción<textarea name="descripcion" rows="3"></textarea></label>
      <div class="form-actions span-2"><button type="button" class="btn ghost" data-close-modal>Cancelar</button><button class="btn primary">Guardar</button></div>
    </form>
  </div>
</div>

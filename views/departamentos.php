<div class="page-head-row">
  <div><h2>Listado de departamentos</h2><p class="muted">Selecciona un departamento para revisar y gestionar sus recursos.</p></div>
  <div class="toolbar"><input id="departmentSearch" class="search" placeholder="Buscar departamento..."><button id="newDepartmentBtn" class="btn secondary admin-only">+ Departamento</button></div>
</div>
<div id="departmentCards" class="department-grid"></div>
<div class="modal hidden" id="departmentModal">
  <div class="modal-card">
    <div class="modal-head"><h3 id="departmentModalTitle">Departamento</h3><button class="icon-btn" data-close-modal>×</button></div>
    <form id="departmentForm" class="form-grid">
      <input type="hidden" name="id">
      <label class="span-2">Nombre<input name="nombre" required></label>
      <label class="span-2">Descripción<textarea name="descripcion" rows="3" required></textarea></label>
      <label>Director<select name="director" required></select></label>
      <label>Primera línea<select name="primera_linea" required></select></label>
      <label>Estado<select name="status"><option value="1">Activo</option><option value="0">Inactivo</option></select></label>
      <div class="form-actions span-2"><button type="button" class="btn ghost" data-close-modal>Cancelar</button><button class="btn primary">Guardar</button></div>
    </form>
  </div>
</div>

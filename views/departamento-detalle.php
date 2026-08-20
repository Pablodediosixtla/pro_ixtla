<?php $departmentId=(int)($_GET['id']??0); ?>
<div id="departmentDetail" data-department-id="<?= $departmentId ?>">
  <div class="page-head-row">
    <div><a class="back-link" href="index.php?view=departamentos">← Departamentos</a><h2 id="detailName">Departamento</h2><p class="muted" id="detailDescription">Cargando...</p></div>
    <a class="btn primary" id="detailMovementLink" href="index.php?view=nuevo-movimiento&departamento_id=<?= $departmentId ?>">Registrar movimiento</a>
  </div>
  <div class="kpi-grid detail-kpis">
    <article class="kpi-card"><small>Presupuesto asignado</small><strong id="detailAssigned">$0.00</strong></article>
    <article class="kpi-card"><small>Entradas</small><strong id="detailEntries">$0.00</strong></article>
    <article class="kpi-card"><small>Salidas</small><strong id="detailOutputs">$0.00</strong></article>
    <article class="kpi-card"><small>Disponible</small><strong id="detailAvailable">$0.00</strong></article>
  </div>
  <div class="two-col">
    <article class="panel">
      <div class="panel-head"><h3>Asignación anual</h3></div>
      <form id="budgetAssignmentForm" class="stack-form compact admin-only">
        <label>Ejercicio<input type="number" name="ejercicio" min="2020" max="2100" value="<?= date('Y') ?>"></label>
        <label>Presupuesto asignado<input type="number" step="0.01" min="0" name="presupuesto_asignado" required></label>
        <button class="btn primary">Guardar asignación</button>
      </form>
      <p class="muted non-admin-only">La asignación anual solo puede ser modificada por un administrador.</p>
    </article>
    <article class="panel">
      <div class="panel-head"><h3>Sub-items principales</h3><a href="index.php?view=subitems">Gestionar</a></div>
      <div id="detailSubitems" class="summary-list"></div>
    </article>
  </div>
  <article class="panel mt-20">
    <div class="panel-head"><h3>Últimos movimientos</h3><a href="index.php?view=bitacora&departamento_id=<?= $departmentId ?>">Ver bitácora</a></div>
    <div class="table-wrap"><table><thead><tr><th>Folio</th><th>Fecha</th><th>Tipo</th><th>Sub-item</th><th>Concepto</th><th>Monto</th><th>Estado</th></tr></thead><tbody id="detailMovements"></tbody></table></div>
  </article>
</div>

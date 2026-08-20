<div class="page-head-row">
  <div><h2>Panorama general</h2><p class="muted">Presupuesto asignado, ejercicio y disponibilidad por departamento.</p></div>
  <label class="inline-field">Ejercicio <select id="dashboardYear"></select></label>
</div>
<div class="kpi-grid" id="dashboardKpis">
  <article class="kpi-card"><span class="kpi-icon green">▣</span><small>Presupuesto asignado</small><strong data-kpi="asignado">$0.00</strong></article>
  <article class="kpi-card"><span class="kpi-icon teal">↙</span><small>Entradas</small><strong data-kpi="entradas">$0.00</strong></article>
  <article class="kpi-card"><span class="kpi-icon orange">↗</span><small>Salidas ejercidas</small><strong data-kpi="salidas">$0.00</strong></article>
  <article class="kpi-card"><span class="kpi-icon blue">◉</span><small>Disponible</small><strong data-kpi="disponible">$0.00</strong></article>
</div>
<div class="two-col">
  <article class="panel">
    <div class="panel-head"><div><h3>Ejecución mensual</h3><p class="muted">Entradas y salidas registradas</p></div></div>
    <div id="monthlyBars" class="monthly-bars"></div>
  </article>
  <article class="panel">
    <div class="panel-head"><div><h3>Departamentos</h3><p class="muted">Disponible y porcentaje ejercido</p></div><a href="index.php?view=departamentos">Ver todos</a></div>
    <div id="departmentSummary" class="summary-list"></div>
  </article>
</div>

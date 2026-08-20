<?php
$departmentId=(int)($_GET['departamento_id']??0);
$requestedYear=(int)($_GET['year']??date('Y'));
?>
<section class="context-head">
  <div>
    <a class="back-link" href="?view=presupuestos">← Presupuestos</a>
    <span class="eyebrow">RESUMEN DEL DEPARTAMENTO</span>
    <h2 id="departmentSummaryTitle">Cargando departamento…</h2>
    <p class="muted" id="departmentSummaryDescription">Consulta disponibilidad, ejercicio y distribución del gasto por subcategoría.</p>
  </div>
  <label class="compact-control summary-year-control">Ejercicio<select id="departmentSummaryYear"></select></label>
</section>

<section class="department-overview-hero" id="departmentOverviewHero" style="--dept-color:#31513f">
  <div class="department-identity">
    <span class="department-code-badge" id="departmentSummaryCode">DEP</span>
    <div>
      <small>Departamento</small>
      <h2 id="departmentHeroName">—</h2>
      <p id="departmentHeroDescription">—</p>
    </div>
  </div>
  <div class="department-hero-status"><span id="departmentHeroPct">0%</span><small>ejercido</small></div>
</section>

<div class="department-kpi-grid">
  <article class="department-kpi-card assigned"><span class="department-kpi-icon">▣</span><div><small>Presupuesto asignado</small><strong id="departmentAssigned">$0.00</strong></div></article>
  <article class="department-kpi-card available"><span class="department-kpi-icon">◫</span><div><small>Disponible</small><strong id="departmentAvailable">$0.00</strong></div></article>
  <article class="department-kpi-card spent"><span class="department-kpi-icon">↘</span><div><small>Ejercido</small><strong id="departmentSpent">$0.00</strong></div></article>
  <article class="department-kpi-card percent"><span class="department-kpi-icon">◔</span><div><small>% ejercido</small><strong id="departmentPercent">0%</strong></div></article>
</div>

<div class="department-summary-main-grid">
  <section class="panel department-monthly-panel">
    <div class="panel-head"><div><span class="eyebrow">EJECUCIÓN MENSUAL</span><h2>Entradas y salidas</h2></div><span class="legend"><i class="dot entry"></i>Entradas <i class="dot output"></i>Salidas</span></div>
    <div id="departmentMonthlyChart" class="monthly-bars department-monthly-bars"></div>
  </section>
  <section class="panel department-actions-panel">
    <div class="panel-head"><div><span class="eyebrow">ACCIONES</span><h2>Operación del departamento</h2></div></div>
    <div class="department-action-list">
      <?php if(nav_ok($perms,'MOVIMIENTO_SALIDA_CREAR')||nav_ok($perms,'MOVIMIENTO_ENTRADA_CREAR')):?><a class="department-action primary" href="?view=nuevo-movimiento"><span>＋</span><div><b>Registrar movimiento</b><small>Entrada o salida de recurso</small></div><i>›</i></a><?php endif;?>
      <?php if(nav_ok($perms,'MOVIMIENTO_VER')):?><a class="department-action" id="departmentMovementsLink" href="?view=movimientos"><span>⇄</span><div><b>Ver movimientos</b><small>Folios y registros del departamento</small></div><i>›</i></a><?php endif;?>
      <?php if(nav_ok($perms,'BITACORA_VER')):?><a class="department-action" href="?view=bitacora"><span>✓</span><div><b>Ver bitácora</b><small>Trazabilidad y auditoría</small></div><i>›</i></a><?php endif;?>
      <a class="department-action" href="#departmentCategories"><span>▤</span><div><b>Subcategorías</b><small>Distribución completa del gasto</small></div><i>↓</i></a>
    </div>
  </section>
</div>

<section class="panel department-category-panel" id="departmentCategories">
  <div class="panel-head department-category-head"><div><span class="eyebrow">SUBCATEGORÍAS</span><h2>Distribución del gasto</h2><p class="muted">Se muestran todas las subcategorías disponibles para este departamento, incluso aquellas sin gasto.</p></div><span class="category-total-label"><b id="categorySpentTotal">$0.00</b><small>salidas del ejercicio</small></span></div>
  <div id="departmentCategoryList" class="department-category-list"><div class="empty-state">Cargando subcategorías…</div></div>
</section>

<section class="panel recent-department-panel">
  <div class="panel-head"><div><span class="eyebrow">ÚLTIMAS SALIDAS</span><h2>Movimientos recientes</h2></div><a id="departmentAllMovementsLink" href="?view=movimientos" class="btn btn-ghost btn-sm">Ver todos</a></div>
  <div id="departmentRecentOutputs" class="recent-output-list"><div class="empty-state">Cargando movimientos…</div></div>
</section>

<script>window.PROIXTLA_DEPARTMENT_ID=<?=json_encode($departmentId)?>;window.PROIXTLA_REQUESTED_YEAR=<?=json_encode($requestedYear)?>;</script>

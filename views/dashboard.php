<?php
$dashPrimary=$user['assignments'][0]??[];
$dashRole=$dashPrimary['role_name']??(user_role_codes($user)[0]??'Usuario');
$dashDepartment=$dashPrimary['department']??'Municipio';
$dashFirstName=$user['first_name']??$user['name']??'Usuario';
?>
<section class="dashboard-hero">
  <div class="dashboard-hero-copy">
    <span class="eyebrow">CONTROL PRESUPUESTAL</span>
    <h2>Hola, <?=htmlspecialchars($dashFirstName)?>.</h2>
    <p><?=user_is_own_scope_only($user)?'Consulta tus solicitudes, movimientos y seguimientos desde una vista personal.':'Consulta presupuesto, salidas, solicitudes y seguimientos desde una vista clara de tu alcance autorizado.'?></p>
    <div class="hero-meta"><span><?=htmlspecialchars($dashRole)?></span><span><?=htmlspecialchars($dashDepartment)?></span><span><?=user_is_global($user)?'Alcance municipal':'Alcance restringido'?></span></div>
  </div>
  <div class="mobile-dashboard-welcome">Bienvenido, <strong><?=htmlspecialchars($dashFirstName)?></strong>.</div>
  <div class="dashboard-hero-actions">
    <label class="compact-control dashboard-year-filter">
      <span class="dashboard-year-filter-label">Ejercicio</span>
      <select id="yearSelect" aria-label="Filtrar por ejercicio"></select>
    </label>
    <?php if(nav_ok($perms,'MOVIMIENTO_SALIDA_CREAR')||nav_ok($perms,'MOVIMIENTO_ENTRADA_CREAR')):?><a class="btn dashboard-register-btn" href="?view=nuevo-movimiento">Registrar movimiento</a><?php endif;?>
  </div>
</section>
<?php if(!user_is_own_scope_only($user)):?>
<div class="kpi-grid kpi-grid-home">
  <article class="kpi-card"><span class="kpi-icon green"><?=nav_icon('bank')?></span><small>Presupuesto asignado</small><strong id="kpiAssigned">$0.00</strong><em>Base anual autorizada</em></article>
  <article class="kpi-card"><span class="kpi-icon blue">↗</span><small>Entradas</small><strong id="kpiEntries">$0.00</strong><em>Recursos agregados</em></article>
  <article class="kpi-card"><span class="kpi-icon teal">✓</span><small>Disponible</small><strong id="kpiAvailable">$0.00</strong><em>Saldo actual</em></article>
  <article class="kpi-card kpi-card-exercised"><span class="kpi-icon gold">◔</span><small>Ejercido</small><strong id="kpiExercised">$0.00</strong><em><b id="kpiExercisedPct">0.0%</b> del presupuesto asignado</em><span class="kpi-mobile-percent" id="kpiExercisedPctMobile">0.0%</span></article>
</div>
<?php endif;?>
<div class="quick-grid">
  <a href="?view=solicitudes" class="quick-card"><span>▤</span><div><b>Solicitudes pendientes</b><strong id="pendingRequests">0</strong></div></a>
  <a href="?view=aclaraciones" class="quick-card"><span class="quick-line-icon"><?=nav_icon('file-lines')?></span><div><b>Aclaraciones abiertas</b><strong id="openClarifications">0</strong></div></a>
  <a href="?view=movimientos" class="quick-card"><span>⌕</span><div><b>Consultar movimientos</b><small>Folios, personas y evidencias</small></div></a>
</div>
<div class="dashboard-grid <?=user_is_own_scope_only($user)?'dashboard-grid-own':''?>">
  <section class="panel monthly-interactive-panel"><div class="panel-head"><div><span class="eyebrow">COMPORTAMIENTO</span><h2><?=user_is_own_scope_only($user)?'Mis movimientos mensuales':'Movimiento mensual'?></h2></div><span class="legend"><?php if(!user_is_own_scope_only($user)):?><i class="dot entry"></i>Entradas <?php endif;?><i class="dot output"></i>Salidas</span></div><div id="monthlyChart" class="monthly-bars" aria-label="Gráfica mensual interactiva"></div><div id="monthlyChartDetail" class="chart-month-detail hidden" aria-live="polite"></div></section>
  <?php if(!user_is_own_scope_only($user)):?>
  <section class="panel"><div class="panel-head"><div><span class="eyebrow">DEPARTAMENTOS</span><h2>Disponibilidad</h2></div><a href="?view=presupuestos" class="btn btn-ghost btn-sm">Ver detalle</a></div><div id="departmentSummary" class="department-summary"></div></section>
  <?php endif;?>
</div>

<?php
$dashPrimary=$user['assignments'][0]??[];
$dashRole=$dashPrimary['role_name']??(user_role_codes($user)[0]??'Usuario');
$dashDepartment=$dashPrimary['department']??'Municipio';
?>
<section class="dashboard-hero">
  <div class="dashboard-hero-copy">
    <span class="eyebrow">CONTROL PRESUPUESTAL</span>
    <h2>Hola, <?=htmlspecialchars($user['first_name']??$user['name']??'Usuario')?>.</h2>
    <p>Consulta presupuesto, salidas, solicitudes y seguimientos desde una vista clara de tu alcance autorizado.</p>
    <div class="hero-meta"><span><?=htmlspecialchars($dashRole)?></span><span><?=htmlspecialchars($dashDepartment)?></span><span><?=user_is_global($user)?'Alcance municipal':'Alcance restringido'?></span></div>
  </div>
  <div class="dashboard-hero-actions">
    <label class="compact-control">Ejercicio<select id="yearSelect"></select></label>
    <?php if(nav_ok($perms,'MOVIMIENTO_SALIDA_CREAR')||nav_ok($perms,'MOVIMIENTO_ENTRADA_CREAR')):?><a class="btn" href="?view=nuevo-movimiento">Registrar movimiento</a><?php endif;?>
  </div>
</section>
<div class="kpi-grid">
  <article class="kpi-card"><span class="kpi-icon green">▣</span><small>Presupuesto asignado</small><strong id="kpiAssigned">$0.00</strong><em>Base anual autorizada</em></article>
  <article class="kpi-card"><span class="kpi-icon blue">↗</span><small>Entradas</small><strong id="kpiEntries">$0.00</strong><em>Recursos agregados</em></article>
  <article class="kpi-card"><span class="kpi-icon red">↘</span><small>Salidas</small><strong id="kpiOutputs">$0.00</strong><em>Ejercicio registrado</em></article>
  <article class="kpi-card"><span class="kpi-icon teal">✓</span><small>Disponible</small><strong id="kpiAvailable">$0.00</strong><em>Saldo actual</em></article>
</div>
<div class="quick-grid">
  <a href="?view=solicitudes" class="quick-card"><span>▤</span><div><b>Solicitudes pendientes</b><strong id="pendingRequests">0</strong></div></a>
  <a href="?view=aclaraciones" class="quick-card"><span>◌</span><div><b>Aclaraciones abiertas</b><strong id="openClarifications">0</strong></div></a>
  <a href="?view=movimientos" class="quick-card"><span>⌕</span><div><b>Consultar movimientos</b><small>Folios, personas y evidencias</small></div></a>
</div>
<div class="dashboard-grid">
  <section class="panel"><div class="panel-head"><div><span class="eyebrow">COMPORTAMIENTO</span><h2>Movimiento mensual</h2></div><span class="legend"><i class="dot entry"></i>Entradas <i class="dot output"></i>Salidas</span></div><div id="monthlyChart" class="monthly-bars"></div></section>
  <section class="panel"><div class="panel-head"><div><span class="eyebrow">DEPARTAMENTOS</span><h2>Disponibilidad</h2></div><a href="?view=presupuestos" class="btn btn-ghost btn-sm">Ver detalle</a></div><div id="departmentSummary" class="department-summary"></div></section>
</div>

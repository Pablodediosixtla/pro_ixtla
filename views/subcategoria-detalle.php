<?php
$departmentId=(int)($_GET['departamento_id']??0);
$subitemId=(int)($_GET['subitem_id']??0);
$requestedYear=(int)($_GET['year']??date('Y'));
?>
<section class="context-head subcategory-context-head">
  <div>
    <a class="back-link" id="subcategoryBackLink" href="?view=presupuestos">← Resumen del departamento</a>
    <span class="eyebrow">DETALLE DE SUBCATEGORÍA</span>
    <h2 id="subcategoryTitle">Cargando subcategoría…</h2>
    <p class="muted" id="subcategoryDescription">Detalle de salidas registradas en esta subcategoría.</p>
  </div>
  <label class="compact-control summary-year-control">Ejercicio<select id="subcategoryYear"></select></label>
</section>

<section class="subcategory-hero" id="subcategoryHero" style="--dept-color:#31513f">
  <div><span class="subcategory-code" id="subcategoryCode">CAT</span><small id="subcategoryDepartment">Departamento</small><h2 id="subcategoryHeroName">—</h2></div>
  <div class="subcategory-share"><strong id="subcategoryShareHero">0%</strong><small>del gasto del departamento</small></div>
</section>

<div class="subcategory-kpi-grid">
  <article><small>Salidas</small><strong id="subcategoryOutputs">$0.00</strong><span>Ejercicio seleccionado</span></article>
  <article><small>% del gasto</small><strong id="subcategoryShare">0%</strong><span>Participación en las salidas</span></article>
  <article><small>Registros</small><strong id="subcategoryCount">0</strong><span>Movimientos registrados</span></article>
  <article><small>Última salida</small><strong id="subcategoryLast">—</strong><span>Fecha más reciente</span></article>
</div>

<section class="panel subcategory-movements-panel">
  <div class="panel-head subcategory-table-head">
    <div><span class="eyebrow">REGISTROS DE SALIDA</span><h2>Detalle de movimientos</h2><p class="muted">Consulta folio, concepto, beneficiario, monto y usuario que registró cada salida.</p></div>
  </div>

  <label class="subcategory-search" for="subcategorySearch">
    <span class="subcategory-search-icon" aria-hidden="true">⌕</span>
    <input id="subcategorySearch" type="search" autocomplete="off" placeholder="Buscar folio, concepto o persona">
  </label>

  <div class="subcategory-movement-table table-wrap">
    <table>
      <thead><tr><th>Folio</th><th>Fecha</th><th>Concepto</th><th>Otorgado a</th><th>Registrado por</th><th>Monto</th><th>Seguimiento</th><th></th></tr></thead>
      <tbody id="subcategoryMovementTable"><tr><td colspan="8" class="empty-state">Cargando movimientos…</td></tr></tbody>
    </table>
  </div>

  <div class="subcategory-activity-list" id="subcategoryMovementCards" aria-live="polite">
    <div class="empty-state subcategory-card-empty">Cargando movimientos…</div>
  </div>
</section>
<script>window.PROIXTLA_DEPARTMENT_ID=<?=json_encode($departmentId)?>;window.PROIXTLA_SUBITEM_ID=<?=json_encode($subitemId)?>;window.PROIXTLA_REQUESTED_YEAR=<?=json_encode($requestedYear)?>;</script>

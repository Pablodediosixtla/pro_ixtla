(()=>{
  const departmentId=Number(window.PROIXTLA_DEPARTMENT_ID||0);
  const subitemId=Number(window.PROIXTLA_SUBITEM_ID||0);
  const year=document.getElementById('subcategoryYear');
  const search=document.getElementById('subcategorySearch');
  const tbody=document.getElementById('subcategoryMovementTable');
  let rows=[];
  App.years(year);
  if(window.PROIXTLA_REQUESTED_YEAR)year.value=String(window.PROIXTLA_REQUESTED_YEAR);

  function updateUrl(){
    const url=new URL(location.href);
    url.searchParams.set('view','subcategoria-detalle');
    url.searchParams.set('departamento_id',departmentId);
    url.searchParams.set('subitem_id',subitemId);
    url.searchParams.set('year',year.value);
    history.replaceState({},'',url);
  }

  function renderRows(){
    const q=(search?.value||'').trim().toLowerCase();
    const filtered=!q?rows:rows.filter(r=>[r.folio,r.concepto,r.otorgado_a,r.solicitado_por,r.registrado_por,r.referencia].some(v=>String(v||'').toLowerCase().includes(q)));
    tbody.innerHTML=filtered.length?filtered.map(r=>`<tr>
      <td><a class="link-btn" href="?view=movimientos&movement_id=${r.movimiento_id}">${App.escape(r.folio)}</a></td>
      <td>${App.date(r.fecha)}</td>
      <td><div class="table-primary-cell"><b>${App.escape(r.concepto)}</b>${r.referencia?`<small>${App.escape(r.referencia)}</small>`:''}</div></td>
      <td>${App.escape(r.otorgado_a||'—')}</td>
      <td>${App.escape(r.registrado_por||'—')}</td>
      <td class="amount">${App.money(r.monto)}</td>
      <td>${r.aclaraciones_abiertas?`<span class="badge warning">${r.aclaraciones_abiertas} abierta${r.aclaraciones_abiertas===1?'':'s'}</span>`:'<span class="badge success">Sin pendientes</span>'}</td>
      <td><a class="btn btn-soft btn-sm" href="?view=movimientos&movement_id=${r.movimiento_id}">Ver</a></td>
    </tr>`).join(''):'<tr><td colspan="8" class="empty-state">No hay registros que coincidan con la búsqueda.</td></tr>';
    App.decorateTables(tbody.closest('.table-wrap'));
  }

  async function load(){
    updateUrl();
    try{
      const d=await App.api(`api.php?route=subitems/detalle&departamento_id=${departmentId}&subitem_id=${subitemId}&year=${year.value}`);
      const dep=d.department,c=d.category,t=d.totals;
      document.getElementById('subcategoryTitle').textContent=c.nombre;
      document.getElementById('subcategoryDescription').textContent=c.descripcion||'Detalle de salidas registradas en esta subcategoría.';
      document.getElementById('subcategoryCode').textContent=c.codigo||'CAT';
      document.getElementById('subcategoryDepartment').textContent=dep.nombre;
      document.getElementById('subcategoryHeroName').textContent=c.nombre;
      document.getElementById('subcategoryHero').style.setProperty('--dept-color',dep.color_hex||'#31513f');
      document.getElementById('subcategoryShareHero').textContent=`${Number(t.participacion_pct||0).toFixed(1)}%`;
      document.getElementById('subcategoryOutputs').textContent=App.money(t.salidas);
      document.getElementById('subcategoryShare').textContent=`${Number(t.participacion_pct||0).toFixed(1)}%`;
      document.getElementById('subcategoryCount').textContent=String(t.registros||0);
      document.getElementById('subcategoryLast').textContent=t.ultima_salida?App.date(t.ultima_salida):'—';
      document.getElementById('subcategoryBackLink').href=`?view=departamento-resumen&departamento_id=${departmentId}&year=${year.value}`;
      rows=d.movements||[];
      renderRows();
    }catch(e){App.toast(e.message,'error')}
  }

  year?.addEventListener('change',load);
  search?.addEventListener('input',App.debounce(renderRows,160));
  load();
})();

(()=>{
  const departmentId=Number(window.PROIXTLA_DEPARTMENT_ID||0);
  const yearSelect=document.getElementById('departmentSummaryYear');
  App.years(yearSelect);
  if(window.PROIXTLA_REQUESTED_YEAR){yearSelect.value=String(window.PROIXTLA_REQUESTED_YEAR)}

  function updateUrl(){
    const url=new URL(location.href);
    url.searchParams.set('view','departamento-resumen');
    url.searchParams.set('departamento_id',departmentId);
    url.searchParams.set('year',yearSelect.value);
    history.replaceState({},'',url);
  }

  function renderMonths(monthly){
    const el=document.getElementById('departmentMonthlyChart');
    const vals=Object.values(monthly||{}).flatMap(x=>[Number(x.entrada||0),Number(x.salida||0)]);
    const max=Math.max(1,...vals);
    const names=['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    el.innerHTML=names.map((name,i)=>{
      const x=monthly?.[i+1]||{entrada:0,salida:0};
      const entry=Math.max(3,Number(x.entrada||0)/max*100);
      const output=Math.max(3,Number(x.salida||0)/max*100);
      return `<div class="month-group"><div class="bars"><i class="bar entry" style="height:${entry}%" title="${App.money(x.entrada)}"></i><i class="bar output" style="height:${output}%" title="${App.money(x.salida)}"></i></div><small>${name}</small></div>`;
    }).join('');
  }

  function categoryIcon(code){
    const c=(code||'').toUpperCase();
    if(c.includes('GAS'))return '⛽';
    if(c.includes('PAP'))return '▤';
    if(c.includes('FER'))return '⌁';
    if(c.includes('MAN'))return '⚙';
    if(c.includes('SER'))return '◇';
    if(c.includes('EVE'))return '☆';
    return '▦';
  }

  function renderCategories(categories,totalOutputs){
    const el=document.getElementById('departmentCategoryList');
    if(!categories?.length){el.innerHTML='<div class="empty-state">No hay subcategorías configuradas para este departamento.</div>';return}
    el.innerHTML=categories.map((c,index)=>{
      const pct=Math.max(0,Math.min(100,Number(c.porcentaje_gasto||0)));
      const detail=`?view=subcategoria-detalle&departamento_id=${departmentId}&subitem_id=${c.subitem_id}&year=${yearSelect.value}`;
      return `<a class="department-category-row" href="${detail}">
        <span class="category-icon tone-${(index%5)+1}">${categoryIcon(c.codigo)}</span>
        <div class="category-main">
          <div class="category-title-row"><div><small>${App.escape(c.codigo||'CAT')}</small><b>${App.escape(c.nombre)}</b></div><span>${Number(c.porcentaje_gasto||0).toFixed(1)}%</span></div>
          <div class="progress category-progress"><span style="width:${pct}%"></span></div>
          <div class="category-meta"><span>${App.money(c.salidas)} gastado</span><span>${Number(c.registros||0)} registro${Number(c.registros||0)===1?'':'s'}</span></div>
        </div>
        <span class="category-arrow">›</span>
      </a>`;
    }).join('');
    document.getElementById('categorySpentTotal').textContent=App.money(totalOutputs);
  }

  function renderRecent(rows){
    const el=document.getElementById('departmentRecentOutputs');
    if(!rows?.length){el.innerHTML='<div class="empty-state">No hay salidas registradas en este ejercicio.</div>';return}
    el.innerHTML=rows.map(r=>`<a class="recent-output-row" href="?view=movimientos&movement_id=${r.movimiento_id}">
      <div class="recent-output-folio"><span>${App.escape(r.folio)}</span><small>${App.date(r.fecha)}</small></div>
      <div class="recent-output-concept"><b>${App.escape(r.concepto)}</b><small>${App.escape(r.subitem)} · ${App.escape(r.otorgado_a||'Sin beneficiario')}</small></div>
      <strong>${App.money(r.monto)}</strong><span class="category-arrow">›</span>
    </a>`).join('');
  }

  async function load(){
    if(!departmentId){App.toast('Departamento inválido','error');return}
    updateUrl();
    try{
      const d=await App.api(`api.php?route=departamentos/resumen&departamento_id=${departmentId}&year=${yearSelect.value}`);
      const dep=d.department,t=d.totals;
      document.getElementById('departmentSummaryTitle').textContent=dep.nombre;
      document.getElementById('departmentSummaryDescription').textContent=dep.descripcion||'Consulta disponibilidad, ejercicio y distribución del gasto por subcategoría.';
      document.getElementById('departmentSummaryCode').textContent=dep.codigo;
      document.getElementById('departmentHeroName').textContent=dep.nombre;
      document.getElementById('departmentHeroDescription').textContent=dep.descripcion||'Resumen presupuestal del departamento.';
      document.getElementById('departmentHeroPct').textContent=`${Number(t.ejercido_pct||0).toFixed(1)}%`;
      document.getElementById('departmentOverviewHero').style.setProperty('--dept-color',dep.color_hex||'#31513f');
      document.getElementById('departmentAssigned').textContent=App.money(t.asignado);
      document.getElementById('departmentEntries').textContent=App.money(t.entradas);
      document.getElementById('departmentAvailable').textContent=App.money(t.disponible);
      document.getElementById('departmentSpent').textContent=App.money(t.salidas);
      const movementsUrl=`?view=movimientos&departamento_id=${departmentId}&year=${yearSelect.value}`;
      document.getElementById('departmentMovementsLink')?.setAttribute('href',movementsUrl);
      document.getElementById('departmentAllMovementsLink')?.setAttribute('href',movementsUrl);
      renderMonths(d.monthly);
      renderCategories(d.categories,t.salidas);
      renderRecent(d.recent_outputs);
    }catch(e){App.toast(e.message,'error')}
  }

  yearSelect?.addEventListener('change',load);
  load();
})();

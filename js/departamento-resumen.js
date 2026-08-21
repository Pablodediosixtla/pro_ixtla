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

  const monthNames=['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
  const monthShort=['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

  function showMonthDetail(month,data,button){
    const chart=document.getElementById('departmentMonthlyChart');
    const detail=document.getElementById('departmentMonthlyDetail');
    chart.querySelectorAll('.chart-month-button').forEach(el=>{
      el.classList.toggle('active',el===button);
      el.setAttribute('aria-pressed',el===button?'true':'false');
    });
    const href=`?view=movimientos&year=${encodeURIComponent(yearSelect.value)}&month=${month}&departamento_id=${departmentId}`;
    detail.innerHTML=`<div class="chart-month-copy"><small>MES SELECCIONADO</small><strong>${monthNames[month-1]} ${App.escape(yearSelect.value)}</strong></div>
      <div class="chart-month-metric entry"><span>Entradas</span><b>${App.money(data?.entrada||0)}</b></div>
      <div class="chart-month-metric output"><span>Salidas</span><b>${App.money(data?.salida||0)}</b></div>
      <a class="btn btn-soft btn-sm chart-detail-link" href="${href}">Ver movimientos <span>›</span></a>`;
    detail.classList.remove('hidden');
  }

  function renderMonths(monthly){
    const el=document.getElementById('departmentMonthlyChart');
    const detail=document.getElementById('departmentMonthlyDetail');
    detail.classList.add('hidden');
    detail.innerHTML='';
    const vals=Object.values(monthly||{}).flatMap(x=>[Number(x.entrada||0),Number(x.salida||0)]);
    const max=Math.max(1,...vals);
    el.innerHTML=monthShort.map((name,i)=>{
      const month=i+1,x=monthly?.[month]||{entrada:0,salida:0};
      const entry=Math.max(3,Number(x.entrada||0)/max*100);
      const output=Math.max(3,Number(x.salida||0)/max*100);
      return `<button type="button" class="month-group chart-month-button" data-month="${month}" aria-pressed="false" aria-label="${monthNames[i]}: entradas ${App.money(x.entrada)}, salidas ${App.money(x.salida)}"><span class="bars"><i class="bar entry" style="height:${entry}%" title="${App.money(x.entrada)}"></i><i class="bar output" style="height:${output}%" title="${App.money(x.salida)}"></i></span><small>${name}</small></button>`;
    }).join('');
    el.querySelectorAll('.chart-month-button').forEach(button=>button.addEventListener('click',()=>{
      const month=Number(button.dataset.month);
      showMonthDetail(month,monthly?.[month]||{entrada:0,salida:0},button);
    }));
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

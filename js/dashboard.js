(()=>{
  const y=document.getElementById('yearSelect');
  const chart=document.getElementById('monthlyChart');
  const chartDetail=document.getElementById('monthlyChartDetail');
  const monthNames=['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
  const monthShort=['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
  App.years(y);

  async function load(){
    try{
      const d=await App.api('api.php?route=dashboard/resumen&year='+y.value);
      const setText=(id,value)=>{const el=document.getElementById(id);if(el)el.textContent=value};
      setText('kpiAssigned',App.money(d.totals.asignado));
      setText('kpiEntries',App.money(d.totals.entradas));
      setText('kpiAvailable',App.money(d.totals.disponible));
      setText('kpiExercised',App.money(d.totals.salidas));
      const exercisedPct=Number(d.totals.ejercido_pct||0).toFixed(1)+'%';
      setText('kpiExercisedPct',exercisedPct);
      setText('kpiExercisedPctMobile',exercisedPct);
      document.getElementById('pendingRequests').textContent=d.pending_requests;
      document.getElementById('openClarifications').textContent=d.open_clarifications;
      renderMonths(d.monthly,d.own_scope_only===true);
      if(document.getElementById('departmentSummary'))renderDeps(d.departments||[]);
    }catch(e){App.toast(e.message,'error')}
  }

  function showMonthDetail(month,data,button,ownOnly=false){
    chart.querySelectorAll('.chart-month-button').forEach(el=>{
      el.classList.toggle('active',el===button);
      el.setAttribute('aria-pressed',el===button?'true':'false');
    });
    const entry=Number(data?.entrada||0),output=Number(data?.salida||0);
    const href=`?view=movimientos&year=${encodeURIComponent(y.value)}&month=${month}`;
    chartDetail.innerHTML=`<div class="chart-month-copy"><small>MES SELECCIONADO</small><strong>${monthNames[month-1]} ${App.escape(y.value)}</strong></div>
      ${ownOnly?'':`<div class="chart-month-metric entry"><span>Entradas</span><b>${App.money(entry)}</b></div>`}
      <div class="chart-month-metric output"><span>${ownOnly?'Mis salidas':'Salidas'}</span><b>${App.money(output)}</b></div>
      <a class="btn btn-soft btn-sm chart-detail-link" href="${href}${ownOnly?'&tipo=SALIDA':''}">Ver movimientos <span>›</span></a>`;
    chartDetail.classList.remove('hidden');
  }

  function renderMonths(m,ownOnly=false){
    chartDetail.classList.add('hidden');
    chartDetail.innerHTML='';
    const vals=Object.values(m||{}).flatMap(x=>[Number(x.entrada||0),Number(x.salida||0)]),max=Math.max(1,...vals);
    chart.innerHTML=monthShort.map((name,i)=>{
      const month=i+1,x=m?.[month]||{entrada:0,salida:0};
      const entry=Math.max(3,Number(x.entrada||0)/max*100),output=Math.max(3,Number(x.salida||0)/max*100);
      return `<button type="button" class="month-group chart-month-button" data-month="${month}" aria-pressed="false" aria-label="${monthNames[i]}: ${ownOnly?'salidas '+App.money(x.salida):'entradas '+App.money(x.entrada)+', salidas '+App.money(x.salida)}"><span class="bars">${ownOnly?'':`<i class="bar entry" style="height:${entry}%" title="${App.money(x.entrada)}"></i>`}<i class="bar output" style="height:${output}%" title="${App.money(x.salida)}"></i></span><small>${name}</small></button>`;
    }).join('');
    chart.querySelectorAll('.chart-month-button').forEach(button=>button.addEventListener('click',()=>{
      const month=Number(button.dataset.month);
      showMonthDetail(month,m?.[month]||{entrada:0,salida:0},button,ownOnly);
    }));
  }

  function renderDeps(ds){
    const el=document.getElementById('departmentSummary');
    el.innerHTML=ds.length?ds.map(d=>`<a class="summary-item summary-item-link" href="?view=departamento-resumen&departamento_id=${d.departamento_id}&year=${y.value}" aria-label="Ver resumen de ${App.escape(d.nombre)}"><div><div class="summary-title"><span class="dept-dot" style="background:${d.color_hex}"></span><b>${App.escape(d.nombre)}</b><strong>${d.ejercido_pct}%</strong></div><div class="progress"><span style="width:${Math.min(100,d.ejercido_pct)}%"></span></div><small>${App.money(d.disponible)} disponible</small></div><span class="summary-chevron">›</span></a>`).join(''):'<div class="empty-state">Sin departamentos en tu alcance.</div>'
  }

  y?.addEventListener('change',load);
  load();
})();

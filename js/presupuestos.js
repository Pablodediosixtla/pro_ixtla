(()=>{
  const y=document.getElementById('budgetYear'),grid=document.getElementById('budgetGrid'),form=document.getElementById('budgetForm'),depSel=document.getElementById('budgetDepartment');
  App.years(y);
  let data=[];

  async function load(){
    try{
      data=await App.api('api.php?route=presupuestos/list&year='+y.value);
      grid.innerHTML=data.length?data.map(d=>{
        const pct=Math.max(0,Math.min(100,Number(d.ejercido_pct||0)));
        return `<article class="budget-card budget-card-clickable">
          <a class="budget-card-summary-link" href="?view=departamento-resumen&departamento_id=${d.departamento_id}&year=${y.value}" aria-label="Ver resumen de ${App.escape(d.nombre)}">
            <div class="budget-card-head"><div><span class="dept-dot big" style="background:${d.color_hex}"></span><div><small>${App.escape(d.codigo)}</small><h3>${App.escape(d.nombre)}</h3></div></div><span class="badge ${d.disponible>=0?'success':'danger'}">${d.ejercido_pct}% ejercido</span></div>
            <div class="budget-amount"><small>Disponible</small><strong>${App.money(d.disponible)}</strong></div>
            <div class="progress" title="${d.ejercido_pct}% ejercido"><span style="width:${pct}%"></span></div>
            <div class="budget-stats"><span><small>Asignado</small><b>${App.money(d.presupuesto_asignado)}</b></span><span><small>Entradas</small><b>${App.money(d.entradas)}</b></span><span><small>Salidas</small><b>${App.money(d.salidas)}</b></span></div>
          </a>
          <a class="btn btn-soft budget-summary-button" href="?view=departamento-resumen&departamento_id=${d.departamento_id}&year=${y.value}">Ver resumen</a>
        </article>`
      }).join(''):'<div class="empty-state"><span>▣</span><h3>Sin presupuesto</h3><p>No hay asignaciones para el ejercicio seleccionado.</p></div>';
      fillDeps();
    }catch(e){App.toast(e.message,'error')}
  }

  function fillDeps(){
    if(!depSel)return;
    depSel.innerHTML=data.map(d=>`<option value="${d.departamento_id}">${App.escape(d.nombre)}</option>`).join('');
  }

  function populate(depId){
    if(!form)return;
    const d=data.find(x=>String(x.departamento_id)===String(depId))||data[0];
    if(!d)return;
    depSel.value=d.departamento_id;
    form.ejercicio.value=y.value;
    form.presupuesto_asignado.value=d.presupuesto_asignado;
    form.observaciones.value=d.observaciones||'';
  }

  function open(depId){
    if(!form)return;
    form.reset();
    populate(depId||data[0]?.departamento_id);
    App.openModal('budgetModal');
  }

  depSel?.addEventListener('change',()=>populate(depSel.value));
  document.getElementById('assignBudgetBtn')?.addEventListener('click',()=>open(data[0]?.departamento_id));
  y?.addEventListener('change',load);
  form?.addEventListener('submit',async e=>{
    e.preventDefault();
    try{
      await App.api('api.php?route=presupuestos/save',{method:'POST',body:Object.fromEntries(new FormData(form))});
      App.closeModals();
      App.toast('Presupuesto actualizado');
      load();
    }catch(err){App.toast(err.message,'error')}
  });
  load();
})();

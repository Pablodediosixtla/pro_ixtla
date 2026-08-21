(()=>{
  const year=document.getElementById('paymentYear');
  const depFilter=document.getElementById('paymentDepartment');
  const search=document.getElementById('paymentSearch');
  const list=document.getElementById('paymentList');
  const form=document.getElementById('paymentForm');
  const depForm=document.getElementById('paymentFormDepartment');
  const subitem=document.getElementById('paymentSubitem');
  if(!year||!list)return;
  App.years(year);
  let rows=[];

  function entryCard(r,index){
    const detailId=`paymentDetail-${r.movimiento_id}-${index}`;
    const active=r.estatus==='REGISTRADO';
    return `<article class="expandable-card payment-entry-card">
      <button type="button" class="expandable-summary" aria-expanded="false" aria-controls="${detailId}">
        <span class="expandable-icon positive">+</span>
        <span class="expandable-copy"><strong>${App.escape(r.folio)}</strong><span>${App.escape(r.departamento)}</span><small>${App.escape(r.concepto||'Entrada de recurso')}</small></span>
        <span class="expandable-meta"><strong class="positive-amount">+ ${App.money(r.monto)}</strong><span class="badge ${active?'success':'danger'}">${App.escape(r.estatus)}</span><i class="expandable-chevron">⌄</i></span>
      </button>
      <div class="expandable-detail" id="${detailId}" hidden>
        <div class="expandable-detail-grid">
          <div><small>Fecha</small><strong>${App.date(r.fecha)}</strong></div>
          <div><small>Sub-item</small><strong>${App.escape(r.subitem||'Sin sub-item')}</strong></div>
          <div><small>Registrado por</small><strong>${App.escape(r.registrado_por||'—')}</strong></div>
          <div><small>Referencia</small><strong>${App.escape(r.referencia||'—')}</strong></div>
          <div class="detail-span"><small>Concepto</small><strong>${App.escape(r.concepto||'—')}</strong></div>
        </div>
        <a class="btn btn-soft expandable-open" href="?view=movimientos&movement_id=${r.movimiento_id}">Ver movimiento</a>
      </div>
    </article>`;
  }

  function render(){
    const active=rows.filter(r=>r.estatus==='REGISTRADO');
    document.getElementById('paymentTotal').textContent=App.money(active.reduce((a,r)=>a+Number(r.monto||0),0));
    document.getElementById('paymentCount').textContent=String(active.length);
    document.getElementById('paymentDepartmentCount').textContent=String(new Set(active.map(r=>r.departamento_id)).size);
    list.innerHTML=rows.length?rows.map(entryCard).join(''):'<div class="empty-state">No hay entradas registradas para los filtros seleccionados.</div>';
  }

  async function load(){
    try{
      const qs=new URLSearchParams({year:year.value,tipo:'ENTRADA'});
      if(depFilter.value)qs.set('departamento_id',depFilter.value);
      if(search.value)qs.set('q',search.value);
      rows=await App.api('api.php?route=movimientos/list&'+qs);
      render();
    }catch(e){App.toast(e.message,'error')}
  }

  async function loadSubitems(){
    const dep=depForm.value;
    if(!dep){subitem.innerHTML='<option value="">Sin sub-item</option>';return}
    try{
      const items=await App.api('api.php?route=subitems/list&departamento_id='+encodeURIComponent(dep));
      subitem.innerHTML='<option value="">Sin sub-item</option>'+items.map(s=>`<option value="${s.subitem_id}">${App.escape(s.nombre)}</option>`).join('');
    }catch(e){App.toast(e.message,'error')}
  }

  async function boot(){
    try{
      const deps=await App.api('api.php?route=departamentos/list');
      const options=deps.map(d=>`<option value="${d.departamento_id}">${App.escape(d.nombre)}</option>`).join('');
      depFilter.innerHTML='<option value="">Todos</option>'+options;
      depForm.innerHTML='<option value="">Seleccionar</option>'+options;
      if(deps.length===1)depForm.value=String(deps[0].departamento_id);
      await loadSubitems();
      await load();
    }catch(e){App.toast(e.message,'error')}
  }

  list.addEventListener('click',e=>{
    const btn=e.target.closest('.expandable-summary');
    if(btn)App.toggleExpandable(btn);
  });
  year.addEventListener('change',load);
  depFilter.addEventListener('change',load);
  search.addEventListener('input',App.debounce(load,220));
  depForm.addEventListener('change',loadSubitems);
  document.getElementById('newPaymentBtn')?.addEventListener('click',()=>{
    form.reset();
    document.getElementById('paymentDate').value=new Date().toISOString().slice(0,10);
    if(depForm.options.length===2)depForm.selectedIndex=1;
    loadSubitems();
    App.openModal('paymentModal');
  });
  form.addEventListener('submit',async e=>{
    e.preventDefault();
    const body=Object.fromEntries(new FormData(form));
    body.tipo='ENTRADA';
    body.ejercicio=year.value;
    try{
      const d=await App.api('api.php?route=movimientos/create',{method:'POST',body});
      App.closeModals();
      App.toast('Entrada registrada: '+d.folio);
      await load();
    }catch(err){App.toast(err.message,'error')}
  });
  boot();
})();

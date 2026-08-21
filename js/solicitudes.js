(()=>{
  const list=document.getElementById('requestList'),year=document.getElementById('requestYear'),status=document.getElementById('requestStatus'),depFilter=document.getElementById('requestDepartment'),form=document.getElementById('requestForm'),depForm=document.getElementById('requestFormDepartment'),subSel=document.getElementById('requestSubitem'),benefSel=document.getElementById('requestBeneficiaryUser');
  if(!list)return;
  App.years(year);
  let deps=[],requests=[];

  function badge(s){return s==='PAGADA'?'success':s==='AUTORIZADA'?'info':s==='RECHAZADA'||s==='CANCELADA'?'danger':'warning'}

  function card(r,index){
    const detailId=`requestDetail-${r.solicitud_id}-${index}`;
    const beneficiary=r.otorgado_a||r.beneficiario_nombre||'Sin beneficiario definido';
    const actions=[];
    if(App.has('SOLICITUD_APROBAR')&&r.estatus==='PENDIENTE'){
      actions.push(`<button class="btn btn-soft btn-sm" data-status="AUTORIZADA" data-id="${r.solicitud_id}">Autorizar</button>`);
      actions.push(`<button class="btn btn-ghost btn-sm" data-status="RECHAZADA" data-id="${r.solicitud_id}">Rechazar</button>`);
    }
    if(App.has('MOVIMIENTO_SALIDA_CREAR')&&r.estatus==='AUTORIZADA')actions.push(`<a class="btn btn-primary btn-sm" href="?view=nuevo-movimiento&solicitud_id=${r.solicitud_id}">Registrar salida</a>`);
    return `<article class="expandable-card request-card">
      <button type="button" class="expandable-summary" aria-expanded="false" aria-controls="${detailId}">
        <span class="expandable-icon request">▤</span>
        <span class="expandable-copy"><strong>${App.escape(r.folio)}</strong><span>${App.escape(r.departamento)}</span><small>${App.escape(r.solicitado_por||'—')}</small></span>
        <span class="expandable-meta"><strong>${App.money(r.monto_solicitado)}</strong><span class="badge ${badge(r.estatus)}">${App.escape(r.estatus)}</span><i class="expandable-chevron">⌄</i></span>
      </button>
      <div class="expandable-detail" id="${detailId}" hidden>
        <div class="expandable-detail-grid">
          <div><small>Fecha</small><strong>${App.date(r.fecha_solicitud)}</strong></div>
          <div><small>Sub-item</small><strong>${App.escape(r.subitem||'Sin sub-item')}</strong></div>
          <div><small>Solicitó</small><strong>${App.escape(r.solicitado_por||'—')}</strong></div>
          <div><small>Se otorga a</small><strong>${App.escape(beneficiary)}</strong></div>
          <div><small>Área</small><strong>${App.escape(r.area_solicitante||'—')}</strong></div>
          <div><small>Estatus</small><span class="badge ${badge(r.estatus)}">${App.escape(r.estatus)}</span></div>
          <div class="detail-span"><small>Concepto / uso</small><strong>${App.escape(r.concepto||'—')}</strong></div>
        </div>
        ${actions.length?`<div class="expandable-actions">${actions.join('')}</div>`:''}
      </div>
    </article>`;
  }

  async function boot(){
    try{
      deps=await App.api('api.php?route=departamentos/list');
      const opts=deps.map(d=>`<option value="${d.departamento_id}">${App.escape(d.nombre)}</option>`).join('');
      depFilter.innerHTML='<option value="">Todos los departamentos</option>'+opts;
      depForm.innerHTML='<option value="">Seleccionar</option>'+opts;
      await load();
    }catch(e){App.toast(e.message,'error')}
  }

  async function load(){
    try{
      const qs=new URLSearchParams({year:year.value});
      if(status.value)qs.set('estatus',status.value);
      if(depFilter.value)qs.set('departamento_id',depFilter.value);
      requests=await App.api('api.php?route=solicitudes/list&'+qs);
      list.innerHTML=requests.length?requests.map(card).join(''):'<div class="empty-state">Sin solicitudes para los filtros seleccionados.</div>';
    }catch(e){App.toast(e.message,'error')}
  }

  async function loadSubUsers(){
    const d=depForm.value;
    if(!d)return;
    try{
      const [subs,users]=await Promise.all([App.api('api.php?route=subitems/list&tipo=SALIDA&departamento_id='+d),App.api('api.php?route=departamentos/list&usuarios=1&departamento_id='+encodeURIComponent(d))]);
      subSel.innerHTML='<option value="">Sin sub-item</option>'+subs.map(s=>`<option value="${s.subitem_id}">${App.escape(s.nombre)}</option>`).join('');
      benefSel.innerHTML='<option value="">Persona externa / sin usuario</option>'+users.map(u=>`<option value="${u.usuario_id}">${App.escape(u.nombre)}</option>`).join('');
    }catch(e){App.toast(e.message,'error')}
  }

  document.getElementById('newRequestBtn')?.addEventListener('click',()=>{form.reset();document.getElementById('requestFormYear').value=year.value;if(deps.length===1)depForm.value=deps[0].departamento_id;loadSubUsers();App.openModal('requestModal')});
  depForm?.addEventListener('change',loadSubUsers);
  [year,status,depFilter].forEach(x=>x?.addEventListener('change',load));
  list.addEventListener('click',async e=>{
    const summary=e.target.closest('.expandable-summary');
    if(summary){App.toggleExpandable(summary);return}
    const id=e.target.dataset.id,st=e.target.dataset.status;
    if(!id||!st)return;
    const comment=prompt(st==='RECHAZADA'?'Motivo del rechazo:':'Comentario (opcional):')??'';
    try{await App.api('api.php?route=solicitudes/status',{method:'POST',body:{solicitud_id:id,estatus:st,comentario:comment}});App.toast('Solicitud actualizada');load()}catch(err){App.toast(err.message,'error')}
  });
  form?.addEventListener('submit',async e=>{e.preventDefault();try{await App.api('api.php?route=solicitudes/create',{method:'POST',body:Object.fromEntries(new FormData(form))});App.closeModals();App.toast('Solicitud enviada a Tesorería');load()}catch(err){App.toast(err.message,'error')}});
  boot();
})();

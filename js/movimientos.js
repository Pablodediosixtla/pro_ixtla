(()=>{
 const list=document.getElementById('movementList');if(list)initList();
 const form=document.getElementById('movementForm');if(form)initForm();
 function badge(s){return s==='REGISTRADO'?'success':'danger'}

 function initList(){
   const year=document.getElementById('movementYear'),month=document.getElementById('movementMonth'),dep=document.getElementById('movementDepartment'),type=document.getElementById('movementType'),status=document.getElementById('movementStatus'),search=document.getElementById('movementSearch'),drawer=document.getElementById('movementDrawer');
   App.years(year);
   const urlParams=new URLSearchParams(location.search);
   if(urlParams.get('year'))year.value=urlParams.get('year');
   if(urlParams.get('month'))month.value=urlParams.get('month');
   let rows=[],current=null;

   function card(r,index){
     const detailId=`movementListDetail-${r.movimiento_id}-${index}`;
     const isEntry=r.tipo==='ENTRADA';
     const beneficiary=r.otorgado_a||r.beneficiario_nombre||'Sin beneficiario';
     return `<article class="expandable-card movement-list-card">
       <button type="button" class="expandable-summary" aria-expanded="false" aria-controls="${detailId}">
         <span class="expandable-icon ${isEntry?'positive':'negative'}">${isEntry?'+':'−'}</span>
         <span class="expandable-copy"><strong>${App.escape(r.folio)}</strong><span>${App.escape(r.departamento)}</span><small>${App.escape(r.concepto||r.tipo)}</small></span>
         <span class="expandable-meta"><strong class="${isEntry?'positive-amount':'negative-amount'}">${isEntry?'+':'−'} ${App.money(r.monto)}</strong><span class="badge ${badge(r.estatus)}">${App.escape(r.estatus)}</span><i class="expandable-chevron">⌄</i></span>
       </button>
       <div class="expandable-detail" id="${detailId}" hidden>
         <div class="expandable-detail-grid">
           <div><small>Tipo</small><span class="badge ${isEntry?'info':'warning'}">${App.escape(r.tipo)}</span></div>
           <div><small>Fecha</small><strong>${App.date(r.fecha)}</strong></div>
           <div><small>Sub-item</small><strong>${App.escape(r.subitem||'Sin sub-item')}</strong></div>
           <div><small>Beneficiario</small><strong>${App.escape(beneficiary)}</strong></div>
           <div><small>Registrado por</small><strong>${App.escape(r.registrado_por||'—')}</strong></div>
           <div><small>Seguimiento</small>${r.aclaraciones_abiertas?`<span class="badge warning">${r.aclaraciones_abiertas} abierta${r.aclaraciones_abiertas===1?'':'s'}</span>`:'<span class="badge success">Sin pendientes</span>'}</div>
           <div class="detail-span"><small>Concepto</small><strong>${App.escape(r.concepto||'—')}</strong></div>
         </div>
         <div class="expandable-actions"><button class="btn btn-soft btn-sm" data-open="${r.movimiento_id}">Ver detalle</button></div>
       </div>
     </article>`;
   }

   async function boot(){
     try{
       const deps=await App.api('api.php?route=departamentos/list');
       dep.innerHTML='<option value="">Todos</option>'+deps.map(d=>`<option value="${d.departamento_id}">${App.escape(d.nombre)}</option>`).join('');
       if(urlParams.get('departamento_id'))dep.value=urlParams.get('departamento_id');
       if(window.PROIXTLA_OWN_SCOPE_ONLY){type.value='SALIDA';type.disabled=true}else if(urlParams.get('tipo'))type.value=urlParams.get('tipo');
       if(urlParams.get('q'))search.value=urlParams.get('q');
       await load();
       if(urlParams.get('movement_id'))await open(urlParams.get('movement_id'));
     }catch(e){App.toast(e.message,'error')}
   }

   async function load(){
     try{
       const qs=new URLSearchParams({year:year.value});
       if(month.value)qs.set('month',month.value);
       if(dep.value)qs.set('departamento_id',dep.value);
       if(window.PROIXTLA_OWN_SCOPE_ONLY)qs.set('tipo','SALIDA');else if(type.value)qs.set('tipo',type.value);
       if(status.value)qs.set('estatus',status.value);
       if(search.value)qs.set('q',search.value);
       rows=await App.api('api.php?route=movimientos/list&'+qs);
       list.innerHTML=rows.length?rows.map(card).join(''):'<div class="empty-state">Sin movimientos.</div>';
     }catch(e){App.toast(e.message,'error')}
   }

   async function open(id){
     try{
       current=await App.api('api.php?route=movimientos/get&id='+id);
       document.getElementById('movementDrawerTitle').textContent=current.folio;
       document.getElementById('movementDetail').innerHTML=`<div class="detail-hero"><span class="badge ${current.tipo==='ENTRADA'?'info':'warning'}">${current.tipo}</span><strong>${App.money(current.monto)}</strong><small>${App.escape(current.departamento)} · ${App.date(current.fecha)}</small></div><div class="detail-grid"><div><small>Concepto</small><b>${App.escape(current.concepto)}</b></div><div><small>Sub-item</small><b>${App.escape(current.subitem||'—')}</b></div><div><small>Solicitado por</small><b>${App.escape(current.solicitado_por||'—')}</b></div><div><small>Otorgado a</small><b>${App.escape(current.otorgado_a||current.beneficiario_nombre||'—')}</b></div><div><small>Registrado por</small><b>${App.escape(current.registrado_por)}</b></div><div><small>Método / referencia</small><b>${App.escape(current.metodo_pago||'—')} ${current.referencia?'· '+App.escape(current.referencia):''}</b></div></div><div class="file-block"><h3>Evidencias</h3>${current.files?.length?current.files.map(f=>`<a href="${f.download_url}">📎 ${App.escape(f.nombre_original)}</a>`).join(''):'<small>Sin archivos adjuntos.</small>'}</div>`;
       drawer.classList.remove('hidden');document.getElementById('globalBackdrop').classList.remove('hidden');
     }catch(e){App.toast(e.message,'error')}
   }

   list.addEventListener('click',e=>{
     const summary=e.target.closest('.expandable-summary');
     if(summary){App.toggleExpandable(summary);return}
     const openBtn=e.target.closest('[data-open]');
     if(openBtn)open(openBtn.dataset.open);
   });
   document.getElementById('closeMovementDrawer')?.addEventListener('click',()=>{drawer.classList.add('hidden');document.getElementById('globalBackdrop').classList.add('hidden')});
   document.getElementById('globalBackdrop')?.addEventListener('click',()=>drawer.classList.add('hidden'));
   document.getElementById('openClarificationBtn')?.addEventListener('click',()=>{if(!current)return;const f=document.getElementById('clarificationForm');f.reset();f.movimiento_id.value=current.movimiento_id;App.openModal('clarificationModal')});
   document.getElementById('clarificationForm')?.addEventListener('submit',async e=>{e.preventDefault();try{await App.api('api.php?route=aclaraciones/create',{method:'POST',body:Object.fromEntries(new FormData(e.target))});App.closeModals();App.toast('Aclaración creada');load()}catch(err){App.toast(err.message,'error')}});
   document.getElementById('cancelMovementBtn')?.addEventListener('click',async()=>{if(!current)return;const motivo=prompt('Motivo de cancelación:');if(!motivo)return;try{await App.api('api.php?route=movimientos/cancel',{method:'POST',body:{movimiento_id:current.movimiento_id,motivo}});drawer.classList.add('hidden');document.getElementById('globalBackdrop').classList.add('hidden');App.toast('Movimiento cancelado');load()}catch(e){App.toast(e.message,'error')}});
   [year,month,dep,type,status].forEach(x=>x?.addEventListener('change',load));
   search?.addEventListener('input',App.debounce(load));
   boot();
 }

 function initForm(){
   const form=document.getElementById('movementForm'),dep=document.getElementById('movementFormDepartment'),sub=document.getElementById('movementFormSubitem'),req=document.getElementById('authorizedRequest'),requester=document.getElementById('movementRequester'),benef=document.getElementById('movementBeneficiary'),available=document.getElementById('movementAvailable'),yearEl=document.getElementById('movementFormYear'),amount=document.getElementById('movementAmount'),warning=document.getElementById('movementBudgetWarning'),warningText=document.getElementById('movementBudgetWarningText');
   const year=new Date().getFullYear();yearEl.value=year;form.fecha.value=new Date().toISOString().slice(0,10);let budgets=[],requests=[],departmentUsers=[];

   const selectedBudget=()=>budgets.find(x=>String(x.departamento_id)===String(dep.value));
   function updateBudgetWarning(){
     const b=selectedBudget(),value=Number(amount?.value||0),availableValue=Number(b?.disponible||0),isOutput=(form.tipo?.value||'SALIDA')==='SALIDA';
     available.textContent=App.money(availableValue);
     const exceeds=!!dep.value&&isOutput&&value>availableValue&&value>0;
     warning?.classList.toggle('hidden',!exceeds);
     available.closest('.available-box')?.classList.toggle('over-budget',exceeds);
     if(exceeds&&warningText)warningText.textContent=`Disponible: ${App.money(availableValue)} · Salida: ${App.money(value)} · Excedente: ${App.money(value-availableValue)}`;
   }

   function renderDepartmentUsers(users){
     departmentUsers=users||[];
     const userOptions=departmentUsers.map(u=>`<option value="${u.usuario_id}">${App.escape(u.nombre)}${u.rol?' · '+App.escape(u.rol):''}</option>`).join('');
     requester.innerHTML='<option value="">Seleccionar usuario del departamento</option>'+userOptions;
     benef.innerHTML='<option value="">Persona externa / sin usuario</option>'+userOptions;
     const current=departmentUsers.find(u=>u.is_current);
     if(current)requester.value=String(current.usuario_id);
   }

   async function boot(){
     try{
       const [deps,b,rs]=await Promise.all([App.api('api.php?route=departamentos/list'),App.api('api.php?route=presupuestos/list&year='+year),App.api('api.php?route=solicitudes/list&year='+year+'&estatus=AUTORIZADA')]);
       budgets=b;requests=rs;
       dep.innerHTML='<option value="">Seleccionar</option>'+deps.map(d=>`<option value="${d.departamento_id}">${App.escape(d.nombre)}</option>`).join('');
       req.innerHTML='<option value="">Movimiento directo</option>'+requests.map(r=>`<option value="${r.solicitud_id}">${App.escape(r.folio)} · ${App.escape(r.departamento)} · ${App.money(r.monto_solicitado)}</option>`).join('');
       const urlId=new URLSearchParams(location.search).get('solicitud_id');
       if(urlId){req.value=urlId;await applyRequest()}else{await depChanged()}
       updateBudgetWarning();
     }catch(e){App.toast(e.message,'error')}
   }

   async function depChanged(){
     const id=dep.value;
     updateBudgetWarning();
     if(!id){
       sub.innerHTML='<option value="">Sin sub-item</option>';
       requester.innerHTML='<option value="">Selecciona primero un departamento</option>';
       benef.innerHTML='<option value="">Persona externa / sin usuario</option>';
       departmentUsers=[];
       return;
     }
     try{
       const movementType=(form.tipo?.value||'SALIDA').toUpperCase();
       const [subs,users]=await Promise.all([
         App.api('api.php?route=subitems/list&tipo='+encodeURIComponent(movementType)+'&departamento_id='+encodeURIComponent(id)),
         App.api('api.php?route=usuarios/options&departamento_id='+encodeURIComponent(id))
       ]);
       sub.innerHTML='<option value="">Sin sub-item</option>'+subs.map(s=>`<option value="${s.subitem_id}">${App.escape(s.nombre)}</option>`).join('');
       renderDepartmentUsers(users);
       if(!users.length)App.toast('No hay usuarios activos asignados a este departamento','warning');
     }catch(e){
       renderDepartmentUsers([]);
       App.toast(e.message,'error');
     }
   }

   async function applyRequest(){
     const r=requests.find(x=>String(x.solicitud_id)===String(req.value));const lock=!!r;
     if(r){
       dep.value=r.departamento_id;form.monto.value=r.monto_solicitado;form.concepto.value=r.concepto;form.area_solicitante.value=r.area_solicitante||'';form.beneficiario_nombre.value=r.beneficiario_nombre||'';
       await depChanged();
       form.subitem_id.value=r.subitem_id||'';form.solicitado_por_usuario_id.value=r.solicitado_por_usuario_id||'';form.otorgado_a_usuario_id.value=r.otorgado_a_usuario_id||'';
     }
     ['departamento_id','monto','concepto'].forEach(n=>form.elements[n].readOnly=lock&&n!=='departamento_id');dep.disabled=lock;
     updateBudgetWarning();
   }

   dep.addEventListener('change',depChanged);
   req.addEventListener('change',applyRequest);
   amount?.addEventListener('input',updateBudgetWarning);
   form.tipo?.addEventListener('change',async()=>{await depChanged();updateBudgetWarning()});
   form.addEventListener('submit',async e=>{
     e.preventDefault();
     const fd=new FormData(form);if(dep.disabled)fd.set('departamento_id',dep.value);fd.set('ejercicio',year);
     try{
       const d=await App.api('api.php?route=movimientos/create',{method:'POST',body:fd});
       if(d.warning?.code==='OVER_BUDGET')App.toast(`Movimiento registrado: ${d.folio}. Excedente: ${App.money(d.warning.excedente)}`,'warning');
       else App.toast('Movimiento registrado: '+d.folio);
       setTimeout(()=>location.href='?view=movimientos',1100)
     }catch(err){App.toast(err.message,'error')}
   });
   boot();
 }
})();

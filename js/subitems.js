(()=>{
 const list=document.getElementById('subitemList'),count=document.getElementById('subitemCount'),form=document.getElementById('subitemForm'),depSel=document.getElementById('subitemDepartment');let data=[],deps=[];
 const typeBadge=t=>t==='ENTRADA'?'<span class="badge success">Entrada</span>':'<span class="badge warning">Salida</span>';
 const statusBadge=s=>`<span class="badge ${s==='ACTIVO'?'success':'neutral'}">${App.escape(s)}</span>`;
 const iconClass=t=>t==='ENTRADA'?'positive':'negative';
 const iconText=t=>t==='ENTRADA'?'↗':'↘';
 async function boot(){try{deps=await App.api('api.php?route=departamentos/list&all=1');depSel.innerHTML='<option value="">Global / Todos</option>'+deps.map(d=>`<option value="${d.departamento_id}">${App.escape(d.nombre)}</option>`).join('');await load()}catch(e){App.toast(e.message,'error')}}
 function card(s,index){
   const detailId=`subitemDetail-${s.subitem_id}`;
   const scope=s.departamento||'Global / Todos';
   const description=(s.descripcion||'').trim()||'Sin descripción';
   return `<article class="expandable-card subitem-expandable-card" data-subitem-card="${s.subitem_id}">
     <button class="expandable-summary" type="button" aria-expanded="false" aria-controls="${detailId}" data-expand-subitem="${s.subitem_id}">
       <span class="expandable-icon ${iconClass(s.tipo)}">${iconText(s.tipo)}</span>
       <span class="expandable-copy"><strong>${App.escape(s.nombre)}</strong><span>${App.escape(s.codigo)} · ${App.escape(scope)}</span><small>${App.escape(description)}</small></span>
       <span class="expandable-meta subitem-expandable-meta"><span>${typeBadge(s.tipo)}</span><span>${statusBadge(s.estatus)}</span><i class="expandable-chevron">⌄</i></span>
     </button>
     <div class="expandable-detail" id="${detailId}" hidden>
       <div class="expandable-detail-grid">
         <div><small>Código</small><strong>${App.escape(s.codigo)}</strong></div>
         <div><small>Tipo</small><strong>${s.tipo==='ENTRADA'?'Entrada':'Salida'}</strong></div>
         <div><small>Departamento</small><strong>${App.escape(scope)}</strong></div>
         <div><small>Estatus</small>${statusBadge(s.estatus)}</div>
         <div class="detail-span"><small>Descripción</small><strong>${App.escape(description)}</strong></div>
       </div>
       <div class="expandable-actions"><button class="btn btn-soft expandable-open" type="button" data-edit="${s.subitem_id}">Editar sub-item</button></div>
     </div>
   </article>`;
 }
 async function load(){try{data=await App.api('api.php?route=subitems/list&all=1');list.innerHTML=data.length?data.map(card).join(''):'<div class="empty-state">No hay sub-items configurados.</div>';if(count)count.textContent=`${data.length} registro${data.length===1?'':'s'}`;}catch(e){App.toast(e.message,'error')}}
 function open(s={}){form.reset();form.subitem_id.value=s.subitem_id||'';form.codigo.value=s.codigo||'';form.nombre.value=s.nombre||'';form.tipo.value=s.tipo||'SALIDA';form.departamento_id.value=s.departamento_id||'';form.descripcion.value=s.descripcion||'';form.estatus.value=s.estatus||'ACTIVO';App.openModal('subitemModal')}
 list?.addEventListener('click',e=>{
   const edit=e.target.closest('[data-edit]');
   if(edit){e.stopPropagation();open(data.find(x=>String(x.subitem_id)===String(edit.dataset.edit)));return}
   const toggle=e.target.closest('[data-expand-subitem]');
   if(toggle)App.toggleExpandable(toggle);
 });
 document.getElementById('newSubitemBtn')?.addEventListener('click',()=>open());
 form?.addEventListener('submit',async e=>{e.preventDefault();try{await App.api('api.php?route=subitems/save',{method:'POST',body:Object.fromEntries(new FormData(form))});App.closeModals();App.toast('Sub-item guardado');load()}catch(err){App.toast(err.message,'error')}});
 boot();
})();

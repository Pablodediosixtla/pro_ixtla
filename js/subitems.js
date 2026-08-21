(()=>{
 const table=document.getElementById('subitemTable'),form=document.getElementById('subitemForm'),depSel=document.getElementById('subitemDepartment');let data=[],deps=[];
 const typeBadge=t=>t==='ENTRADA'?'<span class="badge success">Entrada</span>':'<span class="badge warning">Salida</span>';
 async function boot(){try{deps=await App.api('api.php?route=departamentos/list&all=1');depSel.innerHTML='<option value="">Global / Todos</option>'+deps.map(d=>`<option value="${d.departamento_id}">${App.escape(d.nombre)}</option>`).join('');await load()}catch(e){App.toast(e.message,'error')}}
 async function load(){try{data=await App.api('api.php?route=subitems/list&all=1');table.innerHTML=data.map(s=>`<tr><td><code>${App.escape(s.codigo)}</code></td><td><b>${App.escape(s.nombre)}</b><small class="cell-note">${App.escape(s.descripcion||'')}</small></td><td>${typeBadge(s.tipo)}</td><td>${App.escape(s.departamento||'Global')}</td><td><span class="badge ${s.estatus==='ACTIVO'?'success':'neutral'}">${App.escape(s.estatus)}</span></td><td><button class="btn btn-soft btn-sm" data-edit="${s.subitem_id}">Editar</button></td></tr>`).join('')}catch(e){App.toast(e.message,'error')}}
 function open(s={}){form.reset();form.subitem_id.value=s.subitem_id||'';form.codigo.value=s.codigo||'';form.nombre.value=s.nombre||'';form.tipo.value=s.tipo||'SALIDA';form.departamento_id.value=s.departamento_id||'';form.descripcion.value=s.descripcion||'';form.estatus.value=s.estatus||'ACTIVO';App.openModal('subitemModal')}
 table?.addEventListener('click',e=>{const id=e.target.dataset.edit;if(id)open(data.find(x=>String(x.subitem_id)===id))});
 document.getElementById('newSubitemBtn')?.addEventListener('click',()=>open());
 form?.addEventListener('submit',async e=>{e.preventDefault();try{await App.api('api.php?route=subitems/save',{method:'POST',body:Object.fromEntries(new FormData(form))});App.closeModals();App.toast('Sub-item guardado');load()}catch(err){App.toast(err.message,'error')}});
 boot();
})();

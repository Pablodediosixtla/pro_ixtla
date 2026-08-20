(()=>{
  const grid=document.getElementById('departmentGrid'),search=document.getElementById('departmentSearch'),modal=document.getElementById('departmentModal'),form=document.getElementById('departmentForm');
  let data=[];
  async function load(){
    try{
      const q=encodeURIComponent(search?.value||'');data=await App.api('api.php?route=departamentos/list&all=1&q='+q);
      grid.innerHTML=data.length?data.map(d=>`<article class="department-card" style="--dept-accent:${d.color_hex}"><div class="department-card-head"><span class="dept-icon" style="--dept:${d.color_hex}">🏛</span><span class="badge ${d.estatus==='ACTIVO'?'success':'neutral'}">${d.estatus}</span></div><div><small>${d.codigo}${d.es_tesoreria?' · TESORERÍA':''}</small><h3>${d.nombre}</h3><p>${d.descripcion||'Área municipal registrada para control presupuestal.'}</p></div><div class="department-stats"><span><b>${d.usuarios_count}</b><small>usuarios</small></span></div><button class="btn btn-soft" data-edit="${d.departamento_id}">Configurar departamento</button></article>`).join(''):'<div class="empty-state"><span>🏛</span><h3>Sin departamentos</h3><p>No hay departamentos que coincidan con la búsqueda.</p></div>'
    }catch(e){App.toast(e.message,'error')}
  }
  function open(d={}){form.reset();form.departamento_id.value=d.departamento_id||'';form.codigo.value=d.codigo||'';form.nombre.value=d.nombre||'';form.descripcion.value=d.descripcion||'';form.color_hex.value=d.color_hex||'#6F8F79';form.icono.value=d.icono||'building';form.es_tesoreria.checked=Number(d.es_tesoreria||0)===1;form.estatus.value=d.estatus||'ACTIVO';document.getElementById('departmentModalTitle').textContent=d.departamento_id?'Editar departamento':'Nuevo departamento';App.openModal('departmentModal')}
  grid?.addEventListener('click',e=>{const id=e.target.dataset.edit;if(id)open(data.find(x=>String(x.departamento_id)===id))});
  document.getElementById('newDepartmentBtn')?.addEventListener('click',()=>open());
  form?.addEventListener('submit',async e=>{e.preventDefault();const o=Object.fromEntries(new FormData(form));o.es_tesoreria=form.es_tesoreria.checked?1:0;try{await App.api('api.php?route=departamentos/save',{method:'POST',body:o});App.closeModals();App.toast('Departamento guardado');load()}catch(err){App.toast(err.message,'error')}});
  search?.addEventListener('input',App.debounce(load));load()
})();

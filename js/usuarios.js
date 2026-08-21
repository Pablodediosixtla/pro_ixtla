(()=>{
  const table=document.getElementById('userTable');
  const search=document.getElementById('userSearch');
  const form=document.getElementById('userForm');
  const roleSel=document.getElementById('userRole');
  const depSel=document.getElementById('userDepartment');
  const bossSel=document.getElementById('userBoss');
  let users=[],roles=[],deps=[];

  async function boot(){
    try{
      const [r,d]=await Promise.all([
        App.api('api.php?route=roles/list'),
        App.api('api.php?route=departamentos/list&all=1')
      ]);
      roles=r.roles;
      deps=d;
      roleSel.innerHTML='<option value="">Seleccionar rol</option>'+
        roles.filter(x=>x.estatus==='ACTIVO').map(x=>
          `<option value="${x.rol_id}" data-scope="${App.escape(x.alcance)}">${App.escape(x.nombre)} · ${App.escape(x.alcance)}</option>`
        ).join('');
      depSel.innerHTML='<option value="">Sin departamento / Global</option>'+
        deps.filter(x=>x.estatus==='ACTIVO').map(x=>
          `<option value="${x.departamento_id}">${App.escape(x.nombre)}</option>`
        ).join('');
      await load();
    }catch(e){App.toast(e.message,'error')}
  }

  async function load(){
    try{
      users=await App.api('api.php?route=usuarios/list&q='+encodeURIComponent(search?.value||''));
      table.innerHTML=users.length?users.map(u=>{
        const a=u.assignments[0]||{};
        return `<tr>
          <td><div class="table-user"><span class="avatar-sm">${App.escape((u.nombre||'U')[0])}</span><div><b>${App.escape(u.nombre_completo)}</b><small>@${App.escape(u.username)}${u.puesto?' · '+App.escape(u.puesto):''}</small></div></div></td>
          <td>${App.escape(a.departamento||'Global')}</td>
          <td><span class="badge info">${App.escape(a.rol||'Sin rol')}</span></td>
          <td>${App.escape(a.jefe_nombre||'—')}</td>
          <td><span class="badge ${u.estatus==='ACTIVO'?'success':'neutral'}">${App.escape(u.estatus)}</span></td>
          <td><button class="btn btn-soft btn-sm" data-edit="${u.usuario_id}">Editar</button></td>
        </tr>`;
      }).join(''):'<tr><td colspan="6" class="empty-state">Sin usuarios</td></tr>';
      await fillBosses();
    }catch(e){App.toast(e.message,'error')}
  }

  function selectedRole(){
    const roleId=Number(roleSel?.value||0);
    return roles.find(r=>Number(r.rol_id)===roleId)||null;
  }

  function syncRoleScope(){
    const role=selectedRole();
    const isGlobal=role?.alcance==='GLOBAL';
    if(isGlobal){
      depSel.value='';
      depSel.disabled=true;
      depSel.closest('label')?.classList.add('field-disabled');
      bossSel.value='';
      bossSel.disabled=true;
      bossSel.closest('label')?.classList.add('field-disabled');
    }else{
      depSel.disabled=false;
      depSel.closest('label')?.classList.remove('field-disabled');
      bossSel.disabled=false;
      bossSel.closest('label')?.classList.remove('field-disabled');
    }
  }

  async function fillBosses(selectedBoss=''){
    const current=form.usuario_id.value;
    const dep=depSel.value;
    const role=selectedRole();
    if(role?.alcance==='GLOBAL'||!dep){
      bossSel.innerHTML='<option value="">Sin jefe</option>';
      bossSel.value='';
      return;
    }
    try{
      const opts=App.catalogs?.loaded ? App.catalogUsers(dep) : await App.api('api.php?route=usuarios/options&departamento_id='+encodeURIComponent(dep));
      bossSel.innerHTML='<option value="">Sin jefe</option>'+
        opts.filter(x=>String(x.usuario_id)!==String(current)).map(x=>
          `<option value="${x.usuario_id}">${App.escape(x.nombre)} · ${App.escape(x.rol||'')}</option>`
        ).join('');
      if(selectedBoss!=='')bossSel.value=String(selectedBoss);
    }catch(e){
      bossSel.innerHTML='<option value="">Sin jefe</option>';
    }
  }

  async function open(u={}){
    form.reset();
    form.usuario_id.value=u.usuario_id||'';
    form.username.value=u.username||'';
    form.nombre.value=u.nombre||'';
    form.apellido_paterno.value=u.apellido_paterno||'';
    form.apellido_materno.value=u.apellido_materno||'';
    form.email.value=u.email||'';
    form.telefono.value=u.telefono||'';
    form.puesto.value=u.puesto||'';
    form.estatus.value=u.estatus||'ACTIVO';
    form.password.required=!u.usuario_id;
    form.password.placeholder=u.usuario_id?'Dejar vacío para conservar':'Mínimo 8 caracteres';

    const a=u.assignments?.find(x=>x.assignment_id)||u.assignments?.[0]||{};
    roleSel.value=a.rol_id?String(a.rol_id):'';
    depSel.disabled=false;
    depSel.value=a.departamento_id?String(a.departamento_id):'';
    bossSel.disabled=false;
    syncRoleScope();

    document.getElementById('userModalTitle').textContent=u.usuario_id?'Editar usuario':'Nuevo usuario';
    await fillBosses(a.jefe_usuario_id||'');
    App.openModal('userModal');
  }

  table?.addEventListener('click',e=>{
    const btn=e.target.closest?.('[data-edit]');
    const id=btn?.dataset.edit;
    if(id)open(users.find(x=>String(x.usuario_id)===String(id)));
  });

  document.getElementById('newUserBtn')?.addEventListener('click',()=>open());

  depSel?.addEventListener('change',()=>fillBosses());
  roleSel?.addEventListener('change',async()=>{
    syncRoleScope();
    await fillBosses();
  });

  form?.addEventListener('submit',async e=>{
    e.preventDefault();

    const role=selectedRole();
    if(!role){
      App.toast('Selecciona un rol','error');
      return;
    }
    if(role.alcance!=='GLOBAL'&&!depSel.value){
      App.toast('Este rol requiere seleccionar un departamento','error');
      depSel.focus();
      return;
    }

    // FormData no incluye selects disabled. Los agregamos de forma explícita
    // para que el contrato enviado al API sea determinista.
    const o=Object.fromEntries(new FormData(form));
    o.rol_id=String(roleSel.value||'');
    o.departamento_id=role.alcance==='GLOBAL'?'':String(depSel.value||'');
    o.jefe_usuario_id=role.alcance==='GLOBAL'?'':String(bossSel.value||'');

    const submit=form.querySelector('button[type="submit"]');
    const oldText=submit?.textContent||'Guardar usuario';
    if(submit){submit.disabled=true;submit.textContent='Guardando…';}

    try{
      const saved=await App.api('api.php?route=usuarios/save',{method:'POST',body:o});
      App.closeModals();
      App.toast(saved?.created?'Usuario, rol y departamento creados':'Usuario, rol y departamento actualizados');
      await load();

      // Verificación cliente adicional: el listado debe reflejar lo persistido.
      const refreshed=users.find(x=>Number(x.usuario_id)===Number(saved.usuario_id));
      const assignment=refreshed?.assignments?.[0];
      if(saved?.assignment&&assignment){
        const sameRole=Number(assignment.rol_id)===Number(saved.assignment.rol_id);
        const aDep=assignment.departamento_id===null?null:Number(assignment.departamento_id);
        const sDep=saved.assignment.departamento_id===null?null:Number(saved.assignment.departamento_id);
        if(!sameRole||aDep!==sDep){
          App.toast('El usuario se guardó, pero la vista no coincide con la asignación persistida. Recarga la página.','error');
        }
      }
    }catch(err){
      App.toast(err.message,'error');
    }finally{
      if(submit){submit.disabled=false;submit.textContent=oldText;}
    }
  });

  search?.addEventListener('input',App.debounce(load));
  boot();
})();

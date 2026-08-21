(()=>{
  const list=document.getElementById('auditList'),search=document.getElementById('auditSearch');
  if(!list)return;
  function card(r,index){
    const detailId=`auditDetail-${r.bitacora_id}-${index}`;
    const entity=`${r.entidad||'Sistema'}${r.entidad_id?' #'+r.entidad_id:''}`;
    return `<article class="expandable-card audit-card">
      <button type="button" class="expandable-summary" aria-expanded="false" aria-controls="${detailId}">
        <span class="expandable-icon audit">✓</span>
        <span class="expandable-copy"><strong>${App.escape(r.accion||'Evento')}</strong><span>${App.escape(r.usuario||'Sistema')}</span><small>${App.escape(entity)}</small></span>
        <span class="expandable-meta"><strong class="audit-date">${App.datetime(r.created_at)}</strong><span class="badge info">Auditoría</span><i class="expandable-chevron">⌄</i></span>
      </button>
      <div class="expandable-detail" id="${detailId}" hidden>
        <div class="expandable-detail-grid">
          <div><small>Usuario</small><strong>${App.escape(r.usuario||'Sistema')}</strong></div>
          <div><small>Cuenta</small><strong>${r.username?'@'+App.escape(r.username):'—'}</strong></div>
          <div><small>Entidad</small><strong>${App.escape(entity)}</strong></div>
          <div><small>IP</small><strong>${App.escape(r.ip||'—')}</strong></div>
          <div class="detail-span"><small>Descripción</small><strong>${App.escape(r.descripcion||'Sin descripción')}</strong></div>
        </div>
      </div>
    </article>`;
  }
  async function load(){
    try{
      const rows=await App.api('api.php?route=bitacora/list&q='+encodeURIComponent(search.value||''));
      list.innerHTML=rows.length?rows.map(card).join(''):'<div class="empty-state">Sin registros.</div>';
    }catch(e){App.toast(e.message,'error')}
  }
  list.addEventListener('click',e=>{const summary=e.target.closest('.expandable-summary');if(summary)App.toggleExpandable(summary)});
  search.addEventListener('input',App.debounce(load));
  load();
})();

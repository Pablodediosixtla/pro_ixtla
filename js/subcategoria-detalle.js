(()=>{
  const departmentId=Number(window.PROIXTLA_DEPARTMENT_ID||0);
  const subitemId=Number(window.PROIXTLA_SUBITEM_ID||0);
  const year=document.getElementById('subcategoryYear');
  const search=document.getElementById('subcategorySearch');
  const tbody=document.getElementById('subcategoryMovementTable');
  const cards=document.getElementById('subcategoryMovementCards');
  const drawer=document.getElementById('subcategoryMovementDrawer');
  const drawerTitle=document.getElementById('subcategoryMovementDrawerTitle');
  const drawerDetail=document.getElementById('subcategoryMovementDetail');
  const backdrop=document.getElementById('globalBackdrop');
  let rows=[];
  let currentMovement=null;

  App.years(year);
  if(window.PROIXTLA_REQUESTED_YEAR)year.value=String(window.PROIXTLA_REQUESTED_YEAR);

  function updateUrl(){
    const url=new URL(location.href);
    url.searchParams.set('view','subcategoria-detalle');
    url.searchParams.set('departamento_id',departmentId);
    url.searchParams.set('subitem_id',subitemId);
    url.searchParams.set('year',year.value);
    history.replaceState({},'',url);
  }

  function filteredRows(){
    const q=(search?.value||'').trim().toLowerCase();
    if(!q)return rows;
    return rows.filter(r=>[r.folio,r.concepto,r.otorgado_a,r.solicitado_por,r.registrado_por,r.referencia]
      .some(v=>String(v||'').toLowerCase().includes(q)));
  }

  function trackingBadge(r){
    return r.aclaraciones_abiertas
      ?`<span class="badge warning">${r.aclaraciones_abiertas} abierta${r.aclaraciones_abiertas===1?'':'s'}</span>`
      :'<span class="badge success">Sin pendientes</span>';
  }

  function renderDesktop(filtered){
    tbody.innerHTML=filtered.length?filtered.map(r=>`<tr>
      <td><button class="link-btn subcategory-movement-open-link" type="button" data-view-movement="${r.movimiento_id}">${App.escape(r.folio)}</button></td>
      <td>${App.date(r.fecha)}</td>
      <td><div class="table-primary-cell"><b>${App.escape(r.concepto)}</b>${r.referencia?`<small>${App.escape(r.referencia)}</small>`:''}</div></td>
      <td>${App.escape(r.otorgado_a||'—')}</td>
      <td>${App.escape(r.registrado_por||'—')}</td>
      <td class="amount">${App.money(r.monto)}</td>
      <td>${trackingBadge(r)}</td>
      <td><button class="btn btn-soft btn-sm" type="button" data-view-movement="${r.movimiento_id}">Ver</button></td>
    </tr>`).join(''):'<tr><td colspan="8" class="empty-state">No hay registros que coincidan con la búsqueda.</td></tr>';
    App.decorateTables(tbody.closest('.table-wrap'));
  }

  function renderMobile(filtered){
    if(!cards)return;
    cards.innerHTML=filtered.length?filtered.map((r,index)=>{
      const detailId=`movementActivityDetail-${r.movimiento_id}-${index}`;
      const beneficiary=App.escape(r.otorgado_a||'—');
      const registered=App.escape(r.registrado_por||'—');
      const reference=r.referencia?`<span class="activity-reference">${App.escape(r.referencia)}</span>`:'';
      return `<article class="movement-activity-card">
        <button class="movement-activity-summary" type="button" aria-expanded="false" aria-controls="${detailId}">
          <span class="movement-activity-icon" aria-hidden="true">−</span>
          <span class="movement-activity-copy">
            <strong>${App.escape(r.folio)}</strong>
            <span>${App.escape(r.concepto||'Salida registrada')}</span>
            <small>${App.date(r.fecha)}${r.otorgado_a?` · ${beneficiary}`:''}</small>
          </span>
          <span class="movement-activity-amount">
            <strong>− ${App.money(r.monto)}</strong>
            <span class="movement-activity-chevron" aria-hidden="true">⌄</span>
          </span>
        </button>
        <div class="movement-activity-detail" id="${detailId}" hidden>
          <div class="movement-activity-detail-grid">
            <div><small>Otorgado a</small><strong>${beneficiary}</strong></div>
            <div><small>Registrado por</small><strong>${registered}</strong></div>
            <div><small>Fecha</small><strong>${App.date(r.fecha)}</strong></div>
            <div><small>Seguimiento</small>${trackingBadge(r)}</div>
          </div>
          ${reference}
          <button class="btn btn-soft movement-activity-open" type="button" data-view-movement="${r.movimiento_id}">Ver movimiento</button>
        </div>
      </article>`;
    }).join(''):'<div class="empty-state subcategory-card-empty">No hay registros que coincidan con la búsqueda.</div>';
  }

  function renderRows(){
    const filtered=filteredRows();
    renderDesktop(filtered);
    renderMobile(filtered);
  }

  function toggleCard(button){
    const detailId=button.getAttribute('aria-controls');
    const detail=detailId?document.getElementById(detailId):null;
    if(!detail)return;
    const isOpen=button.getAttribute('aria-expanded')==='true';
    button.setAttribute('aria-expanded',String(!isOpen));
    detail.hidden=isOpen;
    button.closest('.movement-activity-card')?.classList.toggle('open',!isOpen);
  }

  function movementDetailHtml(r){
    const beneficiary=r.otorgado_a||r.beneficiario_nombre||'—';
    return `<div class="detail-hero">
      <span class="badge ${r.tipo==='ENTRADA'?'info':'warning'}">${App.escape(r.tipo)}</span>
      <strong>${App.money(r.monto)}</strong>
      <small>${App.escape(r.departamento)} · ${App.date(r.fecha)}</small>
    </div>
    <div class="detail-grid">
      <div><small>Concepto</small><b>${App.escape(r.concepto||'—')}</b></div>
      <div><small>Sub-item</small><b>${App.escape(r.subitem||'—')}</b></div>
      <div><small>Solicitado por</small><b>${App.escape(r.solicitado_por||'—')}</b></div>
      <div><small>Otorgado a</small><b>${App.escape(beneficiary)}</b></div>
      <div><small>Registrado por</small><b>${App.escape(r.registrado_por||'—')}</b></div>
      <div><small>Método / referencia</small><b>${App.escape(r.metodo_pago||'—')}${r.referencia?' · '+App.escape(r.referencia):''}</b></div>
    </div>
    <div class="file-block"><h3>Evidencias</h3>${r.files?.length?r.files.map(f=>`<a href="${f.download_url}">📎 ${App.escape(f.nombre_original)}</a>`).join(''):'<small>Sin archivos adjuntos.</small>'}</div>`;
  }

  async function openMovement(id){
    if(!drawer||!drawerDetail)return;
    try{
      currentMovement=await App.api('api.php?route=movimientos/get&id='+encodeURIComponent(id));
      drawerTitle.textContent=currentMovement.folio||'Movimiento';
      drawerDetail.innerHTML=movementDetailHtml(currentMovement);
      drawer.classList.remove('hidden');
      backdrop?.classList.remove('hidden');
      document.body.style.overflow='hidden';
    }catch(e){App.toast(e.message,'error')}
  }

  function closeMovement(){
    drawer?.classList.add('hidden');
    backdrop?.classList.add('hidden');
    document.body.style.overflow='';
    currentMovement=null;
  }

  tbody?.addEventListener('click',e=>{
    const btn=e.target.closest('[data-view-movement]');
    if(btn)openMovement(btn.dataset.viewMovement);
  });

  cards?.addEventListener('click',e=>{
    const openBtn=e.target.closest('[data-view-movement]');
    if(openBtn){openMovement(openBtn.dataset.viewMovement);return}
    const summary=e.target.closest('.movement-activity-summary');
    if(summary)toggleCard(summary);
  });

  document.getElementById('closeSubcategoryMovementDrawer')?.addEventListener('click',closeMovement);
  document.getElementById('continueSubcategoryReview')?.addEventListener('click',closeMovement);
  backdrop?.addEventListener('click',()=>{if(drawer&&!drawer.classList.contains('hidden'))closeMovement()});
  document.addEventListener('keydown',e=>{if(e.key==='Escape'&&drawer&&!drawer.classList.contains('hidden'))closeMovement()});

  async function load(){
    updateUrl();
    try{
      const d=await App.api(`api.php?route=subitems/detalle&departamento_id=${departmentId}&subitem_id=${subitemId}&year=${year.value}`);
      const dep=d.department,c=d.category,t=d.totals;
      document.getElementById('subcategoryTitle').textContent=c.nombre;
      document.getElementById('subcategoryDescription').textContent=c.descripcion||'Detalle de salidas registradas en esta subcategoría.';
      document.getElementById('subcategoryCode').textContent=c.codigo||'CAT';
      document.getElementById('subcategoryDepartment').textContent=dep.nombre;
      document.getElementById('subcategoryHeroName').textContent=c.nombre;
      document.getElementById('subcategoryHero').style.setProperty('--dept-color',dep.color_hex||'#31513f');
      document.getElementById('subcategoryShareHero').textContent=`${Number(t.participacion_pct||0).toFixed(1)}%`;
      document.getElementById('subcategoryOutputs').textContent=App.money(t.salidas);
      document.getElementById('subcategoryShare').textContent=`${Number(t.participacion_pct||0).toFixed(1)}%`;
      document.getElementById('subcategoryCount').textContent=String(t.registros||0);
      document.getElementById('subcategoryLast').textContent=t.ultima_salida?App.date(t.ultima_salida):'—';
      document.getElementById('subcategoryBackLink').href=`?view=departamento-resumen&departamento_id=${departmentId}&year=${year.value}`;
      rows=d.movements||[];
      renderRows();
    }catch(e){App.toast(e.message,'error')}
  }

  year?.addEventListener('change',load);
  search?.addEventListener('input',App.debounce(renderRows,160));
  load();
})();

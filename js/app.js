const App={
  user:window.PROIXTLA_USER||{},
  csrf:window.PROIXTLA_CSRF||'',
  async api(url,opts={}){
    const firstQ=url.indexOf('?');
    if(firstQ>=0){const secondQ=url.indexOf('?',firstQ+1);if(secondQ>=0)url=url.slice(0,secondQ)+'&'+url.slice(secondQ+1)}
    const o={...opts};o.headers={...(o.headers||{})};
    if(o.body&&!(o.body instanceof FormData)){o.headers['Content-Type']='application/json';if(typeof o.body!=='string')o.body=JSON.stringify(o.body)}
    if((o.method||'GET').toUpperCase()!=='GET')o.headers['X-CSRF-Token']=this.csrf;
    const r=await fetch(url,o),text=await r.text();let j;
    try{j=JSON.parse(text)}catch{throw new Error(`El servicio respondió contenido no válido (HTTP ${r.status})`)}
    if(!r.ok||!j.ok)throw new Error(j.error||'Error de servicio');return j.data
  },
  money(v){return new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(Number(v||0))},
  date(v){if(!v)return '—';const d=new Date(v.length===10?v+'T12:00:00':v);return d.toLocaleDateString('es-MX',{day:'2-digit',month:'short',year:'numeric'})},
  toast(msg,type='ok'){const root=document.getElementById('toastRoot');if(!root)return;const el=document.createElement('div');el.className='toast '+type;el.textContent=msg;root.appendChild(el);setTimeout(()=>el.remove(),3600)},
  years(select){if(!select)return;const y=new Date().getFullYear();select.innerHTML='';for(let i=y+1;i>=y-4;i--){const o=document.createElement('option');o.value=i;o.textContent=i;if(i===y)o.selected=true;select.appendChild(o)}},
  openModal(id){document.getElementById(id)?.classList.remove('hidden');document.getElementById('globalBackdrop')?.classList.remove('hidden');document.body.style.overflow='hidden'},
  closeModals(){document.querySelectorAll('.modal').forEach(m=>m.classList.add('hidden'));document.getElementById('globalBackdrop')?.classList.add('hidden');document.body.style.overflow=''},
  has(p){return(this.user.permissions||[]).includes(p)},
  debounce(fn,ms=280){let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms)}},
  decorateTables(root=document){
    root.querySelectorAll('.table-wrap table').forEach(table=>{
      table.classList.add('responsive-table');
      const headers=[...table.querySelectorAll('thead th')].map(th=>th.textContent.trim());
      table.querySelectorAll('tbody tr').forEach(tr=>{
        [...tr.children].forEach((td,i)=>{if(td.tagName==='TD'&&!td.hasAttribute('colspan'))td.dataset.label=headers[i]||''})
      })
    })
  }
};
window.App=App;

document.querySelectorAll('[data-close-modal]').forEach(b=>b.addEventListener('click',()=>App.closeModals()));
document.getElementById('globalBackdrop')?.addEventListener('click',()=>App.closeModals());

const sidebar=document.getElementById('sidebar');
const sidebarBackdrop=document.getElementById('sidebarBackdrop');
function openSidebar(){sidebar?.classList.add('open');sidebarBackdrop?.classList.add('show');document.body.style.overflow='hidden'}
function closeSidebar(){sidebar?.classList.remove('open');sidebarBackdrop?.classList.remove('show');document.body.style.overflow=''}
document.getElementById('openSidebar')?.addEventListener('click',openSidebar);
document.getElementById('closeSidebar')?.addEventListener('click',closeSidebar);
sidebarBackdrop?.addEventListener('click',closeSidebar);
sidebar?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{if(innerWidth<=860)closeSidebar()}));
window.addEventListener('resize',()=>{if(innerWidth>860)closeSidebar()});

document.getElementById('logoutBtn')?.addEventListener('click',async()=>{try{await App.api('api.php?route=auth/logout',{method:'POST',body:{}})}catch{}location.href='index.php'});

App.decorateTables();
const tableObserver=new MutationObserver(mutations=>{
  for(const m of mutations){if(m.type==='childList'&&m.target.closest?.('.table-wrap')){App.decorateTables(m.target.closest('.table-wrap'));break}}
});
document.querySelectorAll('.table-wrap tbody').forEach(t=>tableObserver.observe(t,{childList:true,subtree:true}));

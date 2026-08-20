const App={
  user:window.PROIXTLA_USER||{},csrf:window.PROIXTLA_CSRF||'',
  async api(url,opts={}){const o={...opts};o.headers={...(o.headers||{})};if(o.body&&!(o.body instanceof FormData)){o.headers['Content-Type']='application/json';if(typeof o.body!=='string')o.body=JSON.stringify(o.body);}if((o.method||'GET').toUpperCase()!=='GET')o.headers['X-CSRF-Token']=this.csrf;const r=await fetch(url,o);const text=await r.text();let j;try{j=JSON.parse(text)}catch{throw new Error(`El servicio respondió contenido no válido (HTTP ${r.status})`)}if(!r.ok||!j.ok)throw new Error(j.error||'Error de servicio');return j.data},
  money(v){return new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(Number(v||0))},
  date(v){if(!v)return '—';const d=new Date(v.length===10?v+'T12:00:00':v);return d.toLocaleDateString('es-MX',{day:'2-digit',month:'short',year:'numeric'})},
  toast(msg,type='ok'){const root=document.getElementById('toastRoot');if(!root)return;const el=document.createElement('div');el.className='toast '+type;el.textContent=msg;root.appendChild(el);setTimeout(()=>el.remove(),3600)},
  years(select){if(!select)return;const y=new Date().getFullYear();select.innerHTML='';for(let i=y+1;i>=y-4;i--){const o=document.createElement('option');o.value=i;o.textContent=i;if(i===y)o.selected=true;select.appendChild(o)}},
  openModal(id){document.getElementById(id)?.classList.remove('hidden');document.getElementById('globalBackdrop')?.classList.remove('hidden')},
  closeModals(){document.querySelectorAll('.modal').forEach(m=>m.classList.add('hidden'));document.getElementById('globalBackdrop')?.classList.add('hidden')},
  has(p){return (this.user.permissions||[]).includes(p)},
  debounce(fn,ms=280){let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms)}}
};
window.App=App;
document.querySelectorAll('[data-close-modal]').forEach(b=>b.addEventListener('click',()=>App.closeModals()));document.getElementById('globalBackdrop')?.addEventListener('click',()=>App.closeModals());
const sidebar=document.getElementById('sidebar');document.getElementById('openSidebar')?.addEventListener('click',()=>sidebar?.classList.add('open'));document.getElementById('closeSidebar')?.addEventListener('click',()=>sidebar?.classList.remove('open'));
document.getElementById('logoutBtn')?.addEventListener('click',async()=>{try{await App.api('db/web/auth/logout.php',{method:'POST',body:{}})}catch{}location.href='index.php'});

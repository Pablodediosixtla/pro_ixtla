window.Ixtla={
  csrf:'',user:window.APP_USER||null,authMode:'',dataMode:'',
  api:async function(url,options={}){
    options.headers=options.headers||{};
    if(options.body&&!(options.body instanceof FormData)&&typeof options.body!=='string'){
      options.headers['Content-Type']='application/json';options.body=JSON.stringify(options.body);
    }
    if((options.method||'GET').toUpperCase()!=='GET'&&this.csrf)options.headers['X-CSRF-Token']=this.csrf;
    const r=await fetch(url,options);const text=await r.text();let j;
    try{j=JSON.parse(text);}catch{throw Object.assign(new Error(`El servicio devolvió una respuesta no JSON (HTTP ${r.status}).`),{status:r.status,raw:text.slice(0,240)});}
    if(!r.ok||!j.ok)throw Object.assign(new Error(j.error||'Error de operación'),{response:j,status:r.status});
    return j;
  },
  money:n=>new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(Number(n||0)),
  date:d=>d?new Intl.DateTimeFormat('es-MX').format(new Date(d+'T12:00:00')):'',
  toast:function(msg,error=false){const t=document.getElementById('toast');if(!t)return;t.textContent=msg;t.classList.toggle('error',error);t.classList.remove('hidden');setTimeout(()=>t.classList.add('hidden'),3200);},
  isAdmin:function(){return (this.user?.budget_permissions||[]).some(p=>p.role==='ADMIN');},
  openModal:id=>document.getElementById(id)?.classList.remove('hidden'),
  closeModal:el=>el.closest('.modal')?.classList.add('hidden')
};

document.addEventListener('DOMContentLoaded',async()=>{
  try{const me=await Ixtla.api('db/web/auth/me.php');Ixtla.csrf=me.data.csrf;Ixtla.user=me.data.user;Ixtla.authMode=me.data.auth_mode;Ixtla.dataMode=me.data.data_mode;}
  catch{location.href='index.php';return;}
  document.querySelectorAll('.admin-only').forEach(el=>el.classList.toggle('hidden',!Ixtla.isAdmin()));
  document.querySelectorAll('.non-admin-only').forEach(el=>el.classList.toggle('hidden',Ixtla.isAdmin()));
  document.getElementById('logoutBtn')?.addEventListener('click',async()=>{try{await Ixtla.api('db/web/auth/logout.php',{method:'POST'});}finally{location.href='index.php';}});
  document.getElementById('menuBtn')?.addEventListener('click',()=>document.getElementById('sidebar')?.classList.toggle('open'));
  document.addEventListener('click',e=>{const close=e.target.closest('[data-close-modal]');if(close)Ixtla.closeModal(close);if(e.target.classList.contains('modal'))e.target.classList.add('hidden');});
  document.dispatchEvent(new CustomEvent('ixtla:ready'));
});

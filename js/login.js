const form=document.getElementById('loginForm');
const errorBox=document.getElementById('loginError');
const quick=document.getElementById('quickReviewBtn');

async function sendLogin(payload){
  errorBox?.classList.add('hidden');
  const btn=form?.querySelector('button[type=submit]');
  if(btn){btn.disabled=true;btn.textContent='Ingresando...';}
  try{
    const r=await fetch('db/web/auth/login.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const text=await r.text();
    let j;
    try{j=JSON.parse(text);}catch{throw new Error(`El servicio de login respondió con contenido no válido (HTTP ${r.status}).`);}
    if(!r.ok||!j.ok)throw new Error(j.error||'No se pudo iniciar sesión');
    location.href='index.php';
  }catch(err){if(errorBox){errorBox.textContent=err.message;errorBox.classList.remove('hidden');}}
  finally{if(btn){btn.disabled=false;btn.textContent='Entrar a Presupuesto';}}
}

form?.addEventListener('submit',e=>{e.preventDefault();sendLogin(Object.fromEntries(new FormData(form).entries()));});
quick?.addEventListener('click',()=>sendLogin({username:form?.elements.username?.value||'revision',password:''}));

<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';
require_method('POST');start_app_session();
$in=json_input();$username=trim((string)($in['username']??''));$password=(string)($in['password']??'');
if($username===''||$password==='')json_response(['ok'=>false,'error'=>'Captura usuario y contraseña'],400);
$db=conectar();if(!$db)json_response(['ok'=>false,'error'=>'No fue posible conectar a ixtla01_dep02 desde este App Service'],503);
$st=$db->prepare("SELECT usuario_id,username,password_hash,estatus,intentos_fallidos,bloqueado_hasta FROM usuario WHERE username=? OR email=? LIMIT 1");
$st->bind_param('ss',$username,$username);$st->execute();$acc=$st->get_result()->fetch_assoc();$st->close();
if(!$acc||$acc['estatus']!=='ACTIVO'){$db->close();json_response(['ok'=>false,'error'=>'Usuario o contraseña inválidos'],401);}
$uid=(int)$acc['usuario_id'];
if(!empty($acc['bloqueado_hasta'])&&strtotime($acc['bloqueado_hasta'])>time()){$db->close();json_response(['ok'=>false,'error'=>'Cuenta temporalmente bloqueada. Intenta más tarde.'],423);}
if(!password_verify($password,(string)$acc['password_hash'])){
  $fails=(int)$acc['intentos_fallidos']+1;
  if($fails>=5){$st=$db->prepare("UPDATE usuario SET intentos_fallidos=0,bloqueado_hasta=DATE_ADD(NOW(),INTERVAL 15 MINUTE) WHERE usuario_id=?");$st->bind_param('i',$uid);$st->execute();$st->close();audit_log($db,$uid,'LOGIN_BLOQUEADO','USUARIO',$uid,'Cuenta bloqueada por intentos fallidos');$db->close();json_response(['ok'=>false,'error'=>'Cuenta bloqueada durante 15 minutos por múltiples intentos fallidos'],423);}
  $st=$db->prepare("UPDATE usuario SET intentos_fallidos=? WHERE usuario_id=?");$st->bind_param('ii',$fails,$uid);$st->execute();$st->close();$db->close();json_response(['ok'=>false,'error'=>'Usuario o contraseña inválidos'],401);
}
$ip=client_ip();$st=$db->prepare("UPDATE usuario SET intentos_fallidos=0,bloqueado_hasta=NULL,ultimo_login_at=NOW(),ultimo_login_ip=? WHERE usuario_id=?");$st->bind_param('si',$ip,$uid);$st->execute();$st->close();
$user=load_user_context($db,$uid);if(!$user||!($user['assignments']??[])){$db->close();json_response(['ok'=>false,'error'=>'La cuenta no tiene un rol activo en Presupuesto'],403);}
audit_log($db,$uid,'LOGIN','SESION',$uid,'Inicio de sesión correcto');$db->close();
session_regenerate_id(true);$_SESSION['csrf']=bin2hex(random_bytes(32));$_SESSION['user']=$user;
json_response(['ok'=>true,'data'=>['user'=>$user,'csrf'=>$_SESSION['csrf']]]);

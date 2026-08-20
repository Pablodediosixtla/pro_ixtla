<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';require_method('POST');start_app_session();
$uid=(int)($_SESSION['user']['user_id']??0);$db=conectar();if($db){audit_log($db,$uid?:null,'LOGOUT','SESION',$uid?:null,'Cierre de sesión');$db->close();}
$_SESSION=[];if(ini_get('session.use_cookies')){$p=session_get_cookie_params();setcookie(session_name(),'',time()-42000,$p['path'],$p['domain']??'',(bool)$p['secure'],(bool)$p['httponly']);}session_destroy();json_response(['ok'=>true]);

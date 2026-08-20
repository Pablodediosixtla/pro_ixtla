<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');require_login();
$checkDb=(string)($_GET['check_db']??'0')==='1';
$data=[
 'application'=>'Presupuesto Ixtlahuacán',
 'php'=>PHP_VERSION,
 'auth_mode'=>app_auth_mode(),
 'data_mode'=>app_data_mode(),
 'database'=>[
   'configured'=>trim((string)env_value('DB_HOST',''))!=='' && trim((string)env_value('DB_USER',''))!=='' && trim((string)env_value('DB_NAME',''))!=='',
   'checked'=>false,
   'reachable'=>null,
 ]
];
if($checkDb){$data['database']['checked']=true;$con=conectar();$data['database']['reachable']=$con instanceof mysqli;if($con)$con->close();}
json_response(['ok'=>true,'data'=>$data]);

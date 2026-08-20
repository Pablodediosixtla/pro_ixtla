<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');require_login();
if(data_uses_demo()) json_response(['ok'=>true,'data'=>demo_employees(),'mode'=>'demo']);
$con=conectar();if(!$con)json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],503);
$rs=$con->query("SELECT id,nombre,apellidos,puesto FROM empleado WHERE status=1 ORDER BY nombre,apellidos");$data=[];if($rs)while($r=$rs->fetch_assoc()){$r['id']=(int)$r['id'];$data[]=$r;}$con->close();json_response(['ok'=>true,'data'=>$data,'mode'=>'db']);

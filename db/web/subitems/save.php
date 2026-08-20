<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('POST'); require_csrf();
$user=require_budget_role(['ADMIN']);
$in=json_input();$id=(int)($in['id']??0);$departmentId=isset($in['departamento_id'])&&$in['departamento_id']!==''&&$in['departamento_id']!==null?(int)$in['departamento_id']:null;
$codigo=strtoupper(trim((string)($in['codigo']??'')));$nombre=trim((string)($in['nombre']??''));$descripcion=trim((string)($in['descripcion']??''));$status=isset($in['status'])?(int)$in['status']:1;
if($codigo===''||$nombre==='')json_response(['ok'=>false,'error'=>'Código y nombre son obligatorios'],400);
$con=conectar();if(!$con)json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],500);$actor=(int)$user['account_id'];
if($id>0){
    $st=$con->prepare("UPDATE presupuesto_subitem SET departamento_id=?,codigo=?,nombre=?,descripcion=?,status=?,updated_by_cuenta_id=?,updated_at=CURRENT_TIMESTAMP WHERE id=?");
    $st->bind_param('isssiii',$departmentId,$codigo,$nombre,$descripcion,$status,$actor,$id);
}else{
    $st=$con->prepare("INSERT INTO presupuesto_subitem(departamento_id,codigo,nombre,descripcion,status,created_by_cuenta_id) VALUES(?,?,?,?,?,?)");
    $st->bind_param('isssii',$departmentId,$codigo,$nombre,$descripcion,$status,$actor);
}
if(!$st->execute()){ $err=$st->error;$st->close();$con->close();json_response(['ok'=>false,'error'=>'No se pudo guardar el sub-item','detail'=>$err],500); }
if($id<=0)$id=$st->insert_id;$st->close();$con->close();json_response(['ok'=>true,'data'=>['id'=>$id]]);

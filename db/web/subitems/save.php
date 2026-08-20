<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('POST');require_csrf();require_budget_role(['ADMIN']);$in=json_input();
if(data_uses_demo()){try{json_response(['ok'=>true,'data'=>demo_save_subitem($in),'mode'=>'demo']);}catch(Throwable $e){json_response(['ok'=>false,'error'=>$e->getMessage()],400);}}
$id=(int)($in['id']??0);$departmentId=($in['departamento_id']??'')!==''?(int)$in['departamento_id']:null;$code=strtoupper(trim((string)($in['codigo']??'')));$name=trim((string)($in['nombre']??''));$description=trim((string)($in['descripcion']??''));$status=(int)($in['status']??1)===1?1:0;if($code===''||$name==='')json_response(['ok'=>false,'error'=>'Código y nombre son obligatorios'],400);
$con=conectar();if(!$con)json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],503);
if($id>0){$st=$con->prepare("UPDATE presupuesto_subitem SET departamento_id=?,codigo=?,nombre=?,descripcion=?,status=?,updated_at=NOW() WHERE id=?");$st->bind_param('isssii',$departmentId,$code,$name,$description,$status,$id);$st->execute();$st->close();}
else{$st=$con->prepare("INSERT INTO presupuesto_subitem(departamento_id,codigo,nombre,descripcion,status) VALUES(?,?,?,?,?)");$st->bind_param('isssi',$departmentId,$code,$name,$description,$status);$st->execute();$id=$st->insert_id;$st->close();}$con->close();json_response(['ok'=>true,'data'=>['id'=>$id],'mode'=>'db']);

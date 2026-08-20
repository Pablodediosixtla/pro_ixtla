<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('POST');require_csrf();require_budget_role(['ADMIN']);$in=json_input();
if(data_uses_demo()) { try{json_response(['ok'=>true,'data'=>demo_save_department($in),'mode'=>'demo']);}catch(Throwable $e){json_response(['ok'=>false,'error'=>$e->getMessage()],400);} }
$id=(int)($in['id']??0);$nombre=trim((string)($in['nombre']??''));$descripcion=trim((string)($in['descripcion']??''));$director=($in['director']??'')!==''?(int)$in['director']:null;$line=($in['primera_linea']??'')!==''?(int)$in['primera_linea']:null;$status=(int)($in['status']??1)===1?1:0;if($nombre==='')json_response(['ok'=>false,'error'=>'El nombre es obligatorio'],400);
$con=conectar();if(!$con)json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],503);
if($id>0){$st=$con->prepare("UPDATE departamento SET nombre=?,descripcion=?,director=?,primera_linea=?,status=? WHERE id=?");$st->bind_param('ssiiii',$nombre,$descripcion,$director,$line,$status,$id);$st->execute();$st->close();}
else{$st=$con->prepare("INSERT INTO departamento(nombre,descripcion,director,primera_linea,status) VALUES(?,?,?,?,?)");$st->bind_param('ssiii',$nombre,$descripcion,$director,$line,$status);$st->execute();$id=$st->insert_id;$st->close();}
$con->close();json_response(['ok'=>true,'data'=>['id'=>$id],'mode'=>'db']);

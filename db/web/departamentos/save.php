<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('POST'); require_csrf();
$user=require_budget_role(['ADMIN']);
$in=json_input();
$id=(int)($in['id']??0); $nombre=trim((string)($in['nombre']??'')); $descripcion=trim((string)($in['descripcion']??''));
$director=(int)($in['director']??0); $primera=(int)($in['primera_linea']??0); $status=isset($in['status'])?(int)$in['status']:1;
if($nombre===''||$descripcion===''||$director<=0||$primera<=0) json_response(['ok'=>false,'error'=>'Nombre, descripción, director y primera línea son obligatorios'],400);
$con=conectar(); if(!$con) json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],500);
$actor=(int)$user['employee_id'];
if($id>0){
    $st=$con->prepare("UPDATE departamento SET nombre=?,descripcion=?,director=?,primera_linea=?,status=?,updated_by=?,updated_at=CURRENT_TIMESTAMP WHERE id=?");
    $st->bind_param('ssiiiii',$nombre,$descripcion,$director,$primera,$status,$actor,$id);
}else{
    $st=$con->prepare("INSERT INTO departamento(nombre,descripcion,director,primera_linea,status,created_by) VALUES(?,?,?,?,?,?)");
    $st->bind_param('ssiiii',$nombre,$descripcion,$director,$primera,$status,$actor);
}
if(!$st->execute()){ $err=$st->error; $st->close();$con->close(); json_response(['ok'=>false,'error'=>'No se pudo guardar el departamento','detail'=>$err],500); }
if($id<=0)$id=$st->insert_id; $st->close(); $con->close();
json_response(['ok'=>true,'data'=>['id'=>$id]]);

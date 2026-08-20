<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';require_method('POST');require_csrf();$user=require_permission('DEPARTAMENTOS_GESTIONAR');$in=json_input();
$id=(int)($in['departamento_id']??0);$code=strtoupper(trim((string)($in['codigo']??'')));$name=trim((string)($in['nombre']??''));$desc=trim((string)($in['descripcion']??''));$color=trim((string)($in['color_hex']??'#859F8E'));$icon=trim((string)($in['icono']??'building'));$treasury=(int)($in['es_tesoreria']??0)===1?1:0;$status=in_array(($in['estatus']??'ACTIVO'),['ACTIVO','INACTIVO'],true)?$in['estatus']:'ACTIVO';
if($code===''||$name==='')json_response(['ok'=>false,'error'=>'Código y nombre son obligatorios'],400);if(!preg_match('/^#[0-9A-Fa-f]{6}$/',$color))$color='#859F8E';
$db=conectar();if(!$db)json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);$before=null;
try{
 if($id>0){$st=$db->prepare("SELECT * FROM departamento WHERE departamento_id=?");$st->bind_param('i',$id);$st->execute();$before=$st->get_result()->fetch_assoc();$st->close();$st=$db->prepare("UPDATE departamento SET codigo=?,nombre=?,descripcion=?,color_hex=?,icono=?,es_tesoreria=?,estatus=? WHERE departamento_id=?");$st->bind_param('sssssisi',$code,$name,$desc,$color,$icon,$treasury,$status,$id);$st->execute();$st->close();}
 else{$st=$db->prepare("INSERT INTO departamento(codigo,nombre,descripcion,color_hex,icono,es_tesoreria,estatus) VALUES(?,?,?,?,?,?,?)");$st->bind_param('sssssis',$code,$name,$desc,$color,$icon,$treasury,$status);$st->execute();$id=(int)$st->insert_id;$st->close();}
 audit_log($db,(int)$user['user_id'],$before?'DEPARTAMENTO_EDITAR':'DEPARTAMENTO_CREAR','DEPARTAMENTO',$id,$name,$before,$in);$db->close();json_response(['ok'=>true,'data'=>['departamento_id'=>$id]]);
}catch(Throwable $e){$db->close();json_response(['ok'=>false,'error'=>'No se pudo guardar el departamento: '.$e->getMessage()],409);}

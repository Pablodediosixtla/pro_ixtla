<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';
require_method('POST');require_csrf();$user=require_permission('SUBITEMS_GESTIONAR');$in=json_input();
$id=(int)($in['subitem_id']??0);$dep=($in['departamento_id']??'')!==''?(int)$in['departamento_id']:null;
$code=strtoupper(trim((string)($in['codigo']??'')));$name=trim((string)($in['nombre']??''));$desc=trim((string)($in['descripcion']??''));
$type=strtoupper(trim((string)($in['tipo']??'SALIDA')));$status=in_array(($in['estatus']??'ACTIVO'),['ACTIVO','INACTIVO'],true)?$in['estatus']:'ACTIVO';
if($code===''||$name==='')json_response(['ok'=>false,'error'=>'Código y nombre son obligatorios'],400);
if(!in_array($type,['ENTRADA','SALIDA'],true))json_response(['ok'=>false,'error'=>'Selecciona si el sub-item es de entrada o salida'],400);
$db=conectar();if(!$db)json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);
if(!user_is_global($user)){if($dep===null||!in_array($dep,visible_department_ids($db,$user),true)){$db->close();json_response(['ok'=>false,'error'=>'No puedes administrar sub-items fuera de tu alcance'],403);}}
$uid=(int)$user['user_id'];$before=null;
try{
 if($id>0){
   $st=$db->prepare("SELECT * FROM presupuesto_subitem WHERE subitem_id=?");$st->bind_param('i',$id);$st->execute();$before=$st->get_result()->fetch_assoc();$st->close();
   if(!$before){$db->close();json_response(['ok'=>false,'error'=>'Sub-item no encontrado'],404);}
   if(($before['tipo']??$type)!==$type){
      $st=$db->prepare("SELECT COUNT(*) total FROM presupuesto_movimiento WHERE subitem_id=? UNION ALL SELECT COUNT(*) total FROM presupuesto_solicitud WHERE subitem_id=?");$st->bind_param('ii',$id,$id);$st->execute();$rs=$st->get_result();$used=0;while($r=$rs->fetch_assoc())$used+=(int)$r['total'];$st->close();
      if($used>0){$db->close();json_response(['ok'=>false,'error'=>'No puedes cambiar el tipo de un sub-item que ya tiene movimientos o solicitudes. Crea uno nuevo del tipo requerido.'],409);}
   }
   $st=$db->prepare("UPDATE presupuesto_subitem SET departamento_id=?,tipo=?,codigo=?,nombre=?,descripcion=?,estatus=? WHERE subitem_id=?");$st->bind_param('isssssi',$dep,$type,$code,$name,$desc,$status,$id);$st->execute();$st->close();
 }else{
   $st=$db->prepare("INSERT INTO presupuesto_subitem(departamento_id,tipo,codigo,nombre,descripcion,estatus,created_by_usuario_id) VALUES(?,?,?,?,?,?,?)");$st->bind_param('isssssi',$dep,$type,$code,$name,$desc,$status,$uid);$st->execute();$id=(int)$st->insert_id;$st->close();
 }
 audit_log($db,$uid,$before?'SUBITEM_EDITAR':'SUBITEM_CREAR','SUBITEM',$id,$name,$before,$in);$db->close();json_response(['ok'=>true,'data'=>['subitem_id'=>$id]]);
}catch(Throwable $e){$db->close();json_response(['ok'=>false,'error'=>'No se pudo guardar el sub-item: '.$e->getMessage()],409);}

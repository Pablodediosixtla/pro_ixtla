<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';
require_method('POST');
require_csrf();
$user=require_permission('ACLARACION_GESTIONAR');
$in=json_input();
$id=(int)($in['aclaracion_id']??0);
$status=strtoupper(trim((string)($in['estatus']??'')));
$assigned=($in['asignada_a_usuario_id']??'')!==''?(int)$in['asignada_a_usuario_id']:null;

if($id<=0||!in_array($status,['ABIERTA','EN_REVISION','RESUELTA','CERRADA'],true)){
    json_response(['ok'=>false,'error'=>'Datos inválidos'],400);
}

$db=conectar();
if(!$db)json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);

$st=$db->prepare("SELECT a.*,pm.departamento_id,pm.solicitado_por_usuario_id,pm.otorgado_a_usuario_id,pm.registrado_por_usuario_id
                  FROM movimiento_aclaracion a
                  JOIN presupuesto_movimiento pm ON pm.movimiento_id=a.movimiento_id
                  WHERE a.aclaracion_id=? LIMIT 1");
$st->bind_param('i',$id);
$st->execute();
$row=$st->get_result()->fetch_assoc();
$st->close();

if(!$row){
    $db->close();
    json_response(['ok'=>false,'error'=>'Aclaración no encontrada'],404);
}
if(!movement_is_visible($db,$user,$row)){
    $db->close();
    json_response(['ok'=>false,'error'=>'No tienes acceso a esta aclaración'],403);
}

if($assigned!==null){
    $st=$db->prepare("SELECT 1
                      FROM usuario u
                      LEFT JOIN usuario_departamento ud ON ud.usuario_id=u.usuario_id AND ud.estatus='ACTIVO'
                      LEFT JOIN rol r ON r.rol_id=ud.rol_id
                      WHERE u.usuario_id=? AND u.estatus='ACTIVO'
                        AND (ud.departamento_id=? OR r.alcance='GLOBAL')
                      LIMIT 1");
    $departmentId=(int)$row['departamento_id'];
    $st->bind_param('ii',$assigned,$departmentId);
    $st->execute();
    $validAssigned=(bool)$st->get_result()->fetch_row();
    $st->close();
    if(!$validAssigned){
        $db->close();
        json_response(['ok'=>false,'error'=>'El responsable seleccionado no pertenece al alcance del movimiento'],400);
    }
}

$uid=(int)$user['user_id'];
if($status==='CERRADA'){
    $st=$db->prepare("UPDATE movimiento_aclaracion
                      SET estatus=?,asignada_a_usuario_id=?,cerrada_por_usuario_id=?,cerrada_at=NOW()
                      WHERE aclaracion_id=?");
    $st->bind_param('siii',$status,$assigned,$uid,$id);
}else{
    $st=$db->prepare("UPDATE movimiento_aclaracion
                      SET estatus=?,asignada_a_usuario_id=?,cerrada_por_usuario_id=NULL,cerrada_at=NULL
                      WHERE aclaracion_id=?");
    $st->bind_param('sii',$status,$assigned,$id);
}
$st->execute();
$st->close();

audit_log($db,$uid,'ACLARACION_ESTADO','ACLARACION',$id,$status,$row,[
    'estatus'=>$status,
    'asignada_a'=>$assigned
]);
$db->close();
json_response(['ok'=>true]);

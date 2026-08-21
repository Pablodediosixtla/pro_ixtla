<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';
require_method('GET');
$user=require_login();
$id=(int)($_GET['id']??0);
if($id<=0)json_response(['ok'=>false,'error'=>'Aclaración inválida'],400);

$db=conectar();
if(!$db)json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);

$st=$db->prepare("SELECT a.*,pm.folio movimiento_folio,pm.tipo,pm.departamento_id,
                         pm.solicitado_por_usuario_id,pm.otorgado_a_usuario_id,pm.registrado_por_usuario_id,
                         d.nombre departamento
                  FROM movimiento_aclaracion a
                  JOIN presupuesto_movimiento pm ON pm.movimiento_id=a.movimiento_id
                  JOIN departamento d ON d.departamento_id=pm.departamento_id
                  WHERE a.aclaracion_id=?");
$st->bind_param('i',$id);
$st->execute();
$row=$st->get_result()->fetch_assoc();
$st->close();

if(!$row||!movement_is_visible($db,$user,$row)){
    $db->close();
    json_response(['ok'=>false,'error'=>'Aclaración no encontrada'],404);
}

$canSeeInternal=user_has_permission($user,'ACLARACION_GESTIONAR');
$sql="SELECT m.*,CONCAT_WS(' ',u.nombre,u.apellido_paterno,u.apellido_materno) usuario
      FROM movimiento_aclaracion_mensaje m
      JOIN usuario u ON u.usuario_id=m.usuario_id
      WHERE m.aclaracion_id=?";
if(!$canSeeInternal){
    $sql.=" AND m.es_interno=0";
}
$sql.=" ORDER BY m.created_at,m.mensaje_id";

$messages=[];
$st=$db->prepare($sql);
$st->bind_param('i',$id);
$st->execute();
$rs=$st->get_result();
while($m=$rs->fetch_assoc()){
    $m['mensaje_id']=(int)$m['mensaje_id'];
    $m['usuario_id']=(int)$m['usuario_id'];
    $m['es_interno']=(int)$m['es_interno'];
    $messages[]=$m;
}
$st->close();

$row['aclaracion_id']=(int)$row['aclaracion_id'];
$row['movimiento_id']=(int)$row['movimiento_id'];
$row['messages']=$messages;
$db->close();
json_response(['ok'=>true,'data'=>$row]);

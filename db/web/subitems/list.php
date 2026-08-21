<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';
require_method('GET');
$user=require_login();
$dep=(int)($_GET['departamento_id']??0);
$all=(string)($_GET['all']??'0')==='1';
$type=strtoupper(trim((string)($_GET['tipo']??'')));
if($type!==''&&!in_array($type,['ENTRADA','SALIDA'],true))json_response(['ok'=>false,'error'=>'Tipo de sub-item inválido'],400);
$db=conectar();if(!$db)json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);
$where=[];
if(!$all)$where[]="s.estatus='ACTIVO'";
if($type!=='')$where[]="s.tipo='".$db->real_escape_string($type)."'";
$ids=visible_department_ids($db,$user);
if($dep>0){
    if(!user_is_global($user)&&!in_array($dep,$ids,true)){$db->close();json_response(['ok'=>true,'data'=>[]]);}
    $where[]='(s.departamento_id IS NULL OR s.departamento_id='.(int)$dep.')';
}elseif(!user_is_global($user)){
    $where[]='(s.departamento_id IS NULL OR s.departamento_id IN ('.($ids?implode(',',array_map('intval',$ids)):'0').'))';
}
$sql="SELECT s.*,d.nombre departamento FROM presupuesto_subitem s LEFT JOIN departamento d ON d.departamento_id=s.departamento_id".($where?' WHERE '.implode(' AND ',$where):'')." ORDER BY s.tipo,s.nombre";
$rs=$db->query($sql);$data=[];
while($r=$rs->fetch_assoc()){$r['subitem_id']=(int)$r['subitem_id'];$r['departamento_id']=$r['departamento_id']!==null?(int)$r['departamento_id']:null;$data[]=$r;}
$db->close();json_response(['ok'=>true,'data'=>$data]);

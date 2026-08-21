<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';
require_method('GET');
$user=require_permission('PRESUPUESTO_VER');
if(!user_can_view_department_financials($user))json_response(['ok'=>false,'error'=>'Tu perfil no tiene acceso a información presupuestal agregada'],403);

$year=max(2020,min(2100,(int)($_GET['year']??date('Y'))));
$db=conectar();
if(!$db)json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);

$ids=visible_department_ids($db,$user);
if(!$ids){
    $db->close();
    json_response(['ok'=>true,'data'=>[]]);
}

$list=implode(',',array_map('intval',$ids));
$sql="SELECT d.departamento_id,d.codigo,d.nombre,d.color_hex,
             COALESCE((
                 SELECT pd.observaciones
                 FROM presupuesto_departamento pd
                 WHERE pd.departamento_id=d.departamento_id
                   AND pd.ejercicio=$year
                   AND pd.estatus='ACTIVO'
                 ORDER BY pd.presupuesto_departamento_id DESC
                 LIMIT 1
             ),'') observaciones
      FROM departamento d
      WHERE d.departamento_id IN ($list) AND d.estatus='ACTIVO'
      ORDER BY d.nombre";
$rs=$db->query($sql);
$data=[];
if($rs) while($r=$rs->fetch_assoc()){
    $departmentId=(int)$r['departamento_id'];
    $financial=department_financial_summary($db,$departmentId,$year);
    $data[]=[
        'departamento_id'=>$departmentId,
        'codigo'=>$r['codigo'],
        'nombre'=>$r['nombre'],
        'color_hex'=>$r['color_hex'],
        'presupuesto_asignado'=>$financial['asignado'],
        'observaciones'=>$r['observaciones'],
        'entradas'=>$financial['entradas'],
        'salidas'=>$financial['salidas'],
        'disponible'=>$financial['disponible'],
        'ejercido_pct'=>$financial['ejercido_pct'],
    ];
}
$db->close();
json_response(['ok'=>true,'data'=>$data]);

<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');
$user=require_login();
$q=trim((string)($_GET['q']??''));
$year=max(2020,min(2100,(int)($_GET['year']??date('Y'))));
if(data_uses_demo()) json_response(['ok'=>true,'data'=>demo_departments($q,$year),'mode'=>'demo']);
$con=conectar();if(!$con)json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],503);
$sql="SELECT d.id,d.nombre,d.descripcion,d.director,d.primera_linea,d.status,
CONCAT(COALESCE(ed.nombre,''),' ',COALESCE(ed.apellidos,'')) director_nombre,
CONCAT(COALESCE(ep.nombre,''),' ',COALESCE(ep.apellidos,'')) primera_linea_nombre
FROM departamento d LEFT JOIN empleado ed ON ed.id=d.director LEFT JOIN empleado ep ON ep.id=d.primera_linea WHERE (?='' OR d.nombre LIKE CONCAT('%',?,'%') OR d.descripcion LIKE CONCAT('%',?,'%')) ORDER BY d.nombre";
$st=$con->prepare($sql);$st->bind_param('sss',$q,$q,$q);$st->execute();$rs=$st->get_result();$data=[];
while($r=$rs->fetch_assoc()){$id=(int)$r['id'];if(!has_budget_role($user,['ADMIN'])&&!has_budget_role($user,['CAPTURISTA','CONSULTA'],$id))continue;$r['id']=$id;$r['director']=$r['director']!==null?(int)$r['director']:null;$r['primera_linea']=$r['primera_linea']!==null?(int)$r['primera_linea']:null;$r['status']=(int)$r['status'];$r['balance']=department_balance($con,$id,$year);$base=max(.01,$r['balance']['asignado']+$r['balance']['entradas']);$r['balance']['ejercido_pct']=round(($r['balance']['salidas']/$base)*100,1);$data[]=$r;}
$st->close();$con->close();json_response(['ok'=>true,'data'=>$data,'mode'=>'db']);

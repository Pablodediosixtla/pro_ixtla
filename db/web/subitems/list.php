<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET'); require_login();
$departmentId=isset($_GET['departamento_id'])&&$_GET['departamento_id']!==''?(int)$_GET['departamento_id']:null;
$all=($_GET['all']??'0')==='1';
$con=conectar();if(!$con)json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],500);
if($departmentId){
    $sql="SELECT s.*,d.nombre departamento_nombre FROM presupuesto_subitem s LEFT JOIN departamento d ON d.id=s.departamento_id WHERE (s.departamento_id IS NULL OR s.departamento_id=?)" . ($all?'':' AND s.status=1') . " ORDER BY s.status DESC,s.nombre";
    $st=$con->prepare($sql);$st->bind_param('i',$departmentId);
}else{
    $sql="SELECT s.*,d.nombre departamento_nombre FROM presupuesto_subitem s LEFT JOIN departamento d ON d.id=s.departamento_id WHERE 1=1" . ($all?'':' AND s.status=1') . " ORDER BY s.status DESC,s.nombre";
    $st=$con->prepare($sql);
}
$st->execute();$rs=$st->get_result();$data=[];while($r=$rs->fetch_assoc()){ $r['id']=(int)$r['id'];$r['departamento_id']=$r['departamento_id']!==null?(int)$r['departamento_id']:null;$r['status']=(int)$r['status'];$data[]=$r; }
$st->close();$con->close();json_response(['ok'=>true,'data'=>$data]);

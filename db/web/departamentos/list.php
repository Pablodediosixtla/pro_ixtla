<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');
$user = require_login();
$year = max(2020,min(2100,(int)($_GET['year'] ?? current_year())));
$q = trim((string)($_GET['q'] ?? ''));

$con=conectar(); if(!$con) json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],500);
$sql="SELECT d.id,d.nombre,d.descripcion,d.director,d.primera_linea,d.status,d.created_at,d.updated_at,
            CONCAT(COALESCE(e1.nombre,''),' ',COALESCE(e1.apellidos,'')) director_nombre,
            CONCAT(COALESCE(e2.nombre,''),' ',COALESCE(e2.apellidos,'')) primera_linea_nombre,
            COALESCE(pd.presupuesto_asignado,0) presupuesto_asignado
     FROM departamento d
     LEFT JOIN empleado e1 ON e1.id=d.director
     LEFT JOIN empleado e2 ON e2.id=d.primera_linea
     LEFT JOIN presupuesto_departamento pd ON pd.departamento_id=d.id AND pd.ejercicio=? AND pd.status=1
     WHERE (?='' OR d.nombre LIKE CONCAT('%',?,'%') OR d.descripcion LIKE CONCAT('%',?,'%'))
     ORDER BY d.status DESC,d.nombre";
$st=$con->prepare($sql); $st->bind_param('isss',$year,$q,$q,$q); $st->execute(); $rs=$st->get_result();
$data=[];
while($r=$rs->fetch_assoc()){
    $id=(int)$r['id'];
    if(!has_budget_role($user,['ADMIN']) && !has_budget_role($user,['CAPTURISTA','CONSULTA'],$id)) continue;
    $balance=department_balance($con,$id,$year);
    $r['id']=$id; $r['status']=(int)$r['status']; $r['presupuesto_asignado']=(float)$r['presupuesto_asignado'];
    $r['balance']=$balance; $data[]=$r;
}
$st->close(); $con->close();
json_response(['ok'=>true,'data'=>$data]);

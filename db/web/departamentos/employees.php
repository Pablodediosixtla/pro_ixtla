<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET'); require_budget_role(['ADMIN']);
$con=conectar(); if(!$con) json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],500);
$q=trim((string)($_GET['q']??''));
$st=$con->prepare("SELECT id,nombre,apellidos,puesto,departamento_id FROM empleado WHERE status=1 AND (?='' OR nombre LIKE CONCAT('%',?,'%') OR apellidos LIKE CONCAT('%',?,'%')) ORDER BY nombre,apellidos LIMIT 250");
$st->bind_param('sss',$q,$q,$q); $st->execute();$rs=$st->get_result();$data=[];
while($r=$rs->fetch_assoc()){ $r['id']=(int)$r['id']; $data[]=$r; }
$st->close();$con->close(); json_response(['ok'=>true,'data'=>$data]);

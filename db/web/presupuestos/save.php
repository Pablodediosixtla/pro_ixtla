<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('POST'); require_csrf();
$user=require_budget_role(['ADMIN']);
$in=json_input(); $departmentId=(int)($in['departamento_id']??0); $year=(int)($in['ejercicio']??current_year()); $amount=money_value($in['presupuesto_asignado']??0);
if($departmentId<=0||$year<2020||$year>2100||$amount<0) json_response(['ok'=>false,'error'=>'Datos de presupuesto inválidos'],400);
$con=conectar();if(!$con)json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],500);
$account=(int)$user['account_id'];
$sql="INSERT INTO presupuesto_departamento(departamento_id,ejercicio,presupuesto_asignado,status,created_by_cuenta_id,updated_by_cuenta_id)
      VALUES(?,?,?,1,?,?)
      ON DUPLICATE KEY UPDATE presupuesto_asignado=VALUES(presupuesto_asignado),status=1,updated_by_cuenta_id=VALUES(updated_by_cuenta_id),updated_at=CURRENT_TIMESTAMP";
$st=$con->prepare($sql);$st->bind_param('iidii',$departmentId,$year,$amount,$account,$account);
if(!$st->execute()){ $err=$st->error;$st->close();$con->close();json_response(['ok'=>false,'error'=>'No se pudo guardar el presupuesto','detail'=>$err],500); }
$st->close();$balance=department_balance($con,$departmentId,$year);$con->close();
json_response(['ok'=>true,'data'=>$balance]);

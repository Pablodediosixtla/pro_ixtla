<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('POST'); require_csrf();
$user=require_login();
$departmentId=(int)($_POST['departamento_id']??0);$type=strtoupper(trim((string)($_POST['tipo']??'')));$date=trim((string)($_POST['fecha']??''));$amount=money_value($_POST['monto']??0);$concept=trim((string)($_POST['concepto']??''));
$subitemId=isset($_POST['subitem_id'])&&$_POST['subitem_id']!==''?(int)$_POST['subitem_id']:null;$recipient=trim((string)($_POST['entregado_a']??''));$area=trim((string)($_POST['area_solicitante']??''));$method=strtoupper(trim((string)($_POST['metodo_pago']??'')));$reference=trim((string)($_POST['referencia']??''));
if($departmentId<=0||!in_array($type,['ENTRADA','SALIDA'],true)||$date===''||$amount<=0||$concept==='')json_response(['ok'=>false,'error'=>'Completa departamento, tipo, fecha, monto y concepto'],400);
if($type==='SALIDA'&&$subitemId===null)json_response(['ok'=>false,'error'=>'La salida requiere un sub-item'],400);
if($type==='SALIDA'&&!in_array($method,['EFECTIVO','TRANSFERENCIA','CHEQUE','TARJETA','OTRO'],true))json_response(['ok'=>false,'error'=>'Método de pago inválido'],400);
if(!has_budget_role($user,['ADMIN','CAPTURISTA'],$departmentId))json_response(['ok'=>false,'error'=>'No tienes permiso para registrar movimientos en este departamento'],403);
$year=(int)substr($date,0,4);if($year<2020||$year>2100)json_response(['ok'=>false,'error'=>'Fecha inválida'],400);
$con=conectar();if(!$con)json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],500);
$balance=department_balance($con,$departmentId,$year);if($type==='SALIDA'&&$amount>$balance['disponible']){$con->close();json_response(['ok'=>false,'error'=>'La salida excede el presupuesto disponible','data'=>['disponible'=>$balance['disponible']]],409);}
$con->begin_transaction();
try{
    $folio=next_budget_folio($con,$year);$account=(int)$user['account_id'];$methodDb=$type==='SALIDA'?$method:null;
    $st=$con->prepare("INSERT INTO presupuesto_movimiento(folio,ejercicio,departamento_id,subitem_id,tipo,fecha,monto,concepto,entregado_a,area_solicitante,metodo_pago,referencia,status,creado_por_cuenta_id) VALUES(?,?,?,?,?,?,?,?,?,?,?,?, 'REGISTRADO',?)");
    $st->bind_param('siiissdsssssi',$folio,$year,$departmentId,$subitemId,$type,$date,$amount,$concept,$recipient,$area,$methodDb,$reference,$account);
    if(!$st->execute())throw new RuntimeException('No se pudo guardar el movimiento: '.$st->error);$movementId=$st->insert_id;$st->close();
    $savedFile=null;if(isset($_FILES['evidencia'])){$savedFile=save_evidence_file($_FILES['evidencia'],$folio);if($savedFile){$st=$con->prepare("INSERT INTO presupuesto_movimiento_archivo(movimiento_id,nombre_original,nombre_guardado,ruta_relativa,mime_type,size_bytes,created_by_cuenta_id) VALUES(?,?,?,?,?,?,?)");$st->bind_param('issssii',$movementId,$savedFile['nombre_original'],$savedFile['nombre_guardado'],$savedFile['ruta_relativa'],$savedFile['mime_type'],$savedFile['size_bytes'],$account);if(!$st->execute())throw new RuntimeException('No se pudo registrar la evidencia: '.$st->error);$st->close();}}
    $con->commit();$newBalance=department_balance($con,$departmentId,$year);$con->close();json_response(['ok'=>true,'data'=>['id'=>$movementId,'folio'=>$folio,'balance'=>$newBalance]]);
}catch(Throwable $e){$con->rollback();$con->close();json_response(['ok'=>false,'error'=>$e->getMessage()],500);}

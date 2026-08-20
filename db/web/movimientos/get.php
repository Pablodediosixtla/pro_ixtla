<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');$user=require_login();$id=(int)($_GET['id']??0);if($id<=0)json_response(['ok'=>false,'error'=>'ID inválido'],400);
$con=conectar();if(!$con)json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],500);
$st=$con->prepare("SELECT pm.*,d.nombre departamento_nombre,s.nombre subitem_nombre,s.codigo subitem_codigo,CONCAT(COALESCE(e.nombre,''),' ',COALESCE(e.apellidos,'')) usuario_nombre FROM presupuesto_movimiento pm JOIN departamento d ON d.id=pm.departamento_id LEFT JOIN presupuesto_subitem s ON s.id=pm.subitem_id LEFT JOIN empleado_cuenta c ON c.id=pm.creado_por_cuenta_id LEFT JOIN empleado e ON e.id=c.empleado_id WHERE pm.id=? LIMIT 1");
$st->bind_param('i',$id);$st->execute();$row=$st->get_result()->fetch_assoc();$st->close();if(!$row){$con->close();json_response(['ok'=>false,'error'=>'Movimiento no encontrado'],404);} $did=(int)$row['departamento_id'];
if(!has_budget_role($user,['ADMIN'])&&!has_budget_role($user,['CAPTURISTA','CONSULTA'],$did)){ $con->close();json_response(['ok'=>false,'error'=>'Sin permiso'],403); }
$files=[];$st=$con->prepare("SELECT id,nombre_original,ruta_relativa,mime_type,size_bytes,created_at FROM presupuesto_movimiento_archivo WHERE movimiento_id=? ORDER BY id");$st->bind_param('i',$id);$st->execute();$rs=$st->get_result();while($f=$rs->fetch_assoc()){$f['id']=(int)$f['id'];$f['size_bytes']=(int)$f['size_bytes'];$f['download_url']='db/web/movimientos/file.php?id='.$f['id'];unset($f['ruta_relativa']);$files[]=$f;}$st->close();$con->close();
$row['id']=(int)$row['id'];$row['departamento_id']=$did;$row['monto']=(float)$row['monto'];$row['files']=$files;json_response(['ok'=>true,'data'=>$row]);

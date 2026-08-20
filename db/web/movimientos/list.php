<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');$user=require_login();
$filters=[
 'year'=>(int)($_GET['year']??date('Y')),
 'departamento_id'=>(int)($_GET['departamento_id']??0),
 'tipo'=>(string)($_GET['tipo']??''),
 'status'=>(string)($_GET['status']??''),
 'q'=>(string)($_GET['q']??''),
 'fecha_desde'=>(string)($_GET['fecha_desde']??''),
 'fecha_hasta'=>(string)($_GET['fecha_hasta']??''),
 'limit'=>(int)($_GET['limit']??100),
];
if(data_uses_demo()) json_response(['ok'=>true,'data'=>demo_movements($filters),'mode'=>'demo']);
$year=max(2020,min(2100,(int)$filters['year']));$departmentId=(int)$filters['departamento_id'];$type=strtoupper(trim((string)$filters['tipo']));$status=strtoupper(trim((string)$filters['status']));$q=trim((string)$filters['q']);$dateFrom=trim((string)$filters['fecha_desde']);$dateTo=trim((string)$filters['fecha_hasta']);$limit=max(1,min(500,(int)$filters['limit']));
$con=conectar();if(!$con)json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],503);
$where=['pm.ejercicio=?'];$types='i';$params=[$year];
if($departmentId>0){$where[]='pm.departamento_id=?';$types.='i';$params[]=$departmentId;}
if(in_array($type,['ENTRADA','SALIDA'],true)){$where[]='pm.tipo=?';$types.='s';$params[]=$type;}
if(in_array($status,['REGISTRADO','CANCELADO'],true)){$where[]='pm.status=?';$types.='s';$params[]=$status;}
if($q!==''){$where[]="(pm.folio LIKE CONCAT('%',?,'%') OR pm.concepto LIKE CONCAT('%',?,'%') OR pm.entregado_a LIKE CONCAT('%',?,'%'))";$types.='sss';$params[]=$q;$params[]=$q;$params[]=$q;}
if($dateFrom!==''){$where[]='pm.fecha>=?';$types.='s';$params[]=$dateFrom;}
if($dateTo!==''){$where[]='pm.fecha<=?';$types.='s';$params[]=$dateTo;}
$sql="SELECT pm.*,d.nombre departamento_nombre,s.nombre subitem_nombre,s.codigo subitem_codigo,
            CONCAT(COALESCE(e.nombre,''),' ',COALESCE(e.apellidos,'')) usuario_nombre,
            (SELECT COUNT(*) FROM presupuesto_movimiento_archivo a WHERE a.movimiento_id=pm.id) archivos_count
     FROM presupuesto_movimiento pm
     JOIN departamento d ON d.id=pm.departamento_id
     LEFT JOIN presupuesto_subitem s ON s.id=pm.subitem_id
     LEFT JOIN empleado_cuenta c ON c.id=pm.creado_por_cuenta_id
     LEFT JOIN empleado e ON e.id=c.empleado_id
     WHERE ".implode(' AND ',$where)." ORDER BY pm.fecha DESC,pm.id DESC LIMIT ?";
$types.='i';$params[]=$limit;$st=$con->prepare($sql);$st->bind_param($types,...$params);$st->execute();$rs=$st->get_result();$data=[];
while($r=$rs->fetch_assoc()){$did=(int)$r['departamento_id'];if(!has_budget_role($user,['ADMIN'])&&!has_budget_role($user,['CAPTURISTA','CONSULTA'],$did))continue;$r['id']=(int)$r['id'];$r['departamento_id']=$did;$r['subitem_id']=$r['subitem_id']!==null?(int)$r['subitem_id']:null;$r['monto']=(float)$r['monto'];$r['archivos_count']=(int)$r['archivos_count'];$data[]=$r;}
$st->close();$con->close();json_response(['ok'=>true,'data'=>$data,'mode'=>'db']);

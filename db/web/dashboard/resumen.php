<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');
$user = require_login();
$year = max(2020, min(2100, (int)($_GET['year'] ?? date('Y'))));

if (data_uses_demo()) {
    json_response(['ok'=>true,'data'=>demo_dashboard($year),'mode'=>'demo']);
}

$con = conectar();
if (!$con) json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],503);

$whereDepartment = '';
$params = [$year,$year];
$types = 'ii';
if (!has_budget_role($user,['ADMIN'])) {
    $allowed=[];
    foreach ($user['budget_permissions'] ?? [] as $p) if ($p['department_id'] !== null) $allowed[]=(int)$p['department_id'];
    $allowed=array_values(array_unique($allowed));
    if (!$allowed) { $con->close(); json_response(['ok'=>true,'data'=>['totals'=>['asignado'=>0,'entradas'=>0,'salidas'=>0,'disponible'=>0],'monthly'=>array_fill(1,12,['entrada'=>0,'salida'=>0]),'departments'=>[]]]); }
    $whereDepartment = ' AND d.id IN ('.implode(',',array_map('intval',$allowed)).')';
}

$sql="SELECT d.id,d.nombre,
  COALESCE(pd.presupuesto_asignado,0) asignado,
  COALESCE(SUM(CASE WHEN pm.tipo='ENTRADA' AND pm.status='REGISTRADO' THEN pm.monto ELSE 0 END),0) entradas,
  COALESCE(SUM(CASE WHEN pm.tipo='SALIDA' AND pm.status='REGISTRADO' THEN pm.monto ELSE 0 END),0) salidas
FROM departamento d
LEFT JOIN presupuesto_departamento pd ON pd.departamento_id=d.id AND pd.ejercicio=? AND pd.status=1
LEFT JOIN presupuesto_movimiento pm ON pm.departamento_id=d.id AND pm.ejercicio=?
WHERE d.status=1 $whereDepartment
GROUP BY d.id,d.nombre,pd.presupuesto_asignado ORDER BY d.nombre";
$st=$con->prepare($sql);$st->bind_param($types,...$params);$st->execute();$rs=$st->get_result();
$departments=[];$totals=['asignado'=>0.0,'entradas'=>0.0,'salidas'=>0.0,'disponible'=>0.0];
while($r=$rs->fetch_assoc()){
    $a=(float)$r['asignado'];$e=(float)$r['entradas'];$s=(float)$r['salidas'];$dis=$a+$e-$s;$base=max(.01,$a+$e);
    $departments[]=['id'=>(int)$r['id'],'nombre'=>$r['nombre'],'asignado'=>$a,'entradas'=>$e,'salidas'=>$s,'disponible'=>$dis,'ejercido_pct'=>round(($s/$base)*100,1)];
    $totals['asignado']+=$a;$totals['entradas']+=$e;$totals['salidas']+=$s;$totals['disponible']+=$dis;
}
$st->close();
$monthly=[];for($i=1;$i<=12;$i++)$monthly[$i]=['entrada'=>0.0,'salida'=>0.0];
$sql="SELECT MONTH(fecha) mes,tipo,SUM(monto) total FROM presupuesto_movimiento WHERE ejercicio=? AND status='REGISTRADO' GROUP BY MONTH(fecha),tipo";
$st=$con->prepare($sql);$st->bind_param('i',$year);$st->execute();$rs=$st->get_result();while($r=$rs->fetch_assoc()){$m=(int)$r['mes'];if($m<1||$m>12)continue;$monthly[$m][$r['tipo']==='ENTRADA'?'entrada':'salida']=(float)$r['total'];}$st->close();$con->close();
json_response(['ok'=>true,'data'=>['totals'=>$totals,'monthly'=>$monthly,'departments'=>$departments],'mode'=>'db']);

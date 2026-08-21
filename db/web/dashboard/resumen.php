<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';
require_method('GET');
$user=require_login();
$year=max(2020,min(2100,(int)($_GET['year']??date('Y'))));
$db=conectar();
if(!$db) json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);

$ids=visible_department_ids($db,$user);
$ownOnly=user_is_own_scope_only($user);
$tot=['asignado'=>0.0,'entradas'=>0.0,'salidas'=>0.0,'disponible'=>0.0,'ejercido_pct'=>0.0];
$deps=[];
$monthly=[];
for($m=1;$m<=12;$m++) $monthly[$m]=['entrada'=>0.0,'salida'=>0.0];

if(!$ids){
    $db->close();
    json_response(['ok'=>true,'data'=>[
        'totals'=>$tot,
        'show_totals'=>!$ownOnly,
        'show_department_financials'=>!$ownOnly,
        'departments'=>[],
        'monthly'=>$monthly,
        'pending_requests'=>0,
        'open_clarifications'=>0,
        'own_scope_only'=>$ownOnly,
        'scope_department_ids'=>$ids,
    ]]);
}

$idList=implode(',',array_map('intval',$ids));

// Los totalizadores y la disponibilidad por departamento no se calculan ni se
// exponen para alcance PROPIO. Esto evita filtrar información agregada del área.
if(!$ownOnly){
    // Los KPIs se calculan exclusivamente con los departamentos devueltos por
    // visible_department_ids(). Para Director/Supervisor esto equivale al
    // departamento principal de su sesión; nunca al municipio completo.
    $sql="SELECT d.departamento_id,d.codigo,d.nombre,d.color_hex
          FROM departamento d
          WHERE d.departamento_id IN ($idList) AND d.estatus='ACTIVO'
          ORDER BY d.nombre";
    $rs=$db->query($sql);
    if($rs) while($r=$rs->fetch_assoc()){
        $departmentId=(int)$r['departamento_id'];
        $financial=department_financial_summary($db,$departmentId,$year);

        $deps[]=[
            'departamento_id'=>$departmentId,
            'codigo'=>$r['codigo'],
            'nombre'=>$r['nombre'],
            'color_hex'=>$r['color_hex'],
            'asignado'=>$financial['asignado'],
            'entradas'=>$financial['entradas'],
            'salidas'=>$financial['salidas'],
            'disponible'=>$financial['disponible'],
            'ejercido_pct'=>$financial['ejercido_pct'],
        ];

        $tot['asignado'] += $financial['asignado'];
        $tot['entradas'] += $financial['entradas'];
        $tot['salidas'] += $financial['salidas'];
        $tot['disponible'] += $financial['disponible'];
    }
    $tot['ejercido_pct']=$tot['asignado']>0?round(($tot['salidas']/$tot['asignado'])*100,1):0.0;
}

// La gráfica sí se conserva para subordinados, pero movement_is_visible limita
// el contenido a las salidas solicitadas por el propio usuario. Las entradas no
// son visibles para alcance PROPIO.
$rs=$db->query("SELECT pm.*,MONTH(pm.fecha) mes FROM presupuesto_movimiento pm WHERE pm.ejercicio=$year AND pm.estatus='REGISTRADO' AND pm.departamento_id IN ($idList) ORDER BY pm.fecha");
if($rs) while($r=$rs->fetch_assoc()){
    if(!movement_is_visible($db,$user,$r)) continue;
    $m=(int)$r['mes'];
    $k=$r['tipo']==='ENTRADA'?'entrada':'salida';
    $monthly[$m][$k]+=(float)$r['monto'];
}

$pending=0;
$rs=$db->query("SELECT * FROM presupuesto_solicitud WHERE ejercicio=$year AND estatus IN ('PENDIENTE','AUTORIZADA') AND departamento_id IN ($idList)");
if($rs) while($r=$rs->fetch_assoc()){
    if(request_is_visible($db,$user,$r)||user_has_permission($user,'SOLICITUD_APROBAR')) $pending++;
}

$open=0;
$sql="SELECT a.aclaracion_id,pm.* FROM movimiento_aclaracion a JOIN presupuesto_movimiento pm ON pm.movimiento_id=a.movimiento_id WHERE a.estatus IN ('ABIERTA','EN_REVISION') AND pm.departamento_id IN ($idList)";
$rs=$db->query($sql);
if($rs) while($r=$rs->fetch_assoc()) if(movement_is_visible($db,$user,$r)) $open++;

$db->close();
json_response(['ok'=>true,'data'=>[
    'totals'=>$tot,
    'show_totals'=>!$ownOnly,
    'show_department_financials'=>!$ownOnly,
    'departments'=>$deps,
    'monthly'=>$monthly,
    'pending_requests'=>$pending,
    'open_clarifications'=>$open,
    'own_scope_only'=>$ownOnly,
    'scope_department_ids'=>$ids,
]]);

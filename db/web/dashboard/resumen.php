<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');
$user = require_login();
$year = max(2020, min(2100, (int)($_GET['year'] ?? current_year())));

$con = conectar();
if (!$con) json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],500);

$whereDept = '';
$params = [$year, $year];
$types = 'ii';
$allowed = null; // null = acceso global
if (!has_budget_role($user, ['ADMIN','CAPTURISTA','CONSULTA'])) {
    $allowed = [];
    foreach ($user['budget_permissions'] as $p) {
        if ($p['department_id'] !== null) $allowed[] = (int)$p['department_id'];
    }
    $allowed = array_values(array_unique($allowed));
    if (!$allowed && $user['department_id']) $allowed[] = (int)$user['department_id'];
    if ($allowed) {
        $whereDept = ' AND d.id IN (' . implode(',', array_fill(0, count($allowed), '?')) . ')';
        foreach ($allowed as $id) { $params[] = $id; $types .= 'i'; }
    } else {
        $whereDept = ' AND 1=0';
    }
}

$sql = "SELECT d.id, d.nombre,
               COALESCE(pd.presupuesto_asignado,0) AS asignado,
               COALESCE(SUM(CASE WHEN pm.tipo='ENTRADA' AND pm.status='REGISTRADO' THEN pm.monto ELSE 0 END),0) AS entradas,
               COALESCE(SUM(CASE WHEN pm.tipo='SALIDA' AND pm.status='REGISTRADO' THEN pm.monto ELSE 0 END),0) AS salidas
        FROM departamento d
        LEFT JOIN presupuesto_departamento pd ON pd.departamento_id=d.id AND pd.ejercicio=? AND pd.status=1
        LEFT JOIN presupuesto_movimiento pm ON pm.departamento_id=d.id AND pm.ejercicio=?
        WHERE d.status=1 $whereDept
        GROUP BY d.id,d.nombre,pd.presupuesto_asignado
        ORDER BY d.nombre";
$st = $con->prepare($sql);
$st->bind_param($types, ...$params);
$st->execute();
$rs = $st->get_result();
$departments=[];
$totals=['asignado'=>0.0,'entradas'=>0.0,'salidas'=>0.0,'disponible'=>0.0];
while($r=$rs->fetch_assoc()){
    $as=(float)$r['asignado']; $en=(float)$r['entradas']; $sa=(float)$r['salidas']; $di=$as+$en-$sa;
    $departments[]=['id'=>(int)$r['id'],'nombre'=>$r['nombre'],'asignado'=>$as,'entradas'=>$en,'salidas'=>$sa,'disponible'=>$di,'ejercido_pct'=>$as>0?round(($sa/$as)*100,1):0];
    $totals['asignado']+=$as; $totals['entradas']+=$en; $totals['salidas']+=$sa; $totals['disponible']+=$di;
}
$st->close();

$monthly=array_fill(1,12,['entrada'=>0.0,'salida'=>0.0]);
$sqlM="SELECT MONTH(fecha) mes,
             SUM(CASE WHEN tipo='ENTRADA' AND status='REGISTRADO' THEN monto ELSE 0 END) entradas,
             SUM(CASE WHEN tipo='SALIDA' AND status='REGISTRADO' THEN monto ELSE 0 END) salidas
      FROM presupuesto_movimiento
      WHERE ejercicio=?";
$mTypes='i'; $mParams=[$year];
if (is_array($allowed)) {
    if ($allowed) {
        $sqlM .= ' AND departamento_id IN (' . implode(',', array_fill(0,count($allowed),'?')) . ')';
        foreach ($allowed as $id) { $mTypes.='i'; $mParams[]=$id; }
    } else {
        $sqlM .= ' AND 1=0';
    }
}
$sqlM .= ' GROUP BY MONTH(fecha)';
$st=$con->prepare($sqlM); $st->bind_param($mTypes,...$mParams); $st->execute(); $rs=$st->get_result();
while($r=$rs->fetch_assoc()){ $m=(int)$r['mes']; $monthly[$m]=['entrada'=>(float)$r['entradas'],'salida'=>(float)$r['salidas']]; }
$st->close();
$con->close();

json_response(['ok'=>true,'data'=>['year'=>$year,'totals'=>$totals,'departments'=>$departments,'monthly'=>$monthly]]);

<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';
require_method('GET');
$user=require_permission('DEPARTAMENTOS_GESTIONAR');
$db=conectar();
if(!$db)json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);

$requiredTables=[
 'bitacora','departamento','movimiento_aclaracion','movimiento_aclaracion_mensaje','permiso',
 'presupuesto_departamento','presupuesto_folio_anual','presupuesto_movimiento',
 'presupuesto_movimiento_archivo','presupuesto_solicitud','presupuesto_subitem','rol','rol_permiso',
 'usuario','usuario_departamento'
];
$requiredViews=['vw_movimiento_detalle','vw_presupuesto_departamento_resumen','vw_usuario_perfil'];

$tables=[];$views=[];
$rs=$db->query("SELECT TABLE_NAME,TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()");
while($r=$rs->fetch_assoc()){
    if($r['TABLE_TYPE']==='VIEW')$views[]=$r['TABLE_NAME'];
    else $tables[]=$r['TABLE_NAME'];
}
$missingTables=array_values(array_diff($requiredTables,$tables));
$missingViews=array_values(array_diff($requiredViews,$views));

$demoUsers=[];
$rs=$db->query("SELECT username,estatus FROM usuario WHERE username IN ('admin.demo','presidente.demo','tesoreria.demo','cultura.director','cultura.supervisor','cultura.auxiliar','servicios.director') ORDER BY username");
if($rs)while($r=$rs->fetch_assoc())$demoUsers[]=$r;

$db->close();
json_response(['ok'=>true,'data'=>[
    'schema'=>'ixtla01_dep02',
    'tables_ok'=>count($missingTables)===0,
    'views_ok'=>count($missingViews)===0,
    'missing_tables'=>$missingTables,
    'missing_views'=>$missingViews,
    'demo_users'=>$demoUsers,
    'checked_by'=>$user['username']??null,
]]);

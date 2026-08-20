<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';
require_method('GET');

$expectedSchema='ixtla01_dep02';
$db=conectar();
$data=[
    'application'=>'Presupuesto Ixtlahuacán',
    'version'=>trim((string)@file_get_contents(project_root().'/VERSION')) ?: 'unknown',
    'status'=>'UP',
    'database'=>[
        'reachable'=>$db instanceof mysqli,
        'expected_schema'=>$expectedSchema,
        'active_schema'=>null,
        'schema_ok'=>false,
    ],
];

if($db){
    $r=$db->query('SELECT DATABASE() db');
    if($r&&$row=$r->fetch_assoc()){
        $data['database']['active_schema']=$row['db'];
        $data['database']['schema_ok']=$row['db']===$expectedSchema;
    }
    $db->close();
}else{
    $data['status']='DEGRADED';
}

json_response(['ok'=>true,'data'=>$data]);

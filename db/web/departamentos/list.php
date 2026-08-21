<?php

declare(strict_types=1);

require_once dirname(__DIR__,2).'/lib/bootstrap.php';
require_method('GET');
$user=require_login();
$db=conectar();
if(!$db) json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);

/*
 * Catálogo de departamentos y, opcionalmente, catálogo de usuarios de un
 * departamento. Se mantiene dentro de una ruta histórica ya publicada
 * (departamentos/list) para evitar depender de un controlador nuevo en Azure.
 *
 * Uso usuarios:
 *   api.php?route=departamentos/list&usuarios=1&departamento_id=5
 */
$usersMode=(string)($_GET['usuarios']??'0')==='1';
$departmentId=(int)($_GET['departamento_id']??0);

if($usersMode){
    if($departmentId<=0){
        $db->close();
        json_response(['ok'=>true,'data'=>[]]);
    }

    // Globales pueden seleccionar usuarios de cualquier departamento activo.
    // Perfiles departamentales solo pueden consultar su propio alcance.
    if(!user_is_global($user)){
        $ids=visible_department_ids($db,$user);
        if(!in_array($departmentId,$ids,true)){
            $db->close();
            json_response(['ok'=>false,'error'=>'No tienes acceso a este departamento'],403);
        }
    }

    $sql="SELECT DISTINCT
                u.usuario_id,
                CONCAT_WS(' ',u.nombre,u.apellido_paterno,u.apellido_materno) nombre,
                u.username,
                u.puesto,
                ud.departamento_id,
                r.codigo rol_codigo,
                r.nombre rol
          FROM usuario_departamento ud
          JOIN usuario u
            ON u.usuario_id=ud.usuario_id
           AND u.estatus='ACTIVO'
          JOIN rol r
            ON r.rol_id=ud.rol_id
           AND r.estatus='ACTIVO'
          JOIN departamento d
            ON d.departamento_id=ud.departamento_id
           AND d.estatus='ACTIVO'
          WHERE ud.departamento_id=?
            AND ud.estatus='ACTIVO'
          ORDER BY u.nombre,u.apellido_paterno,u.apellido_materno,u.username";

    $st=$db->prepare($sql);
    if(!$st){
        $error=$db->error?:'No se pudo preparar el catálogo de usuarios';
        $db->close();
        json_response(['ok'=>false,'error'=>$error],500);
    }
    $st->bind_param('i',$departmentId);
    if(!$st->execute()){
        $error=$st->error?:'No se pudo consultar el catálogo de usuarios';
        $st->close();$db->close();
        json_response(['ok'=>false,'error'=>$error],500);
    }

    $rs=$st->get_result();
    $data=[];
    $currentId=(int)($user['user_id']??0);
    while($r=$rs->fetch_assoc()){
        $r['usuario_id']=(int)$r['usuario_id'];
        $r['departamento_id']=(int)$r['departamento_id'];
        $r['is_current']=$r['usuario_id']===$currentId;
        $data[]=$r;
    }
    $st->close();$db->close();
    json_response(['ok'=>true,'data'=>$data]);
}

$q=trim((string)($_GET['q']??''));
$all=(string)($_GET['all']??'0')==='1';
$ids=visible_department_ids($db,$user);
$where=[];$types='';$params=[];
if(!$all)$where[]="d.estatus='ACTIVO'";
if(!user_is_global($user)){$where[]='d.departamento_id IN ('.($ids?implode(',',array_map('intval',$ids)):'0').')';}
if($q!==''){$where[]="(d.nombre LIKE CONCAT('%',?,'%') OR d.codigo LIKE CONCAT('%',?,'%'))";$types.='ss';$params[]=$q;$params[]=$q;}
$sql="SELECT d.*, (SELECT COUNT(DISTINCT ud.usuario_id) FROM usuario_departamento ud WHERE ud.departamento_id=d.departamento_id AND ud.estatus='ACTIVO') usuarios_count FROM departamento d".($where?' WHERE '.implode(' AND ',$where):'')." ORDER BY d.es_tesoreria DESC,d.nombre";
$st=$db->prepare($sql);
if($types)$st->bind_param($types,...$params);
$st->execute();$rs=$st->get_result();$data=[];
while($r=$rs->fetch_assoc()){$r['departamento_id']=(int)$r['departamento_id'];$r['es_tesoreria']=(int)$r['es_tesoreria'];$r['usuarios_count']=(int)$r['usuarios_count'];$data[]=$r;}
$st->close();$db->close();
json_response(['ok'=>true,'data'=>$data]);

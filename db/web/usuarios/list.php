<?php
require_once dirname(__DIR__,2).'/lib/bootstrap.php';require_method('GET');$user=require_permission('USUARIOS_GESTIONAR');$db=conectar();if(!$db)json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);
$q=trim((string)($_GET['q']??''));$departmentId=(int)($_GET['departamento_id']??0);$canManage=user_has_permission($user,'USUARIOS_GESTIONAR');$deptIds=visible_department_ids($db,$user);
$where=["u.estatus<>'INACTIVO'"];$types='';$params=[];
if(!$canManage&&!user_is_global($user)){$where[]='ud.departamento_id IN ('.($deptIds?implode(',',array_map('intval',$deptIds)):'0').')';}
if($departmentId>0){$where[]='ud.departamento_id=?';$types.='i';$params[]=$departmentId;}
if($q!==''){$where[]="(u.username LIKE CONCAT('%',?,'%') OR u.nombre LIKE CONCAT('%',?,'%') OR u.apellido_paterno LIKE CONCAT('%',?,'%') OR u.email LIKE CONCAT('%',?,'%'))";$types.='ssss';array_push($params,$q,$q,$q,$q);}
$sql="SELECT u.usuario_id,u.username,u.nombre,u.apellido_paterno,u.apellido_materno,u.email,u.telefono,u.puesto,u.estatus,u.requiere_cambio_password,u.ultimo_login_at,
      ud.usuario_departamento_id,ud.departamento_id,d.nombre departamento,ud.rol_id,r.codigo rol_codigo,r.nombre rol,r.alcance,ud.jefe_usuario_id,ud.es_principal,
      CONCAT_WS(' ',j.nombre,j.apellido_paterno,j.apellido_materno) jefe_nombre
      FROM usuario u LEFT JOIN usuario_departamento ud ON ud.usuario_id=u.usuario_id AND ud.estatus='ACTIVO'
      LEFT JOIN departamento d ON d.departamento_id=ud.departamento_id LEFT JOIN rol r ON r.rol_id=ud.rol_id LEFT JOIN usuario j ON j.usuario_id=ud.jefe_usuario_id
      WHERE ".implode(' AND ',$where)." ORDER BY u.nombre,u.apellido_paterno,ud.es_principal DESC,ud.usuario_departamento_id DESC";
$st=$db->prepare($sql);if($types)$st->bind_param($types,...$params);$st->execute();$rs=$st->get_result();$by=[];
while($r=$rs->fetch_assoc()){$uid=(int)$r['usuario_id'];if(!isset($by[$uid]))$by[$uid]=['usuario_id'=>$uid,'username'=>$r['username'],'nombre'=>$r['nombre'],'apellido_paterno'=>$r['apellido_paterno'],'apellido_materno'=>$r['apellido_materno'],'nombre_completo'=>trim(implode(' ',array_filter([$r['nombre'],$r['apellido_paterno'],$r['apellido_materno']]))),'email'=>$r['email'],'telefono'=>$r['telefono'],'puesto'=>$r['puesto'],'estatus'=>$r['estatus'],'requiere_cambio_password'=>(int)$r['requiere_cambio_password'],'ultimo_login_at'=>$r['ultimo_login_at'],'assignments'=>[]];if($r['usuario_departamento_id']!==null)$by[$uid]['assignments'][]=['assignment_id'=>(int)$r['usuario_departamento_id'],'departamento_id'=>$r['departamento_id']!==null?(int)$r['departamento_id']:null,'departamento'=>$r['departamento'],'rol_id'=>(int)$r['rol_id'],'rol_codigo'=>$r['rol_codigo'],'rol'=>$r['rol'],'alcance'=>$r['alcance'],'jefe_usuario_id'=>$r['jefe_usuario_id']!==null?(int)$r['jefe_usuario_id']:null,'jefe_nombre'=>$r['jefe_nombre'],'es_principal'=>(int)$r['es_principal']];}
$st->close();$db->close();json_response(['ok'=>true,'data'=>array_values($by)]);

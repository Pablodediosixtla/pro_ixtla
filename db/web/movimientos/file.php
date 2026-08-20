<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');
$user = require_login();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) json_response(['ok'=>false,'error'=>'Archivo inválido'],400);

$con = conectar();
if (!$con) json_response(['ok'=>false,'error'=>'No fue posible conectar a la base de datos'],500);
$sql = "SELECT a.nombre_original,a.ruta_relativa,a.mime_type,a.size_bytes,pm.departamento_id
        FROM presupuesto_movimiento_archivo a
        JOIN presupuesto_movimiento pm ON pm.id=a.movimiento_id
        WHERE a.id=? LIMIT 1";
$st=$con->prepare($sql);$st->bind_param('i',$id);$st->execute();$row=$st->get_result()->fetch_assoc();$st->close();$con->close();
if(!$row) json_response(['ok'=>false,'error'=>'Archivo no encontrado'],404);
$departmentId=(int)$row['departamento_id'];
if(!has_budget_role($user,['ADMIN'])&&!has_budget_role($user,['CAPTURISTA','CONSULTA'],$departmentId)) json_response(['ok'=>false,'error'=>'Sin permiso'],403);
$path=project_root().'/'.ltrim($row['ruta_relativa'],'/');
if(!is_file($path)) json_response(['ok'=>false,'error'=>'El archivo físico no está disponible'],404);
header('Content-Type: '.$row['mime_type']);
header('Content-Length: '.filesize($path));
header('Content-Disposition: inline; filename="'.rawurlencode($row['nombre_original']).'"');
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;

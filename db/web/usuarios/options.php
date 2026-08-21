<?php

declare(strict_types=1);

require_once dirname(__DIR__,2).'/lib/bootstrap.php';

require_method('GET');
$user = require_login();
$db = conectar();
if (!$db) json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);

$departmentId = (int)($_GET['departamento_id'] ?? 0);
if ($departmentId <= 0) {
    $db->close();
    json_response(['ok'=>true,'data'=>[]]);
}

// Aunque ADMIN, PRESIDENTE y TESORERIA tengan alcance global, el catálogo de
// personas para un movimiento siempre queda acotado al departamento elegido.
// Un perfil no global además debe tener acceso real a ese departamento.
if (!user_is_global($user)) {
    $ids = visible_department_ids($db, $user);
    if (!in_array($departmentId, $ids, true)) {
        $db->close();
        json_response(['ok'=>true,'data'=>[]]);
    }
}

$sql = "SELECT DISTINCT
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
        WHERE ud.departamento_id=?
          AND ud.estatus='ACTIVO'
        ORDER BY u.nombre,u.apellido_paterno,u.apellido_materno,u.username";

$st = $db->prepare($sql);
if (!$st) {
    $error = $db->error ?: 'No se pudo preparar el catálogo de usuarios';
    $db->close();
    json_response(['ok'=>false,'error'=>$error],500);
}
$st->bind_param('i',$departmentId);
if (!$st->execute()) {
    $error = $st->error ?: 'No se pudo consultar el catálogo de usuarios';
    $st->close();
    $db->close();
    json_response(['ok'=>false,'error'=>$error],500);
}

$rs = $st->get_result();
$data = [];
$currentId = (int)($user['user_id'] ?? 0);
while ($r = $rs->fetch_assoc()) {
    $r['usuario_id'] = (int)$r['usuario_id'];
    $r['departamento_id'] = (int)$r['departamento_id'];
    $r['is_current'] = $r['usuario_id'] === $currentId;
    $data[] = $r;
}
$st->close();
$db->close();

json_response(['ok'=>true,'data'=>$data]);

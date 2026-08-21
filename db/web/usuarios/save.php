<?php

require_once dirname(__DIR__,2).'/lib/bootstrap.php';

require_method('POST');
require_csrf();
$actor = require_permission('USUARIOS_GESTIONAR');
$in = json_input();

$id = (int)($in['usuario_id'] ?? 0);
$username = strtolower(trim((string)($in['username'] ?? '')));
$name = trim((string)($in['nombre'] ?? ''));
$ap = trim((string)($in['apellido_paterno'] ?? ''));
$am = trim((string)($in['apellido_materno'] ?? ''));
$email = strtolower(trim((string)($in['email'] ?? '')));
$phone = trim((string)($in['telefono'] ?? ''));
$position = trim((string)($in['puesto'] ?? ''));
$status = in_array(($in['estatus'] ?? 'ACTIVO'), ['ACTIVO','INACTIVO','BLOQUEADO'], true)
    ? (string)$in['estatus']
    : 'ACTIVO';
$password = (string)($in['password'] ?? '');
$departmentId = ($in['departamento_id'] ?? '') !== '' ? (int)$in['departamento_id'] : null;
$roleId = (int)($in['rol_id'] ?? 0);
$bossId = ($in['jefe_usuario_id'] ?? '') !== '' ? (int)$in['jefe_usuario_id'] : null;

if ($username === '' || $name === '' || $roleId <= 0) {
    json_response(['ok'=>false,'error'=>'Usuario, nombre y rol son obligatorios'],400);
}
if ($id <= 0 && strlen($password) < 8) {
    json_response(['ok'=>false,'error'=>'La contraseña inicial debe tener al menos 8 caracteres'],400);
}

$db = conectar();
if (!$db) {
    json_response(['ok'=>false,'error'=>'Sin conexión a base de datos'],503);
}

$actorId = (int)$actor['user_id'];

// Un usuario no global solo puede administrar departamentos de su alcance.
if (!user_is_global($actor)) {
    $visible = visible_department_ids($db, $actor);
    if ($departmentId === null || !in_array($departmentId, $visible, true)) {
        $db->close();
        json_response(['ok'=>false,'error'=>'No puedes administrar usuarios fuera de tu departamento'],403);
    }
}

$db->begin_transaction();

try {
    $before = null;

    if ($id > 0) {
        $st = $db->prepare(
            "SELECT u.usuario_id,u.username,u.nombre,u.apellido_paterno,u.apellido_materno,
                    u.email,u.telefono,u.puesto,u.estatus,
                    ud.departamento_id,ud.rol_id,ud.jefe_usuario_id
             FROM usuario u
             LEFT JOIN usuario_departamento ud
               ON ud.usuario_id=u.usuario_id
              AND ud.estatus='ACTIVO'
              AND ud.es_principal=1
             WHERE u.usuario_id=?
             LIMIT 1"
        );
        if (!$st) throw new RuntimeException('No se pudo preparar la consulta del usuario');
        $st->bind_param('i', $id);
        user_stmt_execute_or_throw($st, 'No se pudo consultar el usuario');
        $before = $st->get_result()->fetch_assoc();
        $st->close();

        if (!$before) {
            throw new RuntimeException('El usuario a editar no existe');
        }

        if ($password !== '') {
            if (strlen($password) < 8) {
                throw new RuntimeException('La nueva contraseña debe tener al menos 8 caracteres');
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $st = $db->prepare(
                "UPDATE usuario
                 SET username=?,nombre=?,apellido_paterno=?,apellido_materno=?,
                     email=NULLIF(?,''),telefono=?,puesto=?,estatus=?,
                     password_hash=?,requiere_cambio_password=1,
                     bloqueado_hasta=NULL,intentos_fallidos=0
                 WHERE usuario_id=?"
            );
            if (!$st) throw new RuntimeException('No se pudo preparar la actualización del usuario');
            $st->bind_param(
                'sssssssssi',
                $username,$name,$ap,$am,$email,$phone,$position,$status,$hash,$id
            );
        } else {
            $st = $db->prepare(
                "UPDATE usuario
                 SET username=?,nombre=?,apellido_paterno=?,apellido_materno=?,
                     email=NULLIF(?,''),telefono=?,puesto=?,estatus=?
                 WHERE usuario_id=?"
            );
            if (!$st) throw new RuntimeException('No se pudo preparar la actualización del usuario');
            $st->bind_param(
                'ssssssssi',
                $username,$name,$ap,$am,$email,$phone,$position,$status,$id
            );
        }

        user_stmt_execute_or_throw($st, 'No se pudo actualizar el usuario');
        $st->close();
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $uuid = bin2hex(random_bytes(16));
        $uuid = substr($uuid,0,8).'-'.substr($uuid,8,4).'-4'.substr($uuid,13,3).'-a'.substr($uuid,17,3).'-'.substr($uuid,20,12);

        $st = $db->prepare(
            "INSERT INTO usuario
             (uuid,username,nombre,apellido_paterno,apellido_materno,email,telefono,puesto,
              password_hash,estatus,requiere_cambio_password,created_by_usuario_id)
             VALUES(?,?,?,?,?,NULLIF(?,''),?,?,?,?,1,?)"
        );
        if (!$st) throw new RuntimeException('No se pudo preparar la creación del usuario');
        $st->bind_param(
            'ssssssssssi',
            $uuid,$username,$name,$ap,$am,$email,$phone,$position,$hash,$status,$actorId
        );
        user_stmt_execute_or_throw($st, 'No se pudo crear el usuario');
        $id = (int)$st->insert_id;
        $st->close();

        if ($id <= 0) {
            throw new RuntimeException('La creación del usuario no generó un identificador válido');
        }
    }

    // Guarda rol + departamento + jefe como una sola asignación principal validada.
    $assignment = user_management_save_primary_assignment(
        $db,
        $id,
        $roleId,
        $departmentId,
        $bossId,
        $actorId
    );

    // Nunca escribir password en bitácora.
    $auditInput = $in;
    unset($auditInput['password']);
    $auditInput['departamento_id'] = $assignment['departamento_id'];
    $auditInput['rol_id'] = $assignment['rol_id'];
    $auditInput['jefe_usuario_id'] = $assignment['jefe_usuario_id'];

    audit_log(
        $db,
        $actorId,
        $before ? 'USUARIO_EDITAR' : 'USUARIO_CREAR',
        'USUARIO',
        $id,
        $username,
        $before,
        $auditInput
    );

    $db->commit();

    // Si el administrador editó su propia cuenta, refresca permisos/alcance en la sesión.
    if ($id === $actorId) {
        refresh_session_user($db);
    }

    $db->close();

    json_response([
        'ok'=>true,
        'data'=>[
            'usuario_id'=>$id,
            'assignment'=>$assignment,
            'created'=>$before === null,
        ],
    ]);
} catch (Throwable $e) {
    $db->rollback();
    $db->close();
    json_response(['ok'=>false,'error'=>'No se pudo guardar el usuario: '.$e->getMessage()],409);
}

<?php

declare(strict_types=1);

/**
 * Ejecuta un statement y convierte errores silenciosos de mysqli en excepciones.
 * La conexión del proyecto usa MYSQLI_REPORT_OFF, por lo que es importante
 * revisar execute() explícitamente en operaciones transaccionales.
 */
function user_stmt_execute_or_throw(mysqli_stmt $stmt, string $context): void {
    if (!$stmt->execute()) {
        $message = trim((string)$stmt->error);
        throw new RuntimeException($context . ($message !== '' ? ': ' . $message : ''));
    }
}

/**
 * Obtiene y valida un rol activo.
 */
function user_management_role(mysqli $db, int $roleId): array {
    $st = $db->prepare("SELECT rol_id,codigo,nombre,alcance,estatus FROM rol WHERE rol_id=? AND estatus='ACTIVO' LIMIT 1");
    if (!$st) throw new RuntimeException('No se pudo preparar la validación del rol');
    $st->bind_param('i', $roleId);
    user_stmt_execute_or_throw($st, 'No se pudo validar el rol');
    $role = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$role) throw new RuntimeException('El rol seleccionado no existe o está inactivo');
    $role['rol_id'] = (int)$role['rol_id'];
    return $role;
}

/**
 * Valida que el departamento exista y esté activo.
 */
function user_management_validate_department(mysqli $db, int $departmentId): array {
    $st = $db->prepare("SELECT departamento_id,codigo,nombre,estatus FROM departamento WHERE departamento_id=? AND estatus='ACTIVO' LIMIT 1");
    if (!$st) throw new RuntimeException('No se pudo preparar la validación del departamento');
    $st->bind_param('i', $departmentId);
    user_stmt_execute_or_throw($st, 'No se pudo validar el departamento');
    $department = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$department) throw new RuntimeException('El departamento seleccionado no existe o está inactivo');
    $department['departamento_id'] = (int)$department['departamento_id'];
    return $department;
}

/**
 * Valida que el jefe seleccionado sea un usuario activo del mismo departamento.
 */
function user_management_validate_boss(mysqli $db, int $bossId, int $userId, int $departmentId): void {
    if ($bossId === $userId) {
        throw new RuntimeException('Un usuario no puede reportarse a sí mismo');
    }

    $st = $db->prepare(
        "SELECT u.usuario_id
         FROM usuario u
         JOIN usuario_departamento ud
           ON ud.usuario_id=u.usuario_id
          AND ud.estatus='ACTIVO'
         WHERE u.usuario_id=?
           AND u.estatus='ACTIVO'
           AND ud.departamento_id=?
         LIMIT 1"
    );
    if (!$st) throw new RuntimeException('No se pudo preparar la validación del jefe');
    $st->bind_param('ii', $bossId, $departmentId);
    user_stmt_execute_or_throw($st, 'No se pudo validar el jefe seleccionado');
    $boss = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$boss) {
        throw new RuntimeException('La persona a la que reporta debe estar activa y pertenecer al mismo departamento');
    }
}

/**
 * Guarda exactamente una asignación principal activa.
 *
 * En versiones anteriores se desactivaba la asignación y después siempre se
 * intentaba INSERT. Como existe una llave única por usuario/departamento/rol,
 * volver a una combinación histórica podía fallar silenciosamente y el API
 * respondía como si hubiera guardado. Aquí se reutiliza la fila histórica
 * cuando existe y se verifica el resultado antes del COMMIT.
 */
function user_management_save_primary_assignment(
    mysqli $db,
    int $userId,
    int $roleId,
    ?int $departmentId,
    ?int $bossId,
    int $actorId
): array {
    $role = user_management_role($db, $roleId);
    $scope = (string)$role['alcance'];

    // Los roles globales no se amarran a un departamento ni a un jefe.
    if ($scope === 'GLOBAL') {
        $departmentId = null;
        $bossId = null;
    } else {
        if ($departmentId === null || $departmentId <= 0) {
            throw new RuntimeException('El rol seleccionado requiere un departamento');
        }
        user_management_validate_department($db, $departmentId);
        if ($bossId !== null) {
            user_management_validate_boss($db, $bossId, $userId, $departmentId);
        }
    }

    // Bloquea las asignaciones del usuario durante la sustitución.
    $st = $db->prepare(
        "SELECT usuario_departamento_id,departamento_id,rol_id,estatus
         FROM usuario_departamento
         WHERE usuario_id=?
         FOR UPDATE"
    );
    if (!$st) throw new RuntimeException('No se pudo preparar el bloqueo de la jerarquía');
    $st->bind_param('i', $userId);
    user_stmt_execute_or_throw($st, 'No se pudo bloquear la jerarquía del usuario');
    $st->get_result();
    $st->close();

    // Busca una combinación histórica equivalente para reactivarla.
    if ($departmentId === null) {
        $st = $db->prepare(
            "SELECT usuario_departamento_id
             FROM usuario_departamento
             WHERE usuario_id=? AND departamento_id IS NULL AND rol_id=?
             ORDER BY usuario_departamento_id DESC
             LIMIT 1"
        );
        if (!$st) throw new RuntimeException('No se pudo preparar la búsqueda de la asignación global');
        $st->bind_param('ii', $userId, $roleId);
    } else {
        $st = $db->prepare(
            "SELECT usuario_departamento_id
             FROM usuario_departamento
             WHERE usuario_id=? AND departamento_id=? AND rol_id=?
             ORDER BY usuario_departamento_id DESC
             LIMIT 1"
        );
        if (!$st) throw new RuntimeException('No se pudo preparar la búsqueda de la asignación');
        $st->bind_param('iii', $userId, $departmentId, $roleId);
    }
    user_stmt_execute_or_throw($st, 'No se pudo buscar la asignación seleccionada');
    $existing = $st->get_result()->fetch_assoc();
    $st->close();

    // Primero desactiva cualquier asignación principal vigente.
    $st = $db->prepare(
        "UPDATE usuario_departamento
         SET estatus='INACTIVO',es_principal=0
         WHERE usuario_id=? AND (estatus='ACTIVO' OR es_principal=1)"
    );
    if (!$st) throw new RuntimeException('No se pudo preparar la actualización de la jerarquía');
    $st->bind_param('i', $userId);
    user_stmt_execute_or_throw($st, 'No se pudo desactivar la asignación anterior');
    $st->close();

    if ($existing) {
        $assignmentId = (int)$existing['usuario_departamento_id'];
        $st = $db->prepare(
            "UPDATE usuario_departamento
             SET jefe_usuario_id=?,es_principal=1,estatus='ACTIVO',created_by_usuario_id=?
             WHERE usuario_departamento_id=?"
        );
        if (!$st) throw new RuntimeException('No se pudo preparar la reactivación de la asignación');
        $st->bind_param('iii', $bossId, $actorId, $assignmentId);
        user_stmt_execute_or_throw($st, 'No se pudo reactivar la asignación seleccionada');
        $st->close();
    } else {
        $st = $db->prepare(
            "INSERT INTO usuario_departamento
             (usuario_id,departamento_id,rol_id,jefe_usuario_id,es_principal,estatus,created_by_usuario_id)
             VALUES(?,?,?,?,1,'ACTIVO',?)"
        );
        if (!$st) throw new RuntimeException('No se pudo preparar la nueva asignación');
        $st->bind_param('iiiii', $userId, $departmentId, $roleId, $bossId, $actorId);
        user_stmt_execute_or_throw($st, 'No se pudo crear la asignación de rol y departamento');
        $assignmentId = (int)$st->insert_id;
        $st->close();

        if ($assignmentId <= 0) {
            throw new RuntimeException('La asignación de rol y departamento no generó un identificador válido');
        }
    }

    // Lee lo realmente persistido y lo usa como verificación final.
    $st = $db->prepare(
        "SELECT ud.usuario_departamento_id,ud.usuario_id,ud.departamento_id,
                d.nombre departamento,ud.rol_id,r.codigo rol_codigo,r.nombre rol,
                r.alcance,ud.jefe_usuario_id,
                CONCAT_WS(' ',j.nombre,j.apellido_paterno,j.apellido_materno) jefe_nombre,
                ud.es_principal,ud.estatus
         FROM usuario_departamento ud
         JOIN rol r ON r.rol_id=ud.rol_id
         LEFT JOIN departamento d ON d.departamento_id=ud.departamento_id
         LEFT JOIN usuario j ON j.usuario_id=ud.jefe_usuario_id
         WHERE ud.usuario_departamento_id=?
           AND ud.estatus='ACTIVO'
           AND ud.es_principal=1
         LIMIT 1"
    );
    if (!$st) throw new RuntimeException('No se pudo preparar la verificación de la asignación');
    $st->bind_param('i', $assignmentId);
    user_stmt_execute_or_throw($st, 'No se pudo verificar la asignación guardada');
    $saved = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$saved) {
        throw new RuntimeException('No fue posible confirmar la asignación después de guardarla');
    }

    $savedRoleId = (int)$saved['rol_id'];
    $savedDepartmentId = $saved['departamento_id'] !== null ? (int)$saved['departamento_id'] : null;
    if ($savedRoleId !== $roleId || $savedDepartmentId !== $departmentId) {
        throw new RuntimeException('La verificación detectó que el rol o departamento persistido no coincide con la selección');
    }

    return [
        'assignment_id'=>(int)$saved['usuario_departamento_id'],
        'departamento_id'=>$savedDepartmentId,
        'departamento'=>$saved['departamento'],
        'rol_id'=>$savedRoleId,
        'rol_codigo'=>$saved['rol_codigo'],
        'rol'=>$saved['rol'],
        'alcance'=>$saved['alcance'],
        'jefe_usuario_id'=>$saved['jefe_usuario_id'] !== null ? (int)$saved['jefe_usuario_id'] : null,
        'jefe_nombre'=>$saved['jefe_nombre'],
    ];
}

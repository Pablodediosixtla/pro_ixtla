<?php

declare(strict_types=1);

function current_user(): ?array {
    start_app_session();
    return $_SESSION['user'] ?? null;
}

function require_login(): array {
    $user = current_user();
    if (!$user) {
        json_response(['ok'=>false,'error'=>'Sesión no válida'], 401);
    }
    return $user;
}

function user_permission_codes(array $user): array {
    return array_values(array_unique(array_map('strval', $user['permissions'] ?? [])));
}

function user_has_permission(array $user, string $permission): bool {
    return in_array($permission, user_permission_codes($user), true);
}

function user_has_any_permission(array $user, array $permissions): bool {
    foreach ($permissions as $permission) {
        if (user_has_permission($user, $permission)) return true;
    }
    return false;
}

function require_permission(string ...$permissions): array {
    $user = require_login();
    if (!$permissions || user_has_any_permission($user, $permissions)) return $user;
    json_response(['ok'=>false,'error'=>'No tienes permisos para esta operación'],403);
}

function user_role_codes(array $user): array {
    return array_values(array_unique(array_map(
        static fn(array $a): string => (string)($a['role_code'] ?? ''),
        $user['assignments'] ?? []
    )));
}

function user_has_any_role(array $user, array $roles): bool {
    return (bool) array_intersect(user_role_codes($user), $roles);
}

/**
 * Asignación efectiva del usuario.
 *
 * La aplicación administra una sola asignación principal activa por usuario.
 * Si por datos históricos quedara otra asignación ACTIVA, la principal es la
 * que gobierna el alcance de navegación y de información.
 */
function user_primary_assignment(array $user): ?array {
    $assignments = $user['assignments'] ?? [];
    if (!$assignments) return null;

    foreach ($assignments as $assignment) {
        if (($assignment['primary'] ?? false) === true) return $assignment;
    }

    return $assignments[0] ?? null;
}

function user_is_global(array $user): bool {
    $primary = user_primary_assignment($user);

    if ($primary !== null) {
        $roleCode = strtoupper((string)($primary['role_code'] ?? ''));
        $scope = strtoupper((string)($primary['scope'] ?? ''));

        // Roles municipales con alcance global explícito.
        if (in_array($roleCode, ['ADMIN','PRESIDENTE','TESORERIA'], true)) {
            return true;
        }

        // Director, Supervisor y Subordinado nunca heredan accidentalmente
        // alcance GLOBAL de una asignación histórica secundaria.
        if (in_array($roleCode, ['DIRECTOR','SUPERVISOR','SUBORDINADO'], true)) {
            return false;
        }

        // Roles personalizados: respeta el alcance de su asignación principal.
        return $scope === 'GLOBAL';
    }

    return false;
}

/**
 * Devuelve true cuando el usuario únicamente tiene asignaciones de alcance PROPIO.
 * Se usa para perfiles subordinados y para cualquier rol personalizado equivalente.
 */
function user_is_own_scope_only(array $user): bool {
    $primary = user_primary_assignment($user);
    if ($primary === null) return false;
    return strtoupper((string)($primary['scope'] ?? '')) === 'PROPIO';
}

/**
 * La información presupuestal agregada del departamento (asignado, entradas,
 * disponible y distribución) requiere al menos alcance de JERARQUIA.
 */
function user_can_view_department_financials(array $user): bool {
    return !user_is_own_scope_only($user);
}

function user_department_ids(array $user): array {
    $primary = user_primary_assignment($user);

    // Para perfiles departamentales, el departamento principal es la frontera
    // de seguridad. Esto evita sumar información de otras áreas por registros
    // históricos que hayan quedado activos.
    if ($primary !== null) {
        $scope = strtoupper((string)($primary['scope'] ?? ''));
        $departmentId = $primary['department_id'] ?? null;

        if ($scope !== 'GLOBAL' && $departmentId !== null) {
            return [(int)$departmentId];
        }

        if ($scope === 'GLOBAL') {
            return [];
        }
    }

    // Fallback defensivo para cuentas antiguas sin asignación marcada principal.
    $ids = [];
    foreach ($user['assignments'] ?? [] as $a) {
        $scope = strtoupper((string)($a['scope'] ?? ''));
        if ($scope === 'GLOBAL') continue;
        if (($a['department_id'] ?? null) !== null) $ids[] = (int)$a['department_id'];
    }
    return array_values(array_unique($ids));
}

function load_user_context(mysqli $db, int $userId): ?array {
    $st=$db->prepare("SELECT usuario_id,uuid,username,nombre,apellido_paterno,apellido_materno,email,telefono,puesto,estatus,requiere_cambio_password,ultimo_login_at
                      FROM usuario WHERE usuario_id=? LIMIT 1");
    $st->bind_param('i',$userId);$st->execute();$base=$st->get_result()->fetch_assoc();$st->close();
    if(!$base || $base['estatus']!=='ACTIVO') return null;

    $assignments=[];
    $sql="SELECT ud.usuario_departamento_id,ud.departamento_id,d.codigo departamento_codigo,d.nombre departamento,
                 ud.rol_id,r.codigo role_code,r.nombre role_name,r.alcance scope,ud.jefe_usuario_id,ud.es_principal
          FROM usuario_departamento ud
          JOIN rol r ON r.rol_id=ud.rol_id AND r.estatus='ACTIVO'
          LEFT JOIN departamento d ON d.departamento_id=ud.departamento_id
          WHERE ud.usuario_id=? AND ud.estatus='ACTIVO'
          ORDER BY ud.es_principal DESC,r.rol_id";
    $st=$db->prepare($sql);$st->bind_param('i',$userId);$st->execute();$rs=$st->get_result();
    while($r=$rs->fetch_assoc()){
        $assignments[]=[
            'assignment_id'=>(int)$r['usuario_departamento_id'],
            'department_id'=>$r['departamento_id']!==null?(int)$r['departamento_id']:null,
            'department_code'=>$r['departamento_codigo'],
            'department'=>$r['departamento'],
            'role_id'=>(int)$r['rol_id'],
            'role_code'=>$r['role_code'],
            'role_name'=>$r['role_name'],
            'scope'=>$r['scope'],
            'boss_user_id'=>$r['jefe_usuario_id']!==null?(int)$r['jefe_usuario_id']:null,
            'primary'=>(int)$r['es_principal']===1,
        ];
    }
    $st->close();

    $permissions=[];
    $st=$db->prepare("SELECT DISTINCT p.codigo
                      FROM usuario_departamento ud
                      JOIN rol r ON r.rol_id=ud.rol_id AND r.estatus='ACTIVO'
                      JOIN rol_permiso rp ON rp.rol_id=r.rol_id
                      JOIN permiso p ON p.permiso_id=rp.permiso_id
                      WHERE ud.usuario_id=? AND ud.estatus='ACTIVO'");
    $st->bind_param('i',$userId);$st->execute();$rs=$st->get_result();
    while($r=$rs->fetch_assoc()) $permissions[]=(string)$r['codigo'];
    $st->close();

    $name=trim(implode(' ',array_filter([$base['nombre'],$base['apellido_paterno'],$base['apellido_materno']])));
    return [
        'user_id'=>(int)$base['usuario_id'],
        'uuid'=>$base['uuid'],
        'username'=>$base['username'],
        'name'=>$name,
        'first_name'=>$base['nombre'],
        'email'=>$base['email'],
        'phone'=>$base['telefono'],
        'position'=>$base['puesto'],
        'must_change_password'=>(int)$base['requiere_cambio_password']===1,
        'last_login_at'=>$base['ultimo_login_at'],
        'assignments'=>$assignments,
        'permissions'=>array_values(array_unique($permissions)),
    ];
}

function refresh_session_user(mysqli $db): ?array {
    start_app_session();
    $uid=(int)($_SESSION['user']['user_id']??0);
    if($uid<=0) return null;
    $user=load_user_context($db,$uid);
    if($user) $_SESSION['user']=$user;
    return $user;
}

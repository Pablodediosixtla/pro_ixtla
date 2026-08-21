<?php

declare(strict_types=1);

function visible_department_ids(mysqli $db, array $user): array {
    if (user_is_global($user)) {
        $ids = [];
        $rs = $db->query("SELECT departamento_id FROM departamento WHERE estatus='ACTIVO' ORDER BY departamento_id");
        if ($rs) while ($r = $rs->fetch_assoc()) $ids[] = (int)$r['departamento_id'];
        return $ids;
    }

    // Director, Supervisor, Subordinado y roles personalizados no globales
    // quedan limitados al departamento de su asignación principal.
    return user_department_ids($user);
}

function scope_rank(string $scope): int {
    return match($scope){'GLOBAL'=>4,'DEPARTAMENTO'=>3,'JERARQUIA'=>2,'PROPIO'=>1,default=>0};
}

function highest_scope_for_department(array $user, int $departmentId): string {
    // El alcance efectivo siempre parte de la asignación principal activa.
    // Evita que una asignación histórica secundaria con alcance GLOBAL amplíe
    // accidentalmente la visibilidad de un Director/Supervisor/Subordinado.
    if (user_is_global($user)) return 'GLOBAL';

    $primary = user_primary_assignment($user);
    if ($primary !== null) {
        $primaryDepartment = (int)($primary['department_id'] ?? 0);
        $primaryScope = strtoupper((string)($primary['scope'] ?? ''));
        if ($primaryDepartment === $departmentId) return $primaryScope;
        return '';
    }

    // Fallback defensivo para cuentas antiguas sin asignación principal.
    $best='';$rank=0;
    foreach($user['assignments']??[] as $a){
        $scope = strtoupper((string)($a['scope']??''));
        if($scope==='GLOBAL') continue;
        if((int)($a['department_id']??0)!==$departmentId) continue;
        $r=scope_rank($scope);
        if($r>$rank){$rank=$r;$best=$scope;}
    }
    return $best;
}

function hierarchy_user_ids(mysqli $db, int $rootUserId, int $departmentId): array {
    $sql="WITH RECURSIVE tree AS (
            SELECT ud.usuario_id
            FROM usuario_departamento ud
            WHERE ud.usuario_id=? AND ud.departamento_id=? AND ud.estatus='ACTIVO'
            UNION DISTINCT
            SELECT child.usuario_id
            FROM usuario_departamento child
            JOIN tree t ON child.jefe_usuario_id=t.usuario_id
            WHERE child.departamento_id=? AND child.estatus='ACTIVO'
          ) SELECT DISTINCT usuario_id FROM tree";
    $st=$db->prepare($sql);$st->bind_param('iii',$rootUserId,$departmentId,$departmentId);$st->execute();$rs=$st->get_result();$ids=[];
    while($r=$rs->fetch_assoc())$ids[]=(int)$r['usuario_id'];$st->close();
    if(!$ids)$ids=[$rootUserId];
    return array_values(array_unique($ids));
}

function movement_is_visible(mysqli $db, array $user, array $movement): bool {
    if(user_is_global($user)) return true;
    $departmentId=(int)($movement['departamento_id']??0);
    $scope=highest_scope_for_department($user,$departmentId);
    // Director (DEPARTAMENTO) y Supervisor (JERARQUIA) tienen visión
    // operativa completa de su departamento. Esto incluye movimientos creados
    // por Admin, Presidencia o Tesorería siempre que pertenezcan al mismo depto.
    // La jerarquía se conserva para estructura organizacional, pero no recorta
    // la información financiera departamental del Supervisor.
    if(in_array($scope,['DEPARTAMENTO','JERARQUIA'],true)) return true;

    $uid=(int)$user['user_id'];
    $type=strtoupper((string)($movement['tipo']??''));

    if($scope==='PROPIO'){
        // Un subordinado solo ve salidas que él mismo solicitó. No ve entradas
        // departamentales ni movimientos registrados/otorgados a terceros.
        return $type==='SALIDA' && (int)($movement['solicitado_por_usuario_id']??0)===$uid;
    }
    return false;
}

function request_is_visible(mysqli $db, array $user, array $request): bool {
    if(user_is_global($user)) return true;
    $departmentId=(int)($request['departamento_id']??0);
    $scope=highest_scope_for_department($user,$departmentId);
    // Director y Supervisor ven todas las solicitudes de su departamento.
    // El subordinado mantiene alcance estrictamente propio.
    if(in_array($scope,['DEPARTAMENTO','JERARQUIA'],true)) return true;
    $uid=(int)$user['user_id'];
    if($scope==='PROPIO'){
        // El subordinado ve exclusivamente las solicitudes que él originó.
        return (int)($request['solicitado_por_usuario_id']??0)===$uid;
    }
    return false;
}

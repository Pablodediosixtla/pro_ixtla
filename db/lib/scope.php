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
    $best='';$rank=0;
    foreach($user['assignments']??[] as $a){
        if(($a['scope']??'')==='GLOBAL') return 'GLOBAL';
        if((int)($a['department_id']??0)!==$departmentId) continue;
        $r=scope_rank((string)$a['scope']);
        if($r>$rank){$rank=$r;$best=(string)$a['scope'];}
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
    if($scope==='DEPARTAMENTO') return true;

    $uid=(int)$user['user_id'];
    $type=strtoupper((string)($movement['tipo']??''));

    if($scope==='JERARQUIA'){
        // Supervisores sí pueden consultar las entradas de dinero de su departamento.
        if($type==='ENTRADA') return true;

        // Para salidas, conservan el alcance de su jerarquía.
        $ids=hierarchy_user_ids($db,$uid,$departmentId);
        foreach(['solicitado_por_usuario_id','otorgado_a_usuario_id'] as $field){
            $id=(int)($movement[$field]??0);
            if($id>0 && in_array($id,$ids,true)) return true;
        }
        return false;
    }

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
    if($scope==='DEPARTAMENTO') return true;
    $uid=(int)$user['user_id'];
    if($scope==='JERARQUIA'){
        $ids=hierarchy_user_ids($db,$uid,$departmentId);
        return in_array((int)($request['solicitado_por_usuario_id']??0),$ids,true)
            || in_array((int)($request['otorgado_a_usuario_id']??0),$ids,true);
    }
    if($scope==='PROPIO'){
        // El subordinado ve exclusivamente las solicitudes que él originó.
        return (int)($request['solicitado_por_usuario_id']??0)===$uid;
    }
    return false;
}

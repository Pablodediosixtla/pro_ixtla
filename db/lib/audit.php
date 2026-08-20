<?php

declare(strict_types=1);

function client_ip(): string {
    $candidates=['HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'];
    foreach($candidates as $k){
        $v=trim((string)($_SERVER[$k]??''));
        if($v!=='') return substr(trim(explode(',',$v)[0]),0,45);
    }
    return '';
}

function audit_log(mysqli $db, ?int $userId, string $action, string $entity, string|int|null $entityId, ?string $description=null, mixed $before=null, mixed $after=null): void {
    $beforeJson=$before!==null?json_encode($before,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):null;
    $afterJson=$after!==null?json_encode($after,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):null;
    $ip=client_ip();$ua=substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,500);$eid=$entityId!==null?(string)$entityId:null;
    $st=$db->prepare("INSERT INTO bitacora(usuario_id,accion,entidad,entidad_id,descripcion,datos_antes,datos_despues,ip,user_agent) VALUES(?,?,?,?,?,?,?,?,?)");
    if(!$st)return;
    $st->bind_param('issssssss',$userId,$action,$entity,$eid,$description,$beforeJson,$afterJson,$ip,$ua);
    @$st->execute();$st->close();
}

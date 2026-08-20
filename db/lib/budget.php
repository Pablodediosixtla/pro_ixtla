<?php

declare(strict_types=1);

function money_value(mixed $value): float {
    if(is_string($value))$value=str_replace([',','$',' '],'',$value);
    return round((float)$value,2);
}

function current_year(): int { return (int)date('Y'); }

function next_folio(mysqli $db, string $type, int $year): string {
    $type=strtoupper($type);
    $prefix=match($type){'SOLICITUD'=>'SOL','ACLARACION'=>'ACL',default=>'FOL'};
    $db->begin_transaction();
    try{
        $st=$db->prepare("INSERT INTO presupuesto_folio_anual(ejercicio,tipo,ultimo_folio) VALUES(?,?,1)
                          ON DUPLICATE KEY UPDATE ultimo_folio=ultimo_folio+1");
        $st->bind_param('is',$year,$type);$st->execute();$st->close();
        $st=$db->prepare("SELECT ultimo_folio FROM presupuesto_folio_anual WHERE ejercicio=? AND tipo=? FOR UPDATE");
        $st->bind_param('is',$year,$type);$st->execute();$row=$st->get_result()->fetch_assoc();$st->close();
        if(!$row)throw new RuntimeException('No se pudo generar el folio');
        $db->commit();
        return sprintf('%s-%d-%06d',$prefix,$year,(int)$row['ultimo_folio']);
    }catch(Throwable $e){$db->rollback();throw $e;}
}

function department_balance(mysqli $db,int $departmentId,int $year):array{
    $sql="SELECT COALESCE(pd.presupuesto_asignado,0) asignado,
          COALESCE(SUM(CASE WHEN pm.tipo='ENTRADA' AND pm.estatus='REGISTRADO' THEN pm.monto ELSE 0 END),0) entradas,
          COALESCE(SUM(CASE WHEN pm.tipo='SALIDA' AND pm.estatus='REGISTRADO' THEN pm.monto ELSE 0 END),0) salidas
          FROM departamento d
          LEFT JOIN presupuesto_departamento pd ON pd.departamento_id=d.departamento_id AND pd.ejercicio=? AND pd.estatus='ACTIVO'
          LEFT JOIN presupuesto_movimiento pm ON pm.departamento_id=d.departamento_id AND pm.ejercicio=?
          WHERE d.departamento_id=? GROUP BY d.departamento_id,pd.presupuesto_asignado";
    $st=$db->prepare($sql);$st->bind_param('iii',$year,$year,$departmentId);$st->execute();$r=$st->get_result()->fetch_assoc();$st->close();
    $a=(float)($r['asignado']??0);$e=(float)($r['entradas']??0);$s=(float)($r['salidas']??0);
    return ['asignado'=>$a,'entradas'=>$e,'salidas'=>$s,'disponible'=>$a+$e-$s];
}

function save_evidence_file(array $file,string $folio):?array{
    if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
    if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK)throw new RuntimeException('Error al cargar la evidencia');
    if(($file['size']??0)>10*1024*1024)throw new RuntimeException('La evidencia excede 10 MB');
    $allowed=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png'];
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if(!isset($allowed[$mime]))throw new RuntimeException('Formato no permitido. Usa PDF, JPG o PNG');
    $dir=project_root().'/uploads/presupuesto/'.date('Y').'/'.date('m');
    if(!is_dir($dir)&&!mkdir($dir,0775,true)&&!is_dir($dir))throw new RuntimeException('No se pudo crear la carpeta de evidencias');
    $safe=$folio.'-'.bin2hex(random_bytes(5)).'.'.$allowed[$mime];$dest=$dir.'/'.$safe;
    if(!move_uploaded_file($file['tmp_name'],$dest))throw new RuntimeException('No se pudo guardar la evidencia');
    return ['nombre_original'=>basename((string)$file['name']),'nombre_guardado'=>$safe,'ruta_relativa'=>str_replace(project_root().'/','',$dest),'mime_type'=>$mime,'size_bytes'=>(int)$file['size']];
}

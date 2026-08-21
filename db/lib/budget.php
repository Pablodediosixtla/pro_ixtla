<?php

declare(strict_types=1);

function money_value(mixed $value): float {
    if(is_string($value))$value=str_replace([',','$',' '],'',$value);
    return round((float)$value,2);
}

function current_year(): int { return (int)date('Y'); }

/**
 * Totalizadores financieros de un único departamento y ejercicio.
 * Se calculan por subconsultas independientes para evitar duplicaciones por
 * JOIN y para garantizar que cada KPI use exactamente la misma frontera.
 */
function department_financial_summary(mysqli $db, int $departmentId, int $year): array {
    $sql = "SELECT
        COALESCE((
            SELECT pd.presupuesto_asignado
            FROM presupuesto_departamento pd
            WHERE pd.departamento_id=? AND pd.ejercicio=? AND pd.estatus='ACTIVO'
            ORDER BY pd.presupuesto_departamento_id DESC
            LIMIT 1
        ),0) asignado,
        COALESCE((
            SELECT SUM(pm.monto)
            FROM presupuesto_movimiento pm
            WHERE pm.departamento_id=? AND pm.ejercicio=? AND pm.tipo='ENTRADA' AND pm.estatus='REGISTRADO'
        ),0) entradas,
        COALESCE((
            SELECT SUM(pm.monto)
            FROM presupuesto_movimiento pm
            WHERE pm.departamento_id=? AND pm.ejercicio=? AND pm.tipo='SALIDA' AND pm.estatus='REGISTRADO'
        ),0) salidas";
    $st = $db->prepare($sql);
    if (!$st) {
        throw new RuntimeException('No se pudieron preparar los totalizadores financieros');
    }
    $st->bind_param(
        'iiiiii',
        $departmentId, $year,
        $departmentId, $year,
        $departmentId, $year
    );
    if (!$st->execute()) {
        $message = $st->error ?: 'Error al calcular los totalizadores financieros';
        $st->close();
        throw new RuntimeException($message);
    }
    $row = $st->get_result()->fetch_assoc() ?: [];
    $st->close();

    $assigned = (float)($row['asignado'] ?? 0);
    $entries = (float)($row['entradas'] ?? 0);
    $outputs = (float)($row['salidas'] ?? 0);
    $available = $assigned + $entries - $outputs;

    return [
        'asignado' => $assigned,
        'entradas' => $entries,
        'salidas' => $outputs,
        'disponible' => $available,
        'ejercido_pct' => $assigned > 0 ? round(($outputs / $assigned) * 100, 1) : 0.0,
    ];
}

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
    $summary = department_financial_summary($db, $departmentId, $year);
    return [
        'asignado'=>$summary['asignado'],
        'entradas'=>$summary['entradas'],
        'salidas'=>$summary['salidas'],
        'disponible'=>$summary['disponible'],
    ];
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

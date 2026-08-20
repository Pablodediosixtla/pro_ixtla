<?php

declare(strict_types=1);

function money_value(mixed $value): float {
    if (is_string($value)) $value = str_replace([',', '$', ' '], '', $value);
    return round((float)$value, 2);
}

function current_year(): int {
    return (int)date('Y');
}

function next_budget_folio(mysqli $con, int $year): string {
    $st = $con->prepare("INSERT INTO presupuesto_folio_anual (ejercicio, ultimo_folio)
                        VALUES (?, 1)
                        ON DUPLICATE KEY UPDATE ultimo_folio = ultimo_folio + 1");
    $st->bind_param('i', $year);
    if (!$st->execute()) throw new RuntimeException('No se pudo generar el folio: ' . $st->error);
    $st->close();

    $st = $con->prepare("SELECT ultimo_folio FROM presupuesto_folio_anual WHERE ejercicio = ? FOR UPDATE");
    $st->bind_param('i', $year);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$row) throw new RuntimeException('No se pudo consultar el folio generado');
    return sprintf('FOL-%d-%06d', $year, (int)$row['ultimo_folio']);
}

function department_balance(mysqli $con, int $departmentId, int $year): array {
    $sql = "SELECT
                COALESCE(pd.presupuesto_asignado, 0) AS asignado,
                COALESCE(SUM(CASE WHEN pm.tipo='ENTRADA' AND pm.status='REGISTRADO' THEN pm.monto ELSE 0 END),0) AS entradas,
                COALESCE(SUM(CASE WHEN pm.tipo='SALIDA' AND pm.status='REGISTRADO' THEN pm.monto ELSE 0 END),0) AS salidas
            FROM departamento d
            LEFT JOIN presupuesto_departamento pd
              ON pd.departamento_id=d.id AND pd.ejercicio=? AND pd.status=1
            LEFT JOIN presupuesto_movimiento pm
              ON pm.departamento_id=d.id AND pm.ejercicio=?
            WHERE d.id=?
            GROUP BY d.id, pd.presupuesto_asignado";
    $st = $con->prepare($sql);
    $st->bind_param('iii', $year, $year, $departmentId);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    $asignado = (float)($row['asignado'] ?? 0);
    $entradas = (float)($row['entradas'] ?? 0);
    $salidas = (float)($row['salidas'] ?? 0);
    return [
        'asignado' => $asignado,
        'entradas' => $entradas,
        'salidas' => $salidas,
        'disponible' => $asignado + $entradas - $salidas,
    ];
}

function save_evidence_file(array $file, string $folio): ?array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Error al cargar la evidencia');
    }
    if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
        throw new RuntimeException('La evidencia excede 10 MB');
    }

    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Formato de evidencia no permitido');
    }

    $dir = project_root() . '/uploads/presupuesto/' . date('Y') . '/' . date('m');
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('No se pudo crear la carpeta de evidencias');
    }

    $safeName = $folio . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $destination = $dir . '/' . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('No se pudo guardar la evidencia');
    }

    $relative = str_replace(project_root() . '/', '', $destination);
    return [
        'nombre_original' => basename((string)$file['name']),
        'nombre_guardado' => $safeName,
        'ruta_relativa' => $relative,
        'mime_type' => $mime,
        'size_bytes' => (int)$file['size'],
    ];
}

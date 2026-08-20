<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');
$user = require_permission('PRESUPUESTO_VER', 'MOVIMIENTO_VER');

$departmentId = (int)($_GET['departamento_id'] ?? 0);
$subitemId = (int)($_GET['subitem_id'] ?? 0);
$year = max(2020, min(2100, (int)($_GET['year'] ?? date('Y'))));
if ($departmentId <= 0 || $subitemId < 0) {
    json_response(['ok' => false, 'error' => 'Parámetros inválidos'], 400);
}

$db = conectar();
if (!$db) {
    json_response(['ok' => false, 'error' => 'Sin conexión a base de datos'], 503);
}

$visibleDepartments = visible_department_ids($db, $user);
if (!in_array($departmentId, $visibleDepartments, true)) {
    $db->close();
    json_response(['ok' => false, 'error' => 'No tienes acceso a este departamento'], 403);
}

$st = $db->prepare("SELECT departamento_id,codigo,nombre,color_hex
                    FROM departamento WHERE departamento_id=? AND estatus='ACTIVO' LIMIT 1");
$st->bind_param('i', $departmentId);
$st->execute();
$department = $st->get_result()->fetch_assoc();
$st->close();
if (!$department) {
    $db->close();
    json_response(['ok' => false, 'error' => 'Departamento no encontrado'], 404);
}
$department['departamento_id'] = (int)$department['departamento_id'];

if ($subitemId === 0) {
    $category = [
        'subitem_id' => 0,
        'codigo' => 'SIN-CAT',
        'nombre' => 'Sin subcategoría',
        'descripcion' => 'Salidas registradas sin una subcategoría asignada.',
    ];
} else {
    $st = $db->prepare("SELECT subitem_id,codigo,nombre,descripcion
                        FROM presupuesto_subitem
                        WHERE subitem_id=? AND estatus='ACTIVO'
                          AND (departamento_id IS NULL OR departamento_id=?)
                        LIMIT 1");
    $st->bind_param('ii', $subitemId, $departmentId);
    $st->execute();
    $category = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$category) {
        $db->close();
        json_response(['ok' => false, 'error' => 'Subcategoría no encontrada para este departamento'], 404);
    }
    $category['subitem_id'] = (int)$category['subitem_id'];
}

$departmentOutputs = 0.0;
$categoryOutputs = 0.0;
$rows = [];
$st = $db->prepare("SELECT pm.*,
                           CONCAT_WS(' ',us.nombre,us.apellido_paterno,us.apellido_materno) solicitado_por,
                           CONCAT_WS(' ',uo.nombre,uo.apellido_paterno,uo.apellido_materno) otorgado_a,
                           CONCAT_WS(' ',ur.nombre,ur.apellido_paterno,ur.apellido_materno) registrado_por,
                           (SELECT COUNT(*) FROM movimiento_aclaracion a
                            WHERE a.movimiento_id=pm.movimiento_id AND a.estatus IN ('ABIERTA','EN_REVISION')) aclaraciones_abiertas
                    FROM presupuesto_movimiento pm
                    LEFT JOIN usuario us ON us.usuario_id=pm.solicitado_por_usuario_id
                    LEFT JOIN usuario uo ON uo.usuario_id=pm.otorgado_a_usuario_id
                    JOIN usuario ur ON ur.usuario_id=pm.registrado_por_usuario_id
                    WHERE pm.departamento_id=? AND pm.ejercicio=? AND pm.tipo='SALIDA' AND pm.estatus='REGISTRADO'
                    ORDER BY pm.fecha DESC,pm.movimiento_id DESC");
$st->bind_param('ii', $departmentId, $year);
$st->execute();
$rs = $st->get_result();
while ($movement = $rs->fetch_assoc()) {
    if (!movement_is_visible($db, $user, $movement)) {
        continue;
    }
    $amount = (float)$movement['monto'];
    $departmentOutputs += $amount;
    $movementSubitemId = (int)($movement['subitem_id'] ?? 0);
    if ($movementSubitemId !== $subitemId) {
        continue;
    }
    $categoryOutputs += $amount;
    $rows[] = [
        'movimiento_id' => (int)$movement['movimiento_id'],
        'folio' => (string)$movement['folio'],
        'fecha' => (string)$movement['fecha'],
        'monto' => $amount,
        'concepto' => (string)$movement['concepto'],
        'solicitado_por' => trim((string)($movement['solicitado_por'] ?? '')),
        'otorgado_a' => trim((string)($movement['otorgado_a'] ?? '')) ?: (string)($movement['beneficiario_nombre'] ?? ''),
        'registrado_por' => trim((string)$movement['registrado_por']),
        'metodo_pago' => (string)($movement['metodo_pago'] ?? ''),
        'referencia' => (string)($movement['referencia'] ?? ''),
        'aclaraciones_abiertas' => (int)$movement['aclaraciones_abiertas'],
    ];
}
$st->close();

$assigned = 0.0;
$st = $db->prepare("SELECT presupuesto_asignado FROM presupuesto_departamento
                    WHERE departamento_id=? AND ejercicio=? AND estatus='ACTIVO' LIMIT 1");
$st->bind_param('ii', $departmentId, $year);
$st->execute();
if ($budget = $st->get_result()->fetch_assoc()) {
    $assigned = (float)$budget['presupuesto_asignado'];
}
$st->close();

$data = [
    'department' => $department,
    'category' => $category,
    'year' => $year,
    'totals' => [
        'salidas' => $categoryOutputs,
        'registros' => count($rows),
        'participacion_pct' => $departmentOutputs > 0 ? round(($categoryOutputs / $departmentOutputs) * 100, 1) : 0.0,
        'presupuesto_pct' => $assigned > 0 ? round(($categoryOutputs / $assigned) * 100, 1) : 0.0,
        'ultima_salida' => $rows[0]['fecha'] ?? null,
    ],
    'movements' => $rows,
];

$db->close();
json_response(['ok' => true, 'data' => $data]);

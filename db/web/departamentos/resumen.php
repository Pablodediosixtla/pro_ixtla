<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');
$user = require_permission('PRESUPUESTO_VER');
if (!user_can_view_department_financials($user)) {
    json_response(['ok'=>false,'error'=>'Tu perfil no tiene acceso al resumen financiero del departamento'],403);
}

$departmentId = (int)($_GET['departamento_id'] ?? 0);
$year = max(2020, min(2100, (int)($_GET['year'] ?? date('Y'))));
if ($departmentId <= 0) {
    json_response(['ok' => false, 'error' => 'Departamento inválido'], 400);
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

$st = $db->prepare("SELECT departamento_id,codigo,nombre,descripcion,color_hex,icono,es_tesoreria
                    FROM departamento
                    WHERE departamento_id=? AND estatus='ACTIVO'
                    LIMIT 1");
$st->bind_param('i', $departmentId);
$st->execute();
$department = $st->get_result()->fetch_assoc();
$st->close();
if (!$department) {
    $db->close();
    json_response(['ok' => false, 'error' => 'Departamento no encontrado'], 404);
}
$department['departamento_id'] = (int)$department['departamento_id'];
$department['es_tesoreria'] = (int)$department['es_tesoreria'];

$assigned = 0.0;
$observations = '';
$st = $db->prepare("SELECT presupuesto_asignado,COALESCE(observaciones,'') observaciones
                    FROM presupuesto_departamento
                    WHERE departamento_id=? AND ejercicio=? AND estatus='ACTIVO'
                    LIMIT 1");
$st->bind_param('ii', $departmentId, $year);
$st->execute();
if ($budget = $st->get_result()->fetch_assoc()) {
    $assigned = (float)$budget['presupuesto_asignado'];
    $observations = (string)$budget['observaciones'];
}
$st->close();

$subitems = [];
$subitemOrder = [];
$st = $db->prepare("SELECT subitem_id,departamento_id,codigo,nombre,descripcion
                    FROM presupuesto_subitem
                    WHERE estatus='ACTIVO' AND tipo='SALIDA' AND (departamento_id IS NULL OR departamento_id=?)
                    ORDER BY CASE WHEN departamento_id=? THEN 0 ELSE 1 END,nombre");
$st->bind_param('ii', $departmentId, $departmentId);
$st->execute();
$rs = $st->get_result();
while ($row = $rs->fetch_assoc()) {
    $id = (int)$row['subitem_id'];
    $subitemOrder[] = $id;
    $subitems[$id] = [
        'subitem_id' => $id,
        'codigo' => (string)$row['codigo'],
        'nombre' => (string)$row['nombre'],
        'descripcion' => (string)($row['descripcion'] ?? ''),
        'scope' => $row['departamento_id'] !== null ? 'DEPARTAMENTO' : 'GLOBAL',
        'salidas' => 0.0,
        'registros' => 0,
        'ultima_salida' => null,
    ];
}
$st->close();

$entries = 0.0;
$outputs = 0.0;
$monthly = [];
for ($month = 1; $month <= 12; $month++) {
    $monthly[$month] = ['entrada' => 0.0, 'salida' => 0.0];
}
$recentOutputs = [];
$uncategorized = [
    'subitem_id' => 0,
    'codigo' => 'SIN-CAT',
    'nombre' => 'Sin subcategoría',
    'descripcion' => 'Salidas registradas sin una subcategoría asignada.',
    'scope' => 'SISTEMA',
    'salidas' => 0.0,
    'registros' => 0,
    'ultima_salida' => null,
];

$st = $db->prepare("SELECT pm.*,MONTH(pm.fecha) mes,
                           s.nombre subitem,
                           CONCAT_WS(' ',us.nombre,us.apellido_paterno,us.apellido_materno) solicitado_por,
                           CONCAT_WS(' ',uo.nombre,uo.apellido_paterno,uo.apellido_materno) otorgado_a,
                           CONCAT_WS(' ',ur.nombre,ur.apellido_paterno,ur.apellido_materno) registrado_por
                    FROM presupuesto_movimiento pm
                    LEFT JOIN presupuesto_subitem s ON s.subitem_id=pm.subitem_id
                    LEFT JOIN usuario us ON us.usuario_id=pm.solicitado_por_usuario_id
                    LEFT JOIN usuario uo ON uo.usuario_id=pm.otorgado_a_usuario_id
                    JOIN usuario ur ON ur.usuario_id=pm.registrado_por_usuario_id
                    WHERE pm.departamento_id=? AND pm.ejercicio=? AND pm.estatus='REGISTRADO'
                    ORDER BY pm.fecha DESC,pm.movimiento_id DESC");
$st->bind_param('ii', $departmentId, $year);
$st->execute();
$rs = $st->get_result();
while ($movement = $rs->fetch_assoc()) {
    if (!movement_is_visible($db, $user, $movement)) {
        continue;
    }
    $amount = (float)$movement['monto'];
    $month = (int)$movement['mes'];
    if ($movement['tipo'] === 'ENTRADA') {
        $entries += $amount;
        $monthly[$month]['entrada'] += $amount;
        continue;
    }

    $outputs += $amount;
    $monthly[$month]['salida'] += $amount;
    $sid = (int)($movement['subitem_id'] ?? 0);
    if ($sid > 0 && isset($subitems[$sid])) {
        $subitems[$sid]['salidas'] += $amount;
        $subitems[$sid]['registros']++;
        if ($subitems[$sid]['ultima_salida'] === null || $movement['fecha'] > $subitems[$sid]['ultima_salida']) {
            $subitems[$sid]['ultima_salida'] = $movement['fecha'];
        }
    } else {
        $uncategorized['salidas'] += $amount;
        $uncategorized['registros']++;
        if ($uncategorized['ultima_salida'] === null || $movement['fecha'] > $uncategorized['ultima_salida']) {
            $uncategorized['ultima_salida'] = $movement['fecha'];
        }
    }

    if (count($recentOutputs) < 6) {
        $recentOutputs[] = [
            'movimiento_id' => (int)$movement['movimiento_id'],
            'folio' => (string)$movement['folio'],
            'fecha' => (string)$movement['fecha'],
            'monto' => $amount,
            'concepto' => (string)$movement['concepto'],
            'subitem' => $movement['subitem'] ?: 'Sin subcategoría',
            'otorgado_a' => trim((string)($movement['otorgado_a'] ?? '')) ?: (string)($movement['beneficiario_nombre'] ?? ''),
            'registrado_por' => trim((string)$movement['registrado_por']),
        ];
    }
}
$st->close();

$categoryList = [];
foreach ($subitemOrder as $sid) {
    $item = $subitems[$sid];
    $item['porcentaje_gasto'] = $outputs > 0 ? round(($item['salidas'] / $outputs) * 100, 1) : 0.0;
    $item['porcentaje_presupuesto'] = $assigned > 0 ? round(($item['salidas'] / $assigned) * 100, 1) : 0.0;
    $categoryList[] = $item;
}
if ($uncategorized['registros'] > 0) {
    $uncategorized['porcentaje_gasto'] = $outputs > 0 ? round(($uncategorized['salidas'] / $outputs) * 100, 1) : 0.0;
    $uncategorized['porcentaje_presupuesto'] = $assigned > 0 ? round(($uncategorized['salidas'] / $assigned) * 100, 1) : 0.0;
    $categoryList[] = $uncategorized;
}

$available = $assigned + $entries - $outputs;
$data = [
    'department' => $department,
    'year' => $year,
    'observations' => $observations,
    'totals' => [
        'asignado' => $assigned,
        'entradas' => $entries,
        'salidas' => $outputs,
        'disponible' => $available,
        'ejercido_pct' => $assigned > 0 ? round(($outputs / $assigned) * 100, 1) : 0.0,
    ],
    'monthly' => $monthly,
    'categories' => $categoryList,
    'recent_outputs' => $recentOutputs,
];

$db->close();
json_response(['ok' => true, 'data' => $data]);

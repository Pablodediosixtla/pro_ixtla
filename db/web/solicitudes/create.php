<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';

require_method('POST');
require_csrf();
$user = require_permission('SOLICITUD_CREAR');
$in = json_input();

$dep = (int)($in['departamento_id'] ?? 0);
$sub = ($in['subitem_id'] ?? '') !== '' ? (int)$in['subitem_id'] : null;
$amount = money_value($in['monto'] ?? 0);
$concept = trim((string)($in['concepto'] ?? ''));
$benefUser = ($in['otorgado_a_usuario_id'] ?? '') !== '' ? (int)$in['otorgado_a_usuario_id'] : null;
$benefName = trim((string)($in['beneficiario_nombre'] ?? ''));
$area = trim((string)($in['area_solicitante'] ?? ''));
$year = (int)($in['ejercicio'] ?? date('Y'));

if ($dep <= 0 || $amount <= 0 || $concept === '') {
    json_response(['ok' => false, 'error' => 'Departamento, monto y concepto son obligatorios'], 400);
}

if ((function_exists('mb_strlen') ? mb_strlen($concept, 'UTF-8') : strlen($concept)) > 700) {
    json_response(['ok' => false, 'error' => 'El concepto / uso no puede exceder 700 caracteres'], 400);
}

$db = conectar();
if (!$db) {
    json_response(['ok' => false, 'error' => 'Sin conexión a base de datos'], 503);
}

$allowed = visible_department_ids($db, $user);
if (!user_is_global($user) && !in_array($dep, $allowed, true)) {
    $db->close();
    json_response(['ok' => false, 'error' => 'No puedes crear solicitudes para ese departamento'], 403);
}

if ($sub !== null) {
    $st = $db->prepare('SELECT tipo, departamento_id, estatus FROM presupuesto_subitem WHERE subitem_id=? LIMIT 1');
    if (!$st) {
        $db->close();
        json_response(['ok' => false, 'error' => 'No fue posible validar el sub-item'], 500);
    }
    $st->bind_param('i', $sub);
    if (!$st->execute()) {
        $st->close();
        $db->close();
        json_response(['ok' => false, 'error' => 'No fue posible validar el sub-item'], 500);
    }
    $subitem = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$subitem || $subitem['estatus'] !== 'ACTIVO' || $subitem['tipo'] !== 'SALIDA') {
        $db->close();
        json_response(['ok' => false, 'error' => 'Las solicitudes solo pueden usar sub-items activos de salida'], 409);
    }
    if ($subitem['departamento_id'] !== null && (int)$subitem['departamento_id'] !== $dep) {
        $db->close();
        json_response(['ok' => false, 'error' => 'El sub-item seleccionado pertenece a otro departamento'], 409);
    }
}

$balance = department_balance($db, $dep, $year);
if ($amount > $balance['disponible']) {
    $db->close();
    json_response(['ok' => false, 'error' => 'El monto solicitado excede el presupuesto disponible'], 409);
}

$folio = next_folio($db, 'SOLICITUD', $year);
$uid = (int)$user['user_id'];
$date = date('Y-m-d');

/*
 * Orden y tipos del INSERT:
 * folio(s), ejercicio(i), departamento(i), subitem(i), fecha(s), monto(d),
 * concepto(s), solicitado_por(i), otorgado_a(i), beneficiario(s), area(s).
 *
 * En V4.12 el séptimo tipo estaba declarado como "i", por lo que mysqli
 * convertía cualquier texto de concepto a 0 antes de enviarlo a MySQL.
 */
$st = $db->prepare(
    "INSERT INTO presupuesto_solicitud(
        folio, ejercicio, departamento_id, subitem_id, fecha_solicitud,
        monto_solicitado, concepto, solicitado_por_usuario_id,
        otorgado_a_usuario_id, beneficiario_nombre, area_solicitante, estatus
    ) VALUES(?,?,?,?,?,?,?,?,?,?,?,'PENDIENTE')"
);
if (!$st) {
    $db->close();
    json_response(['ok' => false, 'error' => 'No fue posible preparar el registro de la solicitud'], 500);
}

$st->bind_param(
    'siiisdsiiss',
    $folio,
    $year,
    $dep,
    $sub,
    $date,
    $amount,
    $concept,
    $uid,
    $benefUser,
    $benefName,
    $area
);

if (!$st->execute()) {
    $error = $st->error;
    $st->close();
    $db->close();
    error_log('SOLICITUD_CREATE insert error: ' . $error);
    json_response(['ok' => false, 'error' => 'No fue posible guardar la solicitud'], 500);
}

$id = (int)$st->insert_id;
$st->close();

// Verificación de lectura inmediata: evita responder OK si el texto no persistió.
$verify = $db->prepare('SELECT concepto FROM presupuesto_solicitud WHERE solicitud_id=? LIMIT 1');
if (!$verify) {
    $db->close();
    json_response(['ok' => false, 'error' => 'La solicitud se guardó, pero no fue posible verificarla'], 500);
}
$verify->bind_param('i', $id);
if (!$verify->execute()) {
    $verify->close();
    $db->close();
    json_response(['ok' => false, 'error' => 'La solicitud se guardó, pero no fue posible verificarla'], 500);
}
$saved = $verify->get_result()->fetch_assoc();
$verify->close();

if (!$saved || (string)$saved['concepto'] !== $concept) {
    error_log(sprintf(
        'SOLICITUD_CREATE concept mismatch solicitud_id=%d expected=%s saved=%s',
        $id,
        $concept,
        (string)($saved['concepto'] ?? 'NULL')
    ));
    $db->close();
    json_response(['ok' => false, 'error' => 'La solicitud se registró con una inconsistencia en Concepto / uso'], 500);
}

audit_log($db, $uid, 'SOLICITUD_CREAR', 'SOLICITUD', $id, 'Solicitud ' . $folio, null, $in);
$db->close();

json_response([
    'ok' => true,
    'data' => [
        'solicitud_id' => $id,
        'folio' => $folio,
        'concepto' => $concept,
    ],
]);

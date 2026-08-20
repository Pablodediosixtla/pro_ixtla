<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('POST');
start_app_session();

$in = json_input();
$username = trim((string)($in['username'] ?? ''));
$password = (string)($in['password'] ?? '');
if ($username === '' || $password === '') {
    json_response(['ok' => false, 'error' => 'Captura usuario y contraseña'], 400);
}

$con = conectar();
if (!$con) json_response(['ok' => false, 'error' => 'No fue posible conectar a la base de datos'], 500);

$sql = "SELECT
          c.id AS cuenta_id, c.empleado_id, c.username, c.password_hash,
          c.debe_cambiar_pw, c.intentos_fallidos, c.bloqueado_hasta,
          c.status AS status_cuenta,
          e.id AS emp_id, e.nombre, e.apellidos, e.email, e.telefono,
          e.puesto, e.departamento_id, e.status AS status_empleado
        FROM empleado_cuenta c
        JOIN empleado e ON e.id = c.empleado_id
        WHERE c.username = ?
        LIMIT 1";
$st = $con->prepare($sql);
$st->bind_param('s', $username);
$st->execute();
$acc = $st->get_result()->fetch_assoc();
$st->close();

if (!$acc || (int)$acc['status_cuenta'] !== 1 || (int)$acc['status_empleado'] !== 1) {
    $con->close();
    json_response(['ok' => false, 'error' => 'Usuario o contraseña inválidos'], 401);
}

if (!empty($acc['bloqueado_hasta']) && strtotime($acc['bloqueado_hasta']) > time()) {
    $con->close();
    json_response(['ok' => false, 'error' => 'Cuenta temporalmente bloqueada'], 423);
}

if (!password_verify($password, (string)$acc['password_hash'])) {
    $fallidos = (int)$acc['intentos_fallidos'] + 1;
    if ($fallidos >= 5) {
        $st = $con->prepare("UPDATE empleado_cuenta SET intentos_fallidos=0, bloqueado_hasta=DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id=?");
        $cid = (int)$acc['cuenta_id'];
        $st->bind_param('i', $cid);
        $st->execute();
        $st->close();
        $con->close();
        json_response(['ok' => false, 'error' => 'Cuenta bloqueada por múltiples intentos fallidos'], 423);
    }
    $st = $con->prepare("UPDATE empleado_cuenta SET intentos_fallidos=? WHERE id=?");
    $cid = (int)$acc['cuenta_id'];
    $st->bind_param('ii', $fallidos, $cid);
    $st->execute();
    $st->close();
    $con->close();
    json_response(['ok' => false, 'error' => 'Usuario o contraseña inválidos'], 401);
}

$cid = (int)$acc['cuenta_id'];
$st = $con->prepare("UPDATE empleado_cuenta SET intentos_fallidos=0, bloqueado_hasta=NULL, ultima_sesion=NOW() WHERE id=?");
$st->bind_param('i', $cid);
$st->execute();
$st->close();

$roles = [];
$st = $con->prepare("SELECT r.id, r.codigo, r.nombre FROM empleado_rol er JOIN rol r ON r.id=er.rol_id WHERE er.empleado_cuenta_id=? ORDER BY r.codigo");
$st->bind_param('i', $cid);
$st->execute();
$rs = $st->get_result();
while ($r = $rs->fetch_assoc()) {
    $r['id'] = (int)$r['id'];
    $roles[] = $r;
}
$st->close();

$permissions = budget_permissions($con, $cid, $acc['username'], $roles);
$con->close();

if (!$permissions) {
    json_response(['ok' => false, 'error' => 'Tu cuenta no tiene permiso para el módulo de Presupuesto'], 403);
}

session_regenerate_id(true);
$_SESSION['csrf'] = bin2hex(random_bytes(32));
$_SESSION['user'] = [
    'account_id' => $cid,
    'employee_id' => (int)$acc['emp_id'],
    'username' => $acc['username'],
    'name' => trim($acc['nombre'] . ' ' . $acc['apellidos']),
    'email' => $acc['email'],
    'position' => $acc['puesto'],
    'department_id' => $acc['departamento_id'] !== null ? (int)$acc['departamento_id'] : null,
    'legacy_roles' => $roles,
    'budget_permissions' => $permissions,
];

json_response(['ok' => true, 'data' => ['user' => $_SESSION['user'], 'csrf' => $_SESSION['csrf']]]);

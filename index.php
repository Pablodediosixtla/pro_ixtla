<?php
require_once __DIR__.'/db/lib/bootstrap.php';
start_app_session();

if (!isset($_SESSION['user'])) {
    $pageTitle='Iniciar sesión';
    include __DIR__.'/views/login.php';
    exit;
}

$user = $_SESSION['user'];
$allowed = [
    'dashboard','departamentos','usuarios','roles','presupuestos','pagos','subitems',
    'solicitudes','movimientos','nuevo-movimiento','aclaraciones','bitacora',
    'departamento-resumen','subcategoria-detalle'
];
$view = $_GET['view'] ?? 'dashboard';
if (!in_array($view, $allowed, true)) {
    $view='dashboard';
}

$viewPermissions = [
    'departamentos'     => ['DEPARTAMENTOS_GESTIONAR'],
    'usuarios'          => ['USUARIOS_GESTIONAR'],
    'roles'             => ['ROLES_GESTIONAR'],
    'presupuestos'      => ['PRESUPUESTO_VER'],
    'subitems'          => ['SUBITEMS_GESTIONAR'],
    'solicitudes'       => ['SOLICITUD_CREAR','SOLICITUD_APROBAR'],
    'movimientos'       => ['MOVIMIENTO_VER'],
    'nuevo-movimiento'  => ['MOVIMIENTO_ENTRADA_CREAR','MOVIMIENTO_SALIDA_CREAR'],
    'aclaraciones'      => ['ACLARACION_CREAR','ACLARACION_GESTIONAR'],
    'bitacora'          => ['BITACORA_VER'],
    'departamento-resumen' => ['PRESUPUESTO_VER'],
    'subcategoria-detalle' => ['PRESUPUESTO_VER','MOVIMIENTO_VER'],
];

if (isset($viewPermissions[$view]) && !user_has_any_permission($user, $viewPermissions[$view])) {
    $view='dashboard';
}

if ($view === 'pagos' && !user_has_any_role($user, ['ADMIN','PRESIDENTE','TESORERIA'])) {
    $view='dashboard';
}

// Los perfiles de alcance PROPIO no pueden abrir vistas con información
// presupuestal agregada del departamento aunque el rol conserve un permiso
// histórico PRESUPUESTO_VER en la base.
if (user_is_own_scope_only($user) && in_array($view, ['presupuestos','departamento-resumen','subcategoria-detalle'], true)) {
    $view='dashboard';
}

include __DIR__.'/views/layout.php';

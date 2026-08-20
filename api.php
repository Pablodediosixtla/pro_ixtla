<?php

declare(strict_types=1);

/**
 * API Gateway de Presupuesto Ixtlahuacán.
 *
 * Expone un único endpoint raíz (/api.php) y mantiene los controladores
 * dentro de db/web. Esto evita depender de que Azure publique/routee
 * directamente carpetas internas como /db/web/*.
 */

$route = trim((string)($_GET['route'] ?? ''), '/');

$routes = [
    'auth/login' => 'db/web/auth/login.php',
    'auth/logout' => 'db/web/auth/logout.php',
    'auth/me' => 'db/web/auth/me.php',

    'dashboard/resumen' => 'db/web/dashboard/resumen.php',

    'departamentos/list' => 'db/web/departamentos/list.php',
    'departamentos/save' => 'db/web/departamentos/save.php',

    'usuarios/list' => 'db/web/usuarios/list.php',
    'usuarios/options' => 'db/web/usuarios/options.php',
    'usuarios/save' => 'db/web/usuarios/save.php',
    'usuarios/change_password' => 'db/web/usuarios/change_password.php',

    'roles/list' => 'db/web/roles/list.php',
    'roles/save' => 'db/web/roles/save.php',

    'presupuestos/list' => 'db/web/presupuestos/list.php',
    'presupuestos/save' => 'db/web/presupuestos/save.php',

    'subitems/list' => 'db/web/subitems/list.php',
    'subitems/save' => 'db/web/subitems/save.php',

    'solicitudes/list' => 'db/web/solicitudes/list.php',
    'solicitudes/create' => 'db/web/solicitudes/create.php',
    'solicitudes/status' => 'db/web/solicitudes/status.php',

    'movimientos/list' => 'db/web/movimientos/list.php',
    'movimientos/get' => 'db/web/movimientos/get.php',
    'movimientos/create' => 'db/web/movimientos/create.php',
    'movimientos/cancel' => 'db/web/movimientos/cancel.php',
    'movimientos/file' => 'db/web/movimientos/file.php',

    'aclaraciones/list' => 'db/web/aclaraciones/list.php',
    'aclaraciones/get' => 'db/web/aclaraciones/get.php',
    'aclaraciones/create' => 'db/web/aclaraciones/create.php',
    'aclaraciones/message' => 'db/web/aclaraciones/message.php',
    'aclaraciones/status' => 'db/web/aclaraciones/status.php',

    'bitacora/list' => 'db/web/bitacora/list.php',

    'system/health' => 'db/web/system/health.php',
    'system/schema-check' => 'db/web/system/schema-check.php',
];

if (!isset($routes[$route])) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'error' => 'Ruta de API no encontrada',
        'route' => $route,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$file = __DIR__ . '/' . $routes[$route];
if (!is_file($file)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'error' => 'Controlador de API no disponible en el despliegue',
        'route' => $route,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

require $file;

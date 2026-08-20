<?php
require_once __DIR__ . '/db/lib/bootstrap.php';
start_app_session();

if (!isset($_SESSION['user'])) {
    $pageTitle = 'Iniciar sesión';
    include __DIR__ . '/views/login.php';
    exit;
}

$allowedViews = [
    'dashboard',
    'departamentos',
    'departamento-detalle',
    'subitems',
    'movimientos',
    'nuevo-movimiento',
    'bitacora'
];

$view = $_GET['view'] ?? 'dashboard';
if (!in_array($view, $allowedViews, true)) {
    $view = 'dashboard';
}

include __DIR__ . '/views/layout.php';

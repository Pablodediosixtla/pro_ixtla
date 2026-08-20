<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('POST');
start_app_session();
require_csrf();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
}
session_destroy();
json_response(['ok' => true]);

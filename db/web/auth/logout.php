<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('POST');
start_app_session();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'] ?? '', (bool)$p['secure'], (bool)$p['httponly']);
}
session_destroy();
json_response(['ok'=>true]);

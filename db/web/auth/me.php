<?php
require_once dirname(__DIR__, 2) . '/lib/bootstrap.php';
require_method('GET');
$user = require_login();
start_app_session();
json_response(['ok'=>true,'data'=>[
    'user'=>$user,
    'csrf'=>$_SESSION['csrf'] ?? '',
    'auth_mode'=>app_auth_mode(),
    'data_mode'=>app_data_mode(),
    'review_mode'=>app_is_review_mode(),
]]);

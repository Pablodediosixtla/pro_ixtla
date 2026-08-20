<?php

declare(strict_types=1);

function project_root(): string {
    return dirname(__DIR__, 2);
}

function load_env_file(): void {
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;

    $file = project_root() . '/.env';
    if (!is_file($file)) return;

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '') continue;
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function env_value(string $key, ?string $default = null): ?string {
    load_env_file();
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function app_timezone(): string {
    return env_value('APP_TIMEZONE', 'America/Mexico_City') ?: 'America/Mexico_City';
}

date_default_timezone_set(app_timezone());

function start_app_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name(env_value('SESSION_NAME', 'PROIXTLA_SESSION') ?: 'PROIXTLA_SESSION');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
}

require_once project_root() . '/db/conn/conn_db.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/budget.php';

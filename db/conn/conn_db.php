<?php

declare(strict_types=1);

function conectar(): ?mysqli {
    load_env_file();

    $host = trim((string)env_value('DB_HOST', ''));
    $user = trim((string)env_value('DB_USER', ''));
    $pass = (string)env_value('DB_PASS', '');
    $name = trim((string)env_value('DB_NAME', ''));
    $port = (int)(env_value('DB_PORT', '3306') ?: '3306');

    if ($host === '' || $user === '' || $name === '') {
        return null;
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $con = mysqli_init();
    if (!$con) return null;

    $timeout = max(2, min(30, (int)(env_value('DB_CONNECT_TIMEOUT', '8') ?: '8')));
    @mysqli_options($con, MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);

    $useSsl = env_bool('DB_SSL', true);
    if ($useSsl) {
        $ca = (string)env_value('DB_SSL_CA', 'db/conn/DigiCertGlobalRootG2.crt.pem');
        if ($ca !== '') {
            if (!str_starts_with($ca, '/')) {
                $ca = project_root() . '/' . ltrim($ca, '/');
            }
            if (is_file($ca)) {
                mysqli_ssl_set($con, null, null, $ca, null, null);
            }
        }
    }

    $flags = $useSsl ? MYSQLI_CLIENT_SSL : 0;
    if (!@mysqli_real_connect($con, $host, $user, $pass, $name, $port, null, $flags)) {
        return null;
    }

    $con->set_charset('utf8mb4');
    @$con->query("SET time_zone='-06:00'");
    return $con;
}

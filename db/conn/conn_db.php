<?php

declare(strict_types=1);

function conectar(): ?mysqli {
    load_env_file();

    $host = env_value('DB_HOST');
    $user = env_value('DB_USER');
    $pass = env_value('DB_PASS');
    $name = env_value('DB_NAME');
    $port = (int)(env_value('DB_PORT', '3306') ?: '3306');

    if (!$host || !$user || !$name) {
        return null;
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $con = mysqli_init();
    $useSsl = strtolower((string)env_value('DB_SSL', 'true')) !== 'false';

    if ($useSsl) {
        $ca = (string)env_value('DB_SSL_CA', 'db/conn/DigiCertGlobalRootG2.crt.pem');
        if (!str_starts_with($ca, '/')) {
            $ca = project_root() . '/' . ltrim($ca, '/');
        }
        if (is_file($ca)) {
            mysqli_ssl_set($con, null, null, $ca, null, null);
        }
    }

    $flags = $useSsl ? MYSQLI_CLIENT_SSL : 0;
    if (!@mysqli_real_connect($con, $host, $user, $pass ?: '', $name, $port, null, $flags)) {
        return null;
    }

    $con->set_charset('utf8mb4');
    $con->query("SET time_zone='-06:00'");
    return $con;
}

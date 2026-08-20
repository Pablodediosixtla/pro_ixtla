<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo puede ejecutarse desde terminal.\n");
    exit(1);
}

require_once dirname(__DIR__) . '/db/lib/bootstrap.php';

$users = [
    'admin.demo',
    'presidente.demo',
    'tesoreria.demo',
    'cultura.director',
    'cultura.supervisor',
    'cultura.auxiliar',
    'servicios.director',
];

function read_hidden(string $prompt): string
{
    fwrite(STDOUT, $prompt);
    $isTty = function_exists('posix_isatty') ? @posix_isatty(STDIN) : true;
    if ($isTty && stripos(PHP_OS_FAMILY, 'Windows') === false) {
        @system('stty -echo');
        $value = trim((string)fgets(STDIN));
        @system('stty echo');
        fwrite(STDOUT, PHP_EOL);
        return $value;
    }
    return trim((string)fgets(STDIN));
}

$password = read_hidden('Nueva contraseña para las cuentas demo: ');
$confirm  = read_hidden('Confirma la contraseña: ');

if ($password !== $confirm) {
    fwrite(STDERR, "Las contraseñas no coinciden.\n");
    exit(1);
}
if (strlen($password) < 12) {
    fwrite(STDERR, "Usa una contraseña de al menos 12 caracteres.\n");
    exit(1);
}

$db = conectar();
if (!$db) {
    fwrite(STDERR, "No fue posible conectar a la base configurada en .env / variables de entorno.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare(
    "UPDATE usuario
     SET password_hash=?,
         requiere_cambio_password=0,
         intentos_fallidos=0,
         bloqueado_hasta=NULL,
         estatus='ACTIVO'
     WHERE username=?"
);
if (!$stmt) {
    fwrite(STDERR, "No fue posible preparar la actualización.\n");
    $db->close();
    exit(1);
}

$updated = 0;
foreach ($users as $username) {
    $stmt->bind_param('ss', $hash, $username);
    if (!$stmt->execute()) {
        fwrite(STDERR, "No se pudo actualizar {$username}.\n");
        continue;
    }
    if ($stmt->affected_rows >= 0) {
        fwrite(STDOUT, "OK  {$username}\n");
        $updated++;
    }
}
$stmt->close();
$db->close();

fwrite(STDOUT, "\nProceso terminado. Cuentas procesadas: {$updated}.\n");
fwrite(STDOUT, "La contraseña no fue guardada en archivos ni en Git.\n");

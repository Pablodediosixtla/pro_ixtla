USE `ixtla01_dep02`;

-- Este archivo NO contiene contraseñas ni hashes.
-- Las credenciales demo se inicializan fuera de Git con:
--   php scripts/set_demo_passwords.php
--
-- Aquí únicamente se limpian bloqueos para facilitar pruebas controladas.
UPDATE usuario
SET intentos_fallidos=0,
    bloqueado_hasta=NULL,
    estatus='ACTIVO'
WHERE username IN (
 'admin.demo','presidente.demo','tesoreria.demo','cultura.director',
 'cultura.supervisor','cultura.auxiliar','servicios.director'
);

SELECT username, estatus, requiere_cambio_password, intentos_fallidos, bloqueado_hasta
FROM usuario
WHERE username LIKE '%.demo'
   OR username IN ('cultura.director','cultura.supervisor','cultura.auxiliar','servicios.director')
ORDER BY username;

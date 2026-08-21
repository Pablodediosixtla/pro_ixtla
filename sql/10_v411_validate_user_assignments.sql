-- V4.11.0 - Validación de asignaciones de usuario (solo lectura)
USE `ixtla01_dep02`;

-- Cada usuario activo debe tener exactamente una asignación principal activa.
SELECT
    u.usuario_id,
    u.username,
    u.nombre,
    SUM(CASE WHEN ud.estatus='ACTIVO' THEN 1 ELSE 0 END) AS asignaciones_activas,
    SUM(CASE WHEN ud.estatus='ACTIVO' AND ud.es_principal=1 THEN 1 ELSE 0 END) AS principales_activas
FROM usuario u
LEFT JOIN usuario_departamento ud ON ud.usuario_id=u.usuario_id
WHERE u.estatus <> 'INACTIVO'
GROUP BY u.usuario_id,u.username,u.nombre
HAVING asignaciones_activas <> 1 OR principales_activas <> 1
ORDER BY u.username;

-- Detalle de la asignación activa que utiliza la aplicación.
SELECT
    u.usuario_id,
    u.username,
    d.nombre AS departamento,
    r.codigo AS rol_codigo,
    r.nombre AS rol,
    r.alcance,
    CONCAT_WS(' ',j.nombre,j.apellido_paterno,j.apellido_materno) AS reporta_a,
    ud.es_principal,
    ud.estatus
FROM usuario u
JOIN usuario_departamento ud
  ON ud.usuario_id=u.usuario_id
 AND ud.estatus='ACTIVO'
LEFT JOIN departamento d ON d.departamento_id=ud.departamento_id
JOIN rol r ON r.rol_id=ud.rol_id
LEFT JOIN usuario j ON j.usuario_id=ud.jefe_usuario_id
ORDER BY u.username,ud.es_principal DESC,ud.usuario_departamento_id DESC;

-- V4.13 - Diagnóstico de alcance de Director/Supervisor (solo lectura)
USE ixtla01_dep02;

SELECT
    u.usuario_id,
    u.username,
    CONCAT_WS(' ',u.nombre,u.apellido_paterno,u.apellido_materno) usuario,
    ud.es_principal,
    ud.estatus asignacion_estatus,
    r.codigo rol,
    r.alcance,
    d.codigo departamento_codigo,
    d.nombre departamento,
    ud.jefe_usuario_id
FROM usuario u
JOIN usuario_departamento ud ON ud.usuario_id=u.usuario_id
JOIN rol r ON r.rol_id=ud.rol_id
LEFT JOIN departamento d ON d.departamento_id=ud.departamento_id
WHERE u.estatus='ACTIVO'
  AND r.codigo IN ('DIRECTOR','SUPERVISOR')
ORDER BY u.username,ud.es_principal DESC,ud.usuario_departamento_id;

-- Debe existir como máximo una asignación ACTIVA PRINCIPAL por usuario.
SELECT
    u.username,
    SUM(ud.estatus='ACTIVO') asignaciones_activas,
    SUM(ud.estatus='ACTIVO' AND ud.es_principal=1) principales_activas
FROM usuario u
JOIN usuario_departamento ud ON ud.usuario_id=u.usuario_id
GROUP BY u.usuario_id,u.username
HAVING asignaciones_activas<>1 OR principales_activas<>1;

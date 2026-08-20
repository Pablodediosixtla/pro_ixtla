USE `ixtla01_dep02`;

-- Validación rápida después de instalar el modelo.
SELECT DATABASE() AS schema_activo,
       @@character_set_database AS character_set_database,
       @@collation_database AS collation_database;

SELECT TABLE_NAME, TABLE_TYPE
FROM information_schema.TABLES
WHERE TABLE_SCHEMA='ixtla01_dep02'
ORDER BY TABLE_TYPE, TABLE_NAME;

SELECT codigo,nombre,alcance,estatus
FROM rol
ORDER BY rol_id;

SELECT u.username,
       CONCAT_WS(' ',u.nombre,u.apellido_paterno,u.apellido_materno) AS nombre,
       r.codigo AS rol,
       r.alcance,
       d.nombre AS departamento,
       CONCAT_WS(' ',j.nombre,j.apellido_paterno,j.apellido_materno) AS reporta_a,
       u.estatus
FROM usuario u
LEFT JOIN usuario_departamento ud ON ud.usuario_id=u.usuario_id AND ud.estatus='ACTIVO'
LEFT JOIN rol r ON r.rol_id=ud.rol_id
LEFT JOIN departamento d ON d.departamento_id=ud.departamento_id
LEFT JOIN usuario j ON j.usuario_id=ud.jefe_usuario_id
ORDER BY u.username;

SELECT *
FROM vw_presupuesto_departamento_resumen
WHERE ejercicio=2026
ORDER BY departamento;

-- V4.8.0 | Separación de sub-items de ENTRADA y SALIDA
-- Ejecutar UNA SOLA VEZ sobre la base existente ixtla01_dep02 antes de usar V4.8.
USE `ixtla01_dep02`;

ALTER TABLE presupuesto_subitem
  ADD COLUMN tipo ENUM('ENTRADA','SALIDA') NOT NULL DEFAULT 'SALIDA' AFTER departamento_scope;

-- Todo el catálogo existente correspondía históricamente a gasto/salida.
UPDATE presupuesto_subitem SET tipo='SALIDA';

ALTER TABLE presupuesto_subitem
  DROP INDEX uk_subitem_scope_codigo,
  ADD UNIQUE KEY uk_subitem_scope_tipo_codigo (departamento_scope,tipo,codigo),
  ADD KEY ix_subitem_tipo_estatus (tipo,estatus);

-- Compatibilidad con entradas capturadas en V4.7: si alguna ENTRADA ya usó un
-- sub-item antiguo, se crea una copia independiente de tipo ENTRADA y se reasigna
-- el movimiento histórico a esa nueva categoría. No se altera ninguna SALIDA.
INSERT INTO presupuesto_subitem (departamento_id,tipo,codigo,nombre,descripcion,estatus,created_by_usuario_id)
SELECT DISTINCT s.departamento_id,'ENTRADA',s.codigo,s.nombre,
       CONCAT(COALESCE(s.descripcion,''), CASE WHEN COALESCE(s.descripcion,'')='' THEN '' ELSE ' · ' END, 'Migrado de entrada V4.7'),
       s.estatus,s.created_by_usuario_id
FROM presupuesto_subitem s
JOIN presupuesto_movimiento pm ON pm.subitem_id=s.subitem_id
WHERE pm.tipo='ENTRADA' AND s.tipo='SALIDA'
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

UPDATE presupuesto_movimiento pm
JOIN presupuesto_subitem s_old ON s_old.subitem_id=pm.subitem_id AND s_old.tipo='SALIDA'
JOIN presupuesto_subitem s_new ON s_new.tipo='ENTRADA'
  AND s_new.departamento_scope=s_old.departamento_scope
  AND s_new.codigo=s_old.codigo
SET pm.subitem_id=s_new.subitem_id
WHERE pm.tipo='ENTRADA';

SET @U_ADMIN=(SELECT usuario_id FROM usuario WHERE username='admin.demo' LIMIT 1);
SET @U_CREATOR=COALESCE(@U_ADMIN,(SELECT MIN(usuario_id) FROM usuario));

INSERT INTO presupuesto_subitem (departamento_id,tipo,codigo,nombre,descripcion,estatus,created_by_usuario_id) VALUES
(NULL,'ENTRADA','APO','Aportaciones','Aportaciones estatales, federales o extraordinarias','ACTIVO',@U_CREATOR),
(NULL,'ENTRADA','ING','Ingresos propios','Ingresos propios asignados al departamento','ACTIVO',@U_CREATOR),
(NULL,'ENTRADA','REI','Reintegros','Reintegros y recuperaciones de recurso','ACTIVO',@U_CREATOR),
(NULL,'ENTRADA','CON','Convenios','Recursos recibidos mediante convenios','ACTIVO',@U_CREATOR)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre),descripcion=VALUES(descripcion),estatus='ACTIVO';

SELECT tipo, COUNT(*) total FROM presupuesto_subitem GROUP BY tipo ORDER BY tipo;

-- Catálogo inicial global de sub-items basado en el flujo visual acordado.
INSERT INTO presupuesto_subitem (departamento_id,codigo,nombre,descripcion,status)
SELECT NULL,'FERRETERIA','Ferretería','Materiales y herramientas de ferretería',1
WHERE NOT EXISTS (SELECT 1 FROM presupuesto_subitem WHERE departamento_id IS NULL AND codigo='FERRETERIA');

INSERT INTO presupuesto_subitem (departamento_id,codigo,nombre,descripcion,status)
SELECT NULL,'GASOLINA','Gasolina','Combustibles y lubricantes',1
WHERE NOT EXISTS (SELECT 1 FROM presupuesto_subitem WHERE departamento_id IS NULL AND codigo='GASOLINA');

INSERT INTO presupuesto_subitem (departamento_id,codigo,nombre,descripcion,status)
SELECT NULL,'PAPELERIA','Papelería','Materiales de oficina y papelería',1
WHERE NOT EXISTS (SELECT 1 FROM presupuesto_subitem WHERE departamento_id IS NULL AND codigo='PAPELERIA');

INSERT INTO presupuesto_subitem (departamento_id,codigo,nombre,descripcion,status)
SELECT NULL,'SERVICIOS','Servicios','Servicios generales y contrataciones',1
WHERE NOT EXISTS (SELECT 1 FROM presupuesto_subitem WHERE departamento_id IS NULL AND codigo='SERVICIOS');

INSERT INTO presupuesto_subitem (departamento_id,codigo,nombre,descripcion,status)
SELECT NULL,'MANTENIMIENTO','Mantenimiento','Mantenimiento y refacciones de equipos',1
WHERE NOT EXISTS (SELECT 1 FROM presupuesto_subitem WHERE departamento_id IS NULL AND codigo='MANTENIMIENTO');

USE `ixtla01_dep02`;

-- =============================================================
-- PERMISOS
-- =============================================================
INSERT INTO permiso (codigo,nombre,descripcion,modulo) VALUES
('DEPARTAMENTOS_GESTIONAR','Gestionar departamentos','Alta, edición y activación de departamentos','ADMIN'),
('USUARIOS_GESTIONAR','Gestionar usuarios','Alta, edición y asignación jerárquica de usuarios','ADMIN'),
('ROLES_GESTIONAR','Gestionar roles','Crear roles y administrar permisos','ADMIN'),
('PRESUPUESTO_VER','Ver presupuesto','Consultar presupuesto y disponibilidad','PRESUPUESTO'),
('PRESUPUESTO_ASIGNAR','Asignar presupuesto','Crear o modificar presupuesto anual por departamento','PRESUPUESTO'),
('SUBITEMS_GESTIONAR','Gestionar sub-items','Administrar categorías presupuestales','PRESUPUESTO'),
('SOLICITUD_CREAR','Crear solicitudes','Crear solicitudes de salida para Tesorería','SOLICITUDES'),
('SOLICITUD_APROBAR','Gestionar solicitudes','Autorizar, rechazar o marcar solicitudes','SOLICITUDES'),
('MOVIMIENTO_VER','Ver movimientos','Consultar movimientos dentro del alcance del usuario','MOVIMIENTOS'),
('MOVIMIENTO_ENTRADA_CREAR','Registrar entradas','Registrar entradas presupuestales','MOVIMIENTOS'),
('MOVIMIENTO_SALIDA_CREAR','Registrar salidas','Registrar salidas reales de dinero','MOVIMIENTOS'),
('MOVIMIENTO_CANCELAR','Cancelar movimientos','Cancelar movimientos con trazabilidad','MOVIMIENTOS'),
('ACLARACION_CREAR','Crear aclaraciones','Abrir aclaraciones o seguimientos sobre movimientos','ACLARACIONES'),
('ACLARACION_GESTIONAR','Gestionar aclaraciones','Responder, reasignar y cerrar aclaraciones','ACLARACIONES'),
('BITACORA_VER','Ver bitácora','Consultar trazabilidad de operaciones','AUDITORIA'),
('REPORTES_VER','Ver reportes','Consultar tableros e indicadores','REPORTES')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre),descripcion=VALUES(descripcion),modulo=VALUES(modulo);

-- =============================================================
-- ROLES BASE
-- =============================================================
INSERT INTO rol (codigo,nombre,descripcion,alcance,es_sistema,estatus) VALUES
('ADMIN','Administrador','Configuración completa de la aplicación','GLOBAL',1,'ACTIVO'),
('PRESIDENTE','Presidente Municipal','Visibilidad y operación global; experiencia prioritaria móvil','GLOBAL',1,'ACTIVO'),
('TESORERIA','Tesorería','Control financiero global y registro de entradas/salidas','GLOBAL',1,'ACTIVO'),
('DIRECTOR','Director de Departamento','Ve y gestiona la información de su departamento','DEPARTAMENTO',1,'ACTIVO'),
('SUPERVISOR','Supervisor de Departamento','Ve su propia jerarquía: él y sus subordinados','JERARQUIA',1,'ACTIVO'),
('SUBORDINADO','Subordinado de Departamento','Ve su propia información y puede solicitar/consultar','PROPIO',1,'ACTIVO')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre),descripcion=VALUES(descripcion),alcance=VALUES(alcance),es_sistema=VALUES(es_sistema),estatus=VALUES(estatus);

-- ADMIN y PRESIDENTE: todos los permisos.
INSERT IGNORE INTO rol_permiso (rol_id,permiso_id)
SELECT r.rol_id,p.permiso_id FROM rol r CROSS JOIN permiso p WHERE r.codigo IN ('ADMIN','PRESIDENTE');

-- TESORERIA: finanzas, solicitudes, aclaraciones y auditoría.
INSERT IGNORE INTO rol_permiso (rol_id,permiso_id)
SELECT r.rol_id,p.permiso_id FROM rol r JOIN permiso p
WHERE r.codigo='TESORERIA' AND p.codigo IN (
 'PRESUPUESTO_VER','SUBITEMS_GESTIONAR','SOLICITUD_APROBAR',
 'MOVIMIENTO_VER','MOVIMIENTO_ENTRADA_CREAR','MOVIMIENTO_SALIDA_CREAR','MOVIMIENTO_CANCELAR',
 'ACLARACION_CREAR','ACLARACION_GESTIONAR','BITACORA_VER','REPORTES_VER'
);

-- DIRECTOR: su departamento.
INSERT IGNORE INTO rol_permiso (rol_id,permiso_id)
SELECT r.rol_id,p.permiso_id FROM rol r JOIN permiso p
WHERE r.codigo='DIRECTOR' AND p.codigo IN (
 'PRESUPUESTO_VER','SOLICITUD_CREAR','SOLICITUD_APROBAR','MOVIMIENTO_VER',
 'ACLARACION_CREAR','ACLARACION_GESTIONAR','REPORTES_VER'
);

-- SUPERVISOR: su jerarquía.
INSERT IGNORE INTO rol_permiso (rol_id,permiso_id)
SELECT r.rol_id,p.permiso_id FROM rol r JOIN permiso p
WHERE r.codigo='SUPERVISOR' AND p.codigo IN (
 'PRESUPUESTO_VER','SOLICITUD_CREAR','MOVIMIENTO_VER','ACLARACION_CREAR','REPORTES_VER'
);

-- SUBORDINADO: propio.
INSERT IGNORE INTO rol_permiso (rol_id,permiso_id)
SELECT r.rol_id,p.permiso_id FROM rol r JOIN permiso p
WHERE r.codigo='SUBORDINADO' AND p.codigo IN (
 'SOLICITUD_CREAR','MOVIMIENTO_VER','ACLARACION_CREAR'
);

-- =============================================================
-- DEPARTAMENTOS DEMO
-- =============================================================
INSERT INTO departamento (codigo,nombre,descripcion,color_hex,icono,es_tesoreria,estatus) VALUES
('TES','Tesorería','Administración y control financiero municipal','#A9423D','wallet',1,'ACTIVO'),
('CUL','Cultura','Programas, actividades y eventos culturales','#859F8E','palette',0,'ACTIVO'),
('SG','Servicios Generales','Operación y mantenimiento de servicios generales','#5F7F6A','wrench',0,'ACTIVO'),
('OP','Obras Públicas','Infraestructura y obra pública','#B38A58','building',0,'ACTIVO'),
('DSP','Desarrollo Social','Programas y apoyos sociales','#6C7FA3','users',0,'ACTIVO')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre),descripcion=VALUES(descripcion),color_hex=VALUES(color_hex),icono=VALUES(icono),es_tesoreria=VALUES(es_tesoreria),estatus=VALUES(estatus);

-- =============================================================
-- USUARIOS DEMO
-- Las cuentas demo se crean sin una contraseña utilizable en el repositorio.
-- Inicializa sus contraseñas fuera de Git con: php scripts/set_demo_passwords.php
-- =============================================================
SET @PASSWORD_PENDING = 'PASSWORD_NOT_INITIALIZED';

INSERT INTO usuario (uuid,username,nombre,apellido_paterno,apellido_materno,email,puesto,password_hash,estatus,requiere_cambio_password) VALUES
(UUID(),'admin.demo','Administrador','Demo',NULL,'admin.demo@ixtlahuacan.gob.mx','Administrador de plataforma',@PASSWORD_PENDING,'ACTIVO',1),
(UUID(),'presidente.demo','Presidente','Municipal','Demo','presidente.demo@ixtlahuacan.gob.mx','Presidente Municipal',@PASSWORD_PENDING,'ACTIVO',1),
(UUID(),'tesoreria.demo','Andrea','Tesorería','Demo','tesoreria.demo@ixtlahuacan.gob.mx','Responsable de Tesorería',@PASSWORD_PENDING,'ACTIVO',1),
(UUID(),'cultura.director','Daniela','Cultura','Demo','cultura.director@ixtlahuacan.gob.mx','Directora de Cultura',@PASSWORD_PENDING,'ACTIVO',1),
(UUID(),'cultura.supervisor','Marco','Supervisor','Demo','cultura.supervisor@ixtlahuacan.gob.mx','Supervisor de Cultura',@PASSWORD_PENDING,'ACTIVO',1),
(UUID(),'cultura.auxiliar','Laura','Auxiliar','Demo','cultura.auxiliar@ixtlahuacan.gob.mx','Auxiliar de Cultura',@PASSWORD_PENDING,'ACTIVO',1),
(UUID(),'servicios.director','Sofía','Servicios','Demo','servicios.director@ixtlahuacan.gob.mx','Directora de Servicios Generales',@PASSWORD_PENDING,'ACTIVO',1)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre),apellido_paterno=VALUES(apellido_paterno),apellido_materno=VALUES(apellido_materno),email=VALUES(email),puesto=VALUES(puesto),estatus='ACTIVO';

SET @U_ADMIN=(SELECT usuario_id FROM usuario WHERE username='admin.demo');
SET @U_PRES=(SELECT usuario_id FROM usuario WHERE username='presidente.demo');
SET @U_TES=(SELECT usuario_id FROM usuario WHERE username='tesoreria.demo');
SET @U_CDIR=(SELECT usuario_id FROM usuario WHERE username='cultura.director');
SET @U_CSUP=(SELECT usuario_id FROM usuario WHERE username='cultura.supervisor');
SET @U_CAUX=(SELECT usuario_id FROM usuario WHERE username='cultura.auxiliar');
SET @U_SDIR=(SELECT usuario_id FROM usuario WHERE username='servicios.director');
SET @D_TES=(SELECT departamento_id FROM departamento WHERE codigo='TES');
SET @D_CUL=(SELECT departamento_id FROM departamento WHERE codigo='CUL');
SET @D_SG=(SELECT departamento_id FROM departamento WHERE codigo='SG');
SET @R_ADMIN=(SELECT rol_id FROM rol WHERE codigo='ADMIN');
SET @R_PRES=(SELECT rol_id FROM rol WHERE codigo='PRESIDENTE');
SET @R_TES=(SELECT rol_id FROM rol WHERE codigo='TESORERIA');
SET @R_DIR=(SELECT rol_id FROM rol WHERE codigo='DIRECTOR');
SET @R_SUP=(SELECT rol_id FROM rol WHERE codigo='SUPERVISOR');
SET @R_SUB=(SELECT rol_id FROM rol WHERE codigo='SUBORDINADO');

-- Asignaciones globales (NULL departamento): insertar solo si no existe.
INSERT INTO usuario_departamento (usuario_id,departamento_id,rol_id,jefe_usuario_id,es_principal,estatus,created_by_usuario_id)
SELECT @U_ADMIN,NULL,@R_ADMIN,NULL,1,'ACTIVO',@U_ADMIN
WHERE NOT EXISTS (SELECT 1 FROM usuario_departamento WHERE usuario_id=@U_ADMIN AND rol_id=@R_ADMIN AND estatus='ACTIVO');
INSERT INTO usuario_departamento (usuario_id,departamento_id,rol_id,jefe_usuario_id,es_principal,estatus,created_by_usuario_id)
SELECT @U_PRES,NULL,@R_PRES,NULL,1,'ACTIVO',@U_ADMIN
WHERE NOT EXISTS (SELECT 1 FROM usuario_departamento WHERE usuario_id=@U_PRES AND rol_id=@R_PRES AND estatus='ACTIVO');
INSERT INTO usuario_departamento (usuario_id,departamento_id,rol_id,jefe_usuario_id,es_principal,estatus,created_by_usuario_id)
SELECT @U_TES,@D_TES,@R_TES,NULL,1,'ACTIVO',@U_ADMIN
WHERE NOT EXISTS (SELECT 1 FROM usuario_departamento WHERE usuario_id=@U_TES AND rol_id=@R_TES AND estatus='ACTIVO');

INSERT INTO usuario_departamento (usuario_id,departamento_id,rol_id,jefe_usuario_id,es_principal,estatus,created_by_usuario_id) VALUES
(@U_CDIR,@D_CUL,@R_DIR,NULL,1,'ACTIVO',@U_ADMIN),
(@U_CSUP,@D_CUL,@R_SUP,@U_CDIR,1,'ACTIVO',@U_ADMIN),
(@U_CAUX,@D_CUL,@R_SUB,@U_CSUP,1,'ACTIVO',@U_ADMIN),
(@U_SDIR,@D_SG,@R_DIR,NULL,1,'ACTIVO',@U_ADMIN)
ON DUPLICATE KEY UPDATE jefe_usuario_id=VALUES(jefe_usuario_id),es_principal=1,estatus='ACTIVO';

-- =============================================================
-- SUBITEMS
-- =============================================================
INSERT INTO presupuesto_subitem (departamento_id,tipo,codigo,nombre,descripcion,estatus,created_by_usuario_id) VALUES
(NULL,'SALIDA','FER','Ferretería','Materiales y herramientas de ferretería','ACTIVO',@U_ADMIN),
(NULL,'SALIDA','GAS','Gasolina','Combustibles y lubricantes','ACTIVO',@U_ADMIN),
(NULL,'SALIDA','PAP','Papelería','Materiales de oficina y papelería','ACTIVO',@U_ADMIN),
(NULL,'SALIDA','SER','Servicios','Servicios generales y contrataciones','ACTIVO',@U_ADMIN),
(NULL,'SALIDA','MAN','Mantenimiento','Mantenimiento y refacciones','ACTIVO',@U_ADMIN),
(@D_CUL,'SALIDA','EVE','Eventos culturales','Producción y operación de eventos culturales','ACTIVO',@U_ADMIN),
(NULL,'ENTRADA','APO','Aportaciones','Aportaciones estatales, federales o extraordinarias','ACTIVO',@U_ADMIN),
(NULL,'ENTRADA','ING','Ingresos propios','Ingresos propios asignados al departamento','ACTIVO',@U_ADMIN),
(NULL,'ENTRADA','REI','Reintegros','Reintegros y recuperaciones de recurso','ACTIVO',@U_ADMIN),
(NULL,'ENTRADA','CON','Convenios','Recursos recibidos mediante convenios','ACTIVO',@U_ADMIN)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre),descripcion=VALUES(descripcion),estatus='ACTIVO';

-- =============================================================
-- PRESUPUESTOS DEMO 2026
-- =============================================================
INSERT INTO presupuesto_departamento (departamento_id,ejercicio,presupuesto_asignado,observaciones,estatus,created_by_usuario_id,updated_by_usuario_id)
SELECT departamento_id,2026,
 CASE codigo WHEN 'CUL' THEN 850000.00 WHEN 'SG' THEN 1200000.00 WHEN 'OP' THEN 4500000.00 WHEN 'DSP' THEN 1800000.00 WHEN 'TES' THEN 950000.00 ELSE 0 END,
 'Presupuesto demo para validación funcional','ACTIVO',@U_ADMIN,@U_ADMIN
FROM departamento
ON DUPLICATE KEY UPDATE presupuesto_asignado=VALUES(presupuesto_asignado),observaciones=VALUES(observaciones),estatus='ACTIVO',updated_by_usuario_id=@U_ADMIN;

SET @S_EVE=(SELECT subitem_id FROM presupuesto_subitem WHERE departamento_id=@D_CUL AND tipo='SALIDA' AND codigo='EVE' LIMIT 1);
SET @S_PAP=(SELECT subitem_id FROM presupuesto_subitem WHERE departamento_id IS NULL AND tipo='SALIDA' AND codigo='PAP' LIMIT 1);
SET @S_SER=(SELECT subitem_id FROM presupuesto_subitem WHERE departamento_id IS NULL AND tipo='SALIDA' AND codigo='SER' LIMIT 1);

-- Solicitud demo de Cultura.
INSERT INTO presupuesto_solicitud (folio,ejercicio,departamento_id,subitem_id,fecha_solicitud,monto_solicitado,concepto,solicitado_por_usuario_id,otorgado_a_usuario_id,beneficiario_nombre,area_solicitante,estatus)
VALUES ('SOL-2026-000001',2026,@D_CUL,@S_EVE,'2026-08-18',12500.00,'Material y logística para evento cultural',@U_CSUP,@U_CAUX,NULL,'Cultura','PENDIENTE')
ON DUPLICATE KEY UPDATE concepto=VALUES(concepto),monto_solicitado=VALUES(monto_solicitado),estatus='PENDIENTE';

-- Movimientos demo. Tesorería registra salidas.
INSERT INTO presupuesto_movimiento (folio,ejercicio,departamento_id,subitem_id,tipo,fecha,monto,concepto,solicitado_por_usuario_id,otorgado_a_usuario_id,beneficiario_nombre,area_solicitante,metodo_pago,referencia,estatus,registrado_por_usuario_id) VALUES
('FOL-2026-000001',2026,@D_CUL,@S_PAP,'SALIDA','2026-08-10',3200.00,'Papelería para talleres culturales',@U_CDIR,@U_CAUX,NULL,'Cultura','TRANSFERENCIA','TRX-DEMO-001','REGISTRADO',@U_TES),
('FOL-2026-000002',2026,@D_CUL,@S_SER,'SALIDA','2026-08-14',8500.00,'Servicio de audio para evento municipal',@U_CSUP,NULL,'Proveedor Audio Demo','Cultura','TRANSFERENCIA','TRX-DEMO-002','REGISTRADO',@U_TES),
('FOL-2026-000003',2026,@D_SG,@S_SER,'SALIDA','2026-08-15',15600.00,'Mantenimiento preventivo de instalaciones',@U_SDIR,NULL,'Mantenimiento Demo SA','Servicios Generales','CHEQUE','CHQ-DEMO-003','REGISTRADO',@U_TES)
ON DUPLICATE KEY UPDATE concepto=VALUES(concepto),monto=VALUES(monto),estatus='REGISTRADO';

INSERT INTO presupuesto_folio_anual (ejercicio,tipo,ultimo_folio) VALUES
(2026,'MOVIMIENTO',3),(2026,'SOLICITUD',1),(2026,'ACLARACION',0)
ON DUPLICATE KEY UPDATE ultimo_folio=GREATEST(ultimo_folio,VALUES(ultimo_folio));

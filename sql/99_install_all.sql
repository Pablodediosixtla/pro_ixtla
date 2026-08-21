-- =============================================================
-- PRESUPUESTO IXTLAHUACAN - DEP02
-- 00_create_schema.sql
-- MySQL 8 / Azure Database for MySQL Flexible Server
-- =============================================================
CREATE DATABASE IF NOT EXISTS `ixtla01_dep02`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ixtla01_dep02`;

SELECT DATABASE() AS schema_activo,
       @@character_set_database AS character_set_database,
       @@collation_database AS collation_database;
USE `ixtla01_dep02`;

CREATE TABLE IF NOT EXISTS departamento (
    departamento_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(30) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(500) NULL,
    color_hex CHAR(7) NULL DEFAULT '#859F8E',
    icono VARCHAR(60) NULL DEFAULT 'building',
    es_tesoreria TINYINT(1) NOT NULL DEFAULT 0,
    estatus ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (departamento_id),
    UNIQUE KEY uk_departamento_codigo (codigo),
    UNIQUE KEY uk_departamento_nombre (nombre),
    KEY ix_departamento_estatus (estatus),
    KEY ix_departamento_tesoreria (es_tesoreria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permiso (
    permiso_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(80) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(500) NULL,
    modulo VARCHAR(60) NOT NULL,
    PRIMARY KEY (permiso_id),
    UNIQUE KEY uk_permiso_codigo (codigo),
    KEY ix_permiso_modulo (modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rol (
    rol_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(60) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    descripcion VARCHAR(500) NULL,
    alcance ENUM('GLOBAL','DEPARTAMENTO','JERARQUIA','PROPIO') NOT NULL DEFAULT 'PROPIO',
    es_sistema TINYINT(1) NOT NULL DEFAULT 0,
    estatus ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (rol_id),
    UNIQUE KEY uk_rol_codigo (codigo),
    KEY ix_rol_alcance (alcance),
    KEY ix_rol_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rol_permiso (
    rol_id SMALLINT UNSIGNED NOT NULL,
    permiso_id SMALLINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (rol_id, permiso_id),
    CONSTRAINT fk_rol_permiso_rol FOREIGN KEY (rol_id) REFERENCES rol(rol_id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_rol_permiso_permiso FOREIGN KEY (permiso_id) REFERENCES permiso(permiso_id)
      ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario (
    usuario_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL,
    username VARCHAR(80) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NULL,
    apellido_materno VARCHAR(100) NULL,
    email VARCHAR(180) NULL,
    telefono VARCHAR(30) NULL,
    puesto VARCHAR(150) NULL,
    password_hash VARCHAR(255) NOT NULL,
    estatus ENUM('ACTIVO','INACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
    intentos_fallidos SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    requiere_cambio_password TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_login_at DATETIME NULL,
    ultimo_login_ip VARCHAR(45) NULL,
    created_by_usuario_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id),
    UNIQUE KEY uk_usuario_uuid (uuid),
    UNIQUE KEY uk_usuario_username (username),
    UNIQUE KEY uk_usuario_email (email),
    KEY ix_usuario_estatus (estatus),
    CONSTRAINT fk_usuario_created_by FOREIGN KEY (created_by_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_departamento (
    usuario_departamento_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id BIGINT UNSIGNED NOT NULL,
    departamento_id BIGINT UNSIGNED NULL COMMENT 'NULL permitido solo para roles GLOBAL',
    rol_id SMALLINT UNSIGNED NOT NULL,
    jefe_usuario_id BIGINT UNSIGNED NULL COMMENT 'Jerarquia operativa dentro del departamento',
    es_principal TINYINT(1) NOT NULL DEFAULT 1,
    estatus ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    created_by_usuario_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_departamento_id),
    UNIQUE KEY uk_usuario_departamento_rol (usuario_id, departamento_id, rol_id),
    KEY ix_ud_usuario (usuario_id),
    KEY ix_ud_departamento (departamento_id),
    KEY ix_ud_rol (rol_id),
    KEY ix_ud_jefe (jefe_usuario_id),
    KEY ix_ud_estatus (estatus),
    CONSTRAINT fk_ud_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_ud_departamento FOREIGN KEY (departamento_id) REFERENCES departamento(departamento_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ud_rol FOREIGN KEY (rol_id) REFERENCES rol(rol_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ud_jefe FOREIGN KEY (jefe_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_ud_created_by FOREIGN KEY (created_by_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
USE `ixtla01_dep02`;

CREATE TABLE IF NOT EXISTS presupuesto_departamento (
    presupuesto_departamento_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    departamento_id BIGINT UNSIGNED NOT NULL,
    ejercicio SMALLINT UNSIGNED NOT NULL,
    presupuesto_asignado DECIMAL(16,2) NOT NULL DEFAULT 0.00,
    observaciones VARCHAR(500) NULL,
    estatus ENUM('ACTIVO','CERRADO') NOT NULL DEFAULT 'ACTIVO',
    created_by_usuario_id BIGINT UNSIGNED NOT NULL,
    updated_by_usuario_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (presupuesto_departamento_id),
    UNIQUE KEY uk_presupuesto_dep_ejercicio (departamento_id, ejercicio),
    KEY ix_presupuesto_ejercicio (ejercicio),
    CONSTRAINT fk_presupuesto_dep FOREIGN KEY (departamento_id) REFERENCES departamento(departamento_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_presupuesto_created_by FOREIGN KEY (created_by_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_presupuesto_updated_by FOREIGN KEY (updated_by_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT ck_presupuesto_asignado CHECK (presupuesto_asignado >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuesto_subitem (
    subitem_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    departamento_id BIGINT UNSIGNED NULL COMMENT 'NULL = subitem global',
    departamento_scope BIGINT UNSIGNED GENERATED ALWAYS AS (IFNULL(departamento_id, 0)) STORED,
    tipo ENUM('ENTRADA','SALIDA') NOT NULL DEFAULT 'SALIDA',
    codigo VARCHAR(40) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(500) NULL,
    estatus ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    created_by_usuario_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (subitem_id),
    UNIQUE KEY uk_subitem_scope_tipo_codigo (departamento_scope, tipo, codigo),
    KEY ix_subitem_departamento (departamento_id),
    KEY ix_subitem_tipo_estatus (tipo, estatus),
    KEY ix_subitem_estatus (estatus),
    CONSTRAINT fk_subitem_dep FOREIGN KEY (departamento_id) REFERENCES departamento(departamento_id)
      ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_subitem_created_by FOREIGN KEY (created_by_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuesto_folio_anual (
    ejercicio SMALLINT UNSIGNED NOT NULL,
    tipo ENUM('MOVIMIENTO','SOLICITUD','ACLARACION') NOT NULL,
    ultimo_folio INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (ejercicio, tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuesto_solicitud (
    solicitud_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    folio VARCHAR(30) NOT NULL,
    ejercicio SMALLINT UNSIGNED NOT NULL,
    departamento_id BIGINT UNSIGNED NOT NULL,
    subitem_id BIGINT UNSIGNED NULL,
    fecha_solicitud DATE NOT NULL,
    monto_solicitado DECIMAL(16,2) NOT NULL,
    concepto VARCHAR(700) NOT NULL,
    solicitado_por_usuario_id BIGINT UNSIGNED NOT NULL,
    otorgado_a_usuario_id BIGINT UNSIGNED NULL,
    beneficiario_nombre VARCHAR(180) NULL,
    area_solicitante VARCHAR(180) NULL,
    estatus ENUM('PENDIENTE','AUTORIZADA','RECHAZADA','PAGADA','CANCELADA') NOT NULL DEFAULT 'PENDIENTE',
    resuelto_por_usuario_id BIGINT UNSIGNED NULL,
    resuelto_at DATETIME NULL,
    comentario_resolucion VARCHAR(700) NULL,
    movimiento_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (solicitud_id),
    UNIQUE KEY uk_solicitud_folio (folio),
    KEY ix_solicitud_dep_fecha (departamento_id, fecha_solicitud),
    KEY ix_solicitud_usuario (solicitado_por_usuario_id),
    KEY ix_solicitud_estatus (estatus),
    KEY ix_solicitud_subitem (subitem_id),
    CONSTRAINT fk_solicitud_dep FOREIGN KEY (departamento_id) REFERENCES departamento(departamento_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_solicitud_subitem FOREIGN KEY (subitem_id) REFERENCES presupuesto_subitem(subitem_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_solicitud_solicitante FOREIGN KEY (solicitado_por_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_solicitud_otorgado FOREIGN KEY (otorgado_a_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_solicitud_resuelto FOREIGN KEY (resuelto_por_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT ck_solicitud_monto CHECK (monto_solicitado > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuesto_movimiento (
    movimiento_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    folio VARCHAR(30) NOT NULL,
    ejercicio SMALLINT UNSIGNED NOT NULL,
    departamento_id BIGINT UNSIGNED NOT NULL,
    subitem_id BIGINT UNSIGNED NULL,
    solicitud_id BIGINT UNSIGNED NULL,
    tipo ENUM('ENTRADA','SALIDA') NOT NULL,
    fecha DATE NOT NULL,
    monto DECIMAL(16,2) NOT NULL,
    concepto VARCHAR(700) NOT NULL,
    solicitado_por_usuario_id BIGINT UNSIGNED NULL,
    otorgado_a_usuario_id BIGINT UNSIGNED NULL,
    beneficiario_nombre VARCHAR(180) NULL,
    area_solicitante VARCHAR(180) NULL,
    metodo_pago ENUM('EFECTIVO','TRANSFERENCIA','CHEQUE','TARJETA','OTRO') NULL,
    referencia VARCHAR(180) NULL,
    estatus ENUM('REGISTRADO','CANCELADO') NOT NULL DEFAULT 'REGISTRADO',
    registrado_por_usuario_id BIGINT UNSIGNED NOT NULL,
    cancelado_por_usuario_id BIGINT UNSIGNED NULL,
    cancelado_at DATETIME NULL,
    motivo_cancelacion VARCHAR(700) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (movimiento_id),
    UNIQUE KEY uk_movimiento_folio (folio),
    UNIQUE KEY uk_movimiento_solicitud (solicitud_id),
    KEY ix_movimiento_dep_fecha (departamento_id, fecha),
    KEY ix_movimiento_ejercicio (ejercicio),
    KEY ix_movimiento_tipo_estatus (tipo, estatus),
    KEY ix_movimiento_solicitante (solicitado_por_usuario_id),
    KEY ix_movimiento_otorgado (otorgado_a_usuario_id),
    KEY ix_movimiento_registrado_por (registrado_por_usuario_id),
    CONSTRAINT fk_movimiento_dep FOREIGN KEY (departamento_id) REFERENCES departamento(departamento_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_movimiento_subitem FOREIGN KEY (subitem_id) REFERENCES presupuesto_subitem(subitem_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_movimiento_solicitud FOREIGN KEY (solicitud_id) REFERENCES presupuesto_solicitud(solicitud_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_mov_solicitante FOREIGN KEY (solicitado_por_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_mov_otorgado FOREIGN KEY (otorgado_a_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_mov_registrado FOREIGN KEY (registrado_por_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_mov_cancelado FOREIGN KEY (cancelado_por_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT ck_movimiento_monto CHECK (monto > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS presupuesto_movimiento_archivo (
    archivo_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    movimiento_id BIGINT UNSIGNED NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    nombre_guardado VARCHAR(255) NOT NULL,
    ruta_relativa VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    uploaded_by_usuario_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (archivo_id),
    KEY ix_archivo_movimiento (movimiento_id),
    CONSTRAINT fk_archivo_movimiento FOREIGN KEY (movimiento_id) REFERENCES presupuesto_movimiento(movimiento_id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_archivo_usuario FOREIGN KEY (uploaded_by_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
USE `ixtla01_dep02`;

CREATE TABLE IF NOT EXISTS movimiento_aclaracion (
    aclaracion_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    folio VARCHAR(30) NOT NULL,
    movimiento_id BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT NOT NULL,
    prioridad ENUM('BAJA','MEDIA','ALTA') NOT NULL DEFAULT 'MEDIA',
    estatus ENUM('ABIERTA','EN_REVISION','RESUELTA','CERRADA') NOT NULL DEFAULT 'ABIERTA',
    creada_por_usuario_id BIGINT UNSIGNED NOT NULL,
    asignada_a_usuario_id BIGINT UNSIGNED NULL,
    cerrada_por_usuario_id BIGINT UNSIGNED NULL,
    cerrada_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (aclaracion_id),
    UNIQUE KEY uk_aclaracion_folio (folio),
    KEY ix_aclaracion_movimiento (movimiento_id),
    KEY ix_aclaracion_estatus (estatus),
    KEY ix_aclaracion_creada_por (creada_por_usuario_id),
    CONSTRAINT fk_aclaracion_mov FOREIGN KEY (movimiento_id) REFERENCES presupuesto_movimiento(movimiento_id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_aclaracion_creada FOREIGN KEY (creada_por_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_aclaracion_asignada FOREIGN KEY (asignada_a_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_aclaracion_cerrada FOREIGN KEY (cerrada_por_usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS movimiento_aclaracion_mensaje (
    mensaje_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    aclaracion_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    mensaje TEXT NOT NULL,
    es_interno TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (mensaje_id),
    KEY ix_aclaracion_mensaje (aclaracion_id, created_at),
    CONSTRAINT fk_mensaje_aclaracion FOREIGN KEY (aclaracion_id) REFERENCES movimiento_aclaracion(aclaracion_id)
      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_mensaje_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bitacora (
    bitacora_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id BIGINT UNSIGNED NULL,
    accion VARCHAR(80) NOT NULL,
    entidad VARCHAR(80) NOT NULL,
    entidad_id VARCHAR(80) NULL,
    descripcion VARCHAR(700) NULL,
    datos_antes JSON NULL,
    datos_despues JSON NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (bitacora_id),
    KEY ix_bitacora_usuario_fecha (usuario_id, created_at),
    KEY ix_bitacora_entidad (entidad, entidad_id),
    KEY ix_bitacora_accion_fecha (accion, created_at),
    CONSTRAINT fk_bitacora_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id)
      ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
USE `ixtla01_dep02`;

CREATE OR REPLACE VIEW vw_usuario_perfil AS
SELECT
  u.usuario_id,
  u.username,
  CONCAT_WS(' ', u.nombre, u.apellido_paterno, u.apellido_materno) AS nombre_completo,
  u.email,
  u.telefono,
  u.puesto,
  u.estatus,
  ud.usuario_departamento_id,
  ud.departamento_id,
  d.codigo AS departamento_codigo,
  d.nombre AS departamento,
  ud.rol_id,
  r.codigo AS rol_codigo,
  r.nombre AS rol,
  r.alcance,
  ud.jefe_usuario_id,
  CONCAT_WS(' ', j.nombre, j.apellido_paterno, j.apellido_materno) AS jefe_nombre,
  ud.es_principal,
  ud.estatus AS asignacion_estatus
FROM usuario u
LEFT JOIN usuario_departamento ud ON ud.usuario_id = u.usuario_id AND ud.estatus='ACTIVO'
LEFT JOIN departamento d ON d.departamento_id = ud.departamento_id
LEFT JOIN rol r ON r.rol_id = ud.rol_id
LEFT JOIN usuario j ON j.usuario_id = ud.jefe_usuario_id;

CREATE OR REPLACE VIEW vw_presupuesto_departamento_resumen AS
SELECT
    d.departamento_id,
    d.codigo AS departamento_codigo,
    d.nombre AS departamento,
    pd.ejercicio,
    COALESCE(pd.presupuesto_asignado, 0) AS presupuesto_asignado,
    COALESCE(SUM(CASE WHEN pm.tipo='ENTRADA' AND pm.estatus='REGISTRADO' THEN pm.monto ELSE 0 END),0) AS entradas,
    COALESCE(SUM(CASE WHEN pm.tipo='SALIDA' AND pm.estatus='REGISTRADO' THEN pm.monto ELSE 0 END),0) AS salidas,
    COALESCE(pd.presupuesto_asignado, 0)
      + COALESCE(SUM(CASE WHEN pm.tipo='ENTRADA' AND pm.estatus='REGISTRADO' THEN pm.monto ELSE 0 END),0)
      - COALESCE(SUM(CASE WHEN pm.tipo='SALIDA' AND pm.estatus='REGISTRADO' THEN pm.monto ELSE 0 END),0) AS disponible,
    CASE WHEN COALESCE(pd.presupuesto_asignado,0) > 0
         THEN ROUND((COALESCE(SUM(CASE WHEN pm.tipo='SALIDA' AND pm.estatus='REGISTRADO' THEN pm.monto ELSE 0 END),0) / pd.presupuesto_asignado) * 100, 2)
         ELSE 0 END AS porcentaje_ejercido
FROM departamento d
JOIN presupuesto_departamento pd ON pd.departamento_id=d.departamento_id
LEFT JOIN presupuesto_movimiento pm
  ON pm.departamento_id=d.departamento_id AND pm.ejercicio=pd.ejercicio
WHERE d.estatus='ACTIVO'
GROUP BY d.departamento_id,d.codigo,d.nombre,pd.ejercicio,pd.presupuesto_asignado;

CREATE OR REPLACE VIEW vw_movimiento_detalle AS
SELECT
  pm.movimiento_id, pm.folio, pm.ejercicio, pm.tipo, pm.fecha, pm.monto, pm.concepto,
  pm.estatus, pm.metodo_pago, pm.referencia, pm.beneficiario_nombre, pm.area_solicitante,
  pm.departamento_id, d.codigo AS departamento_codigo, d.nombre AS departamento,
  pm.subitem_id, s.tipo AS subitem_tipo, s.codigo AS subitem_codigo, s.nombre AS subitem,
  pm.solicitud_id,
  pm.solicitado_por_usuario_id,
  CONCAT_WS(' ', us.nombre, us.apellido_paterno, us.apellido_materno) AS solicitado_por,
  pm.otorgado_a_usuario_id,
  CONCAT_WS(' ', uo.nombre, uo.apellido_paterno, uo.apellido_materno) AS otorgado_a,
  pm.registrado_por_usuario_id,
  CONCAT_WS(' ', ur.nombre, ur.apellido_paterno, ur.apellido_materno) AS registrado_por,
  pm.created_at, pm.updated_at
FROM presupuesto_movimiento pm
JOIN departamento d ON d.departamento_id=pm.departamento_id
LEFT JOIN presupuesto_subitem s ON s.subitem_id=pm.subitem_id
LEFT JOIN usuario us ON us.usuario_id=pm.solicitado_por_usuario_id
LEFT JOIN usuario uo ON uo.usuario_id=pm.otorgado_a_usuario_id
JOIN usuario ur ON ur.usuario_id=pm.registrado_por_usuario_id;
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

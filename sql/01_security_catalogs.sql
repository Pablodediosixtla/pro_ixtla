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

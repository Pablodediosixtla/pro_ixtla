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

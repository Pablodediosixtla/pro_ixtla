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

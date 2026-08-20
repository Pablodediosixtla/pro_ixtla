-- =============================================================
-- PRESUPUESTO IXTLAHUACÁN - ESQUEMA V1
-- MySQL 8 / Azure Database for MySQL
-- Reutiliza: departamento, empleado, empleado_cuenta, rol, empleado_rol
-- =============================================================

CREATE TABLE IF NOT EXISTS presupuesto_departamento (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    departamento_id INT NOT NULL,
    ejercicio SMALLINT UNSIGNED NOT NULL,
    presupuesto_asignado DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_by_cuenta_id INT NULL,
    updated_by_cuenta_id INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_presupuesto_departamento_ejercicio (departamento_id, ejercicio),
    KEY ix_presupuesto_departamento_status (status),
    KEY ix_presupuesto_departamento_ejercicio (ejercicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuesto_subitem (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    departamento_id INT NULL COMMENT 'NULL = sub-item global',
    departamento_scope INT GENERATED ALWAYS AS (IFNULL(departamento_id,0)) STORED,
    codigo VARCHAR(30) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    descripcion VARCHAR(500) NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_by_cuenta_id INT NULL,
    updated_by_cuenta_id INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_presupuesto_subitem_scope (departamento_scope, codigo),
    KEY ix_presupuesto_subitem_status (status),
    KEY ix_presupuesto_subitem_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuesto_usuario_permiso (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    empleado_cuenta_id INT NOT NULL,
    rol_presupuesto ENUM('ADMIN','CAPTURISTA','CONSULTA') NOT NULL,
    departamento_id INT NULL COMMENT 'NULL = todos los departamentos',
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_by_cuenta_id INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_presupuesto_permiso (empleado_cuenta_id, rol_presupuesto, departamento_id),
    KEY ix_presupuesto_permiso_cuenta (empleado_cuenta_id),
    KEY ix_presupuesto_permiso_departamento (departamento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuesto_folio_anual (
    ejercicio SMALLINT UNSIGNED NOT NULL,
    ultimo_folio INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (ejercicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuesto_movimiento (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    folio VARCHAR(24) NOT NULL,
    ejercicio SMALLINT UNSIGNED NOT NULL,
    departamento_id INT NOT NULL,
    subitem_id BIGINT UNSIGNED NULL,
    tipo ENUM('ENTRADA','SALIDA') NOT NULL,
    fecha DATE NOT NULL,
    monto DECIMAL(14,2) NOT NULL,
    concepto VARCHAR(500) NOT NULL,
    entregado_a VARCHAR(180) NULL,
    area_solicitante VARCHAR(180) NULL,
    metodo_pago ENUM('EFECTIVO','TRANSFERENCIA','CHEQUE','TARJETA','OTRO') NULL,
    referencia VARCHAR(180) NULL,
    status ENUM('REGISTRADO','CANCELADO') NOT NULL DEFAULT 'REGISTRADO',
    creado_por_cuenta_id INT NOT NULL,
    cancelado_por_cuenta_id INT NULL,
    cancelado_at DATETIME NULL,
    motivo_cancelacion VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_presupuesto_movimiento_folio (folio),
    KEY ix_presupuesto_movimiento_dep_fecha (departamento_id, fecha),
    KEY ix_presupuesto_movimiento_ejercicio (ejercicio),
    KEY ix_presupuesto_movimiento_tipo_status (tipo, status),
    KEY ix_presupuesto_movimiento_subitem (subitem_id),
    CONSTRAINT fk_presupuesto_movimiento_subitem
        FOREIGN KEY (subitem_id) REFERENCES presupuesto_subitem(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_presupuesto_movimiento_monto CHECK (monto > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuesto_movimiento_archivo (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    movimiento_id BIGINT UNSIGNED NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    nombre_guardado VARCHAR(255) NOT NULL,
    ruta_relativa VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    created_by_cuenta_id INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_presupuesto_archivo_movimiento (movimiento_id),
    CONSTRAINT fk_presupuesto_archivo_movimiento
        FOREIGN KEY (movimiento_id) REFERENCES presupuesto_movimiento(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vista operacional para consultas y Power BI.
CREATE OR REPLACE VIEW vw_presupuesto_departamento_resumen AS
SELECT
    d.id AS departamento_id,
    d.nombre AS departamento,
    pd.ejercicio,
    COALESCE(pd.presupuesto_asignado,0) AS presupuesto_asignado,
    COALESCE(SUM(CASE WHEN pm.tipo='ENTRADA' AND pm.status='REGISTRADO' THEN pm.monto ELSE 0 END),0) AS entradas,
    COALESCE(SUM(CASE WHEN pm.tipo='SALIDA' AND pm.status='REGISTRADO' THEN pm.monto ELSE 0 END),0) AS salidas,
    COALESCE(pd.presupuesto_asignado,0)
      + COALESCE(SUM(CASE WHEN pm.tipo='ENTRADA' AND pm.status='REGISTRADO' THEN pm.monto ELSE 0 END),0)
      - COALESCE(SUM(CASE WHEN pm.tipo='SALIDA' AND pm.status='REGISTRADO' THEN pm.monto ELSE 0 END),0) AS disponible
FROM departamento d
JOIN presupuesto_departamento pd ON pd.departamento_id=d.id AND pd.status=1
LEFT JOIN presupuesto_movimiento pm ON pm.departamento_id=d.id AND pm.ejercicio=pd.ejercicio
GROUP BY d.id,d.nombre,pd.ejercicio,pd.presupuesto_asignado;

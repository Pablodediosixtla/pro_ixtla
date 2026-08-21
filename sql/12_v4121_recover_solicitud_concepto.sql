-- V4.12.1 - Recuperación opcional de Concepto / uso en solicitudes afectadas
-- por el bind_param incorrecto de V4.12.
--
-- IMPORTANTE:
-- 1) Este script NO cambia estructura de tablas.
-- 2) Solo intenta corregir solicitudes cuyo concepto actual es exactamente '0'.
-- 3) Recupera el texto original desde bitacora.datos_despues, registrado por
--    SOLICITUD_CREAR. Si no existe auditoría para una solicitud, no la modifica.

USE ixtla01_dep02;

-- Revisar primero qué registros son candidatos y qué texto sería recuperado.
SELECT
    ps.solicitud_id,
    ps.folio,
    ps.concepto AS concepto_actual,
    JSON_UNQUOTE(JSON_EXTRACT(b.datos_despues, '$.concepto')) AS concepto_recuperable,
    b.created_at AS fecha_auditoria
FROM presupuesto_solicitud ps
JOIN bitacora b
  ON b.bitacora_id = (
      SELECT MAX(b2.bitacora_id)
      FROM bitacora b2
      WHERE b2.accion = 'SOLICITUD_CREAR'
        AND b2.entidad = 'SOLICITUD'
        AND CAST(b2.entidad_id AS UNSIGNED) = ps.solicitud_id
  )
WHERE TRIM(ps.concepto) = '0'
  AND JSON_UNQUOTE(JSON_EXTRACT(b.datos_despues, '$.concepto')) IS NOT NULL
  AND TRIM(JSON_UNQUOTE(JSON_EXTRACT(b.datos_despues, '$.concepto'))) NOT IN ('', '0')
ORDER BY ps.solicitud_id;

-- Ejecutar la actualización después de revisar el SELECT anterior.
UPDATE presupuesto_solicitud ps
JOIN bitacora b
  ON b.bitacora_id = (
      SELECT MAX(b2.bitacora_id)
      FROM bitacora b2
      WHERE b2.accion = 'SOLICITUD_CREAR'
        AND b2.entidad = 'SOLICITUD'
        AND CAST(b2.entidad_id AS UNSIGNED) = ps.solicitud_id
  )
SET ps.concepto = JSON_UNQUOTE(JSON_EXTRACT(b.datos_despues, '$.concepto'))
WHERE TRIM(ps.concepto) = '0'
  AND JSON_UNQUOTE(JSON_EXTRACT(b.datos_despues, '$.concepto')) IS NOT NULL
  AND TRIM(JSON_UNQUOTE(JSON_EXTRACT(b.datos_despues, '$.concepto'))) NOT IN ('', '0');

-- Validación final.
SELECT solicitud_id, folio, concepto
FROM presupuesto_solicitud
ORDER BY solicitud_id DESC
LIMIT 50;

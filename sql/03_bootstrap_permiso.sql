-- CAMBIA 'TU_USUARIO' por un username real de empleado_cuenta.
-- Otorga administración global del módulo Presupuesto.
INSERT INTO presupuesto_usuario_permiso (empleado_cuenta_id, rol_presupuesto, departamento_id, status)
SELECT c.id, 'ADMIN', NULL, 1
FROM empleado_cuenta c
WHERE c.username = 'TU_USUARIO'
  AND NOT EXISTS (
      SELECT 1
      FROM presupuesto_usuario_permiso p
      WHERE p.empleado_cuenta_id = c.id
        AND p.rol_presupuesto = 'ADMIN'
        AND p.departamento_id IS NULL
  );

SELECT c.id, c.username, p.rol_presupuesto, p.departamento_id
FROM empleado_cuenta c
JOIN presupuesto_usuario_permiso p ON p.empleado_cuenta_id=c.id
WHERE p.status=1;

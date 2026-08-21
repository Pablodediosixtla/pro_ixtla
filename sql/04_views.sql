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

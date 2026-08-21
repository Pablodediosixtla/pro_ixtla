USE ixtla01_dep02;

-- V4.12: los perfiles de alcance PROPIO no consultan presupuesto agregado.
-- El backend ya aplica esta regla aunque este script no se ejecute; esta
-- migración alinea también el catálogo de permisos de la base actual.
DELETE rp
FROM rol_permiso rp
JOIN rol r ON r.rol_id = rp.rol_id
JOIN permiso p ON p.permiso_id = rp.permiso_id
WHERE r.codigo = 'SUBORDINADO'
  AND p.codigo = 'PRESUPUESTO_VER';

SELECT r.codigo AS rol, p.codigo AS permiso
FROM rol_permiso rp
JOIN rol r ON r.rol_id = rp.rol_id
JOIN permiso p ON p.permiso_id = rp.permiso_id
WHERE r.codigo = 'SUBORDINADO'
ORDER BY p.codigo;

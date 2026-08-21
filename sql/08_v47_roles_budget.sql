USE `ixtla01_dep02`;

-- V4.7: la asignación anual queda reservada a ADMIN y PRESIDENTE.
DELETE rp
FROM rol_permiso rp
JOIN rol r ON r.rol_id=rp.rol_id
JOIN permiso p ON p.permiso_id=rp.permiso_id
WHERE r.codigo='TESORERIA' AND p.codigo='PRESUPUESTO_ASIGNAR';

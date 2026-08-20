-- Verifica las dependencias existentes encontradas en el proyecto DB proporcionado.
SELECT DATABASE() AS base_actual;

SHOW TABLES LIKE 'departamento';
SHOW TABLES LIKE 'empleado';
SHOW TABLES LIKE 'empleado_cuenta';
SHOW TABLES LIKE 'rol';
SHOW TABLES LIKE 'empleado_rol';

DESCRIBE departamento;
DESCRIBE empleado;
DESCRIBE empleado_cuenta;
DESCRIBE rol;
DESCRIBE empleado_rol;

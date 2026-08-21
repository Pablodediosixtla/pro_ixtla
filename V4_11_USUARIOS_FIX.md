# V4.11.0 — Corrección de usuarios, roles y departamentos

## Corrección
- Edición de usuario: rol, departamento y jefe se persisten y se verifican antes del COMMIT.
- Creación de usuario: se valida que la asignación principal haya sido creada correctamente.
- Se reutilizan asignaciones históricas inactivas para evitar conflictos con la llave única `uk_usuario_departamento_rol`.
- Los errores de `mysqli::execute()` ya no se ignoran silenciosamente en el guardado de usuarios.
- Los roles `GLOBAL` guardan departamento y jefe como `NULL` de forma explícita.
- La bitácora ya no recibe la contraseña enviada por el formulario.

## Base de datos
No requiere DDL ni migración.

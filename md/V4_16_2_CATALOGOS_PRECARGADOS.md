# V4.16.2 — Catálogos precargados en formularios

Se corrige la carga intermitente/404 de catálogos dependientes del departamento en Azure.

## Ajustes
- Los formularios de Registrar movimiento, Pagos y Solicitudes reciben desde el render PHP los catálogos permitidos para la sesión.
- Usuarios se agrupan por departamento y solo incluye cuentas/asignaciones ACTIVAS.
- Sub-items se filtran localmente por departamento y tipo ENTRADA/SALIDA.
- El formulario de Usuarios reutiliza el mismo catálogo para "Reporta a".
- Se conserva un fallback API para instalaciones donde no sea posible precargar el catálogo.
- No se modifica el esquema de base de datos ni Azure App Settings.

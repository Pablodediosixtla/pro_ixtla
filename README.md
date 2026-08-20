# Presupuesto Ixtlahuacán — V4 / DEP02

Aplicación web PHP + MySQL para la administración del presupuesto municipal de Ixtlahuacán. La aplicación es autocontenida y usa exclusivamente el schema `ixtla01_dep02`; no depende del login ni de servicios publicados en `ixtla-app.com`.

## Estado de esta versión

- Schema operativo: `ixtla01_dep02`
- MySQL 8 / Azure Database for MySQL
- PHP 8.x / Azure App Service `pro-ixtla`
- Servicios propios en `db/web/`
- Login real contra `usuario`
- Roles y permisos con alcance de datos
- Jerarquía de Director → Supervisor → Subordinado
- Tesorería registra las salidas reales
- Solicitudes previas al pago
- Aclaraciones y seguimiento por movimiento
- Evidencias PDF/JPG/PNG
- Bitácora de acciones
- Experiencia responsive y navegación rápida para móvil

## Modelo de acceso

| Rol | Alcance | Uso principal |
|---|---|---|
| ADMIN | GLOBAL | Configuración completa, catálogos, usuarios y operación |
| PRESIDENTE | GLOBAL | Mismo alcance global, priorizando consulta desde móvil |
| TESORERIA | GLOBAL | Presupuesto, solicitudes, entradas/salidas, aclaraciones y auditoría |
| DIRECTOR | DEPARTAMENTO | Información y solicitudes de su departamento |
| SUPERVISOR | JERARQUIA | Él y sus subordinados recursivos en su departamento |
| SUBORDINADO | PROPIO | Su propia operación y solicitudes |

La seguridad no depende de ocultar botones. Los endpoints vuelven a validar permisos y alcance antes de consultar o modificar datos.

## Flujo de salida

1. Un usuario del departamento genera una solicitud.
2. La solicitud se autoriza o rechaza según permisos.
3. Tesorería registra la salida financiera real.
4. Se genera un folio `FOL-AAAA-NNNNNN`.
5. Se registra quién solicitó, a quién se otorgó y quién registró el movimiento.
6. La evidencia queda asociada al movimiento.
7. Cualquier usuario con alcance puede levantar una aclaración.
8. La bitácora conserva las operaciones sensibles.

## SQL

El schema ya puede instalarse con:

1. `sql/00_create_schema.sql`
2. `sql/01_security_catalogs.sql`
3. `sql/02_budget_movements.sql`
4. `sql/03_followups_audit.sql`
5. `sql/04_views.sql`
6. `sql/05_seed_demo.sql`

`sql/99_install_all.sql` contiene todo el instalador consolidado. Esta versión incorpora la corrección de la FK de `presupuesto_subitem` que evita el Error 1215.

Después de instalar, ejecutar `sql/06_validate_install.sql` para revisar objetos, roles, usuarios y presupuesto demo.

`sql/07_demo_credentials.sql` permite restablecer únicamente las cuentas demo durante pruebas.

## Usuarios demo

Las contraseñas demo se inicializan fuera del repositorio con `php scripts/set_demo_passwords.php`.

- `admin.demo`
- `presidente.demo`
- `tesoreria.demo`
- `cultura.director`
- `cultura.supervisor`
- `cultura.auxiliar`
- `servicios.director`

## Configuración

La aplicación lee variables desde Azure App Service y, localmente, desde `.env`.

Variables principales:

```env
APP_ENV=production
APP_URL=https://pro-ixtla-c7azh2cagpfvfede.mexicocentral-01.azurewebsites.net
APP_TIMEZONE=America/Mexico_City
SESSION_NAME=PROIXTLA_SESSION

DB_HOST=...
DB_PORT=3306
DB_NAME=ixtla01_dep02
DB_USER=...
DB_PASS=...
DB_SSL=true
DB_SSL_CA=db/conn/DigiCertGlobalRootG2.crt.pem
DB_CONNECT_TIMEOUT=8
```

`.env` está en `.gitignore`; no debe publicarse en GitHub. Para Azure, las credenciales deben configurarse en **App Service → Variables de entorno**.

## Servicios

- `db/web/auth/` — login, logout y sesión
- `db/web/departamentos/` — administración de departamentos
- `db/web/usuarios/` — usuarios, roles y jerarquía
- `db/web/roles/` — RBAC y permisos
- `db/web/presupuestos/` — asignación y consulta de presupuesto
- `db/web/subitems/` — categorías presupuestales
- `db/web/solicitudes/` — solicitudes previas al pago
- `db/web/movimientos/` — entradas, salidas, evidencia, cancelación y detalle
- `db/web/aclaraciones/` — seguimiento tipo conversación
- `db/web/bitacora/` — auditoría
- `db/web/dashboard/` — KPIs
- `api.php?route=system/health` — disponibilidad del App Service y conexión al schema
- `api.php?route=system/schema-check` — validación de objetos para administradores

## Prueba de salud

Después del despliegue:

```text
https://pro-ixtla-c7azh2cagpfvfede.mexicocentral-01.azurewebsites.net/api.php?route=system/health
```

Debe mostrar `database.reachable=true`, `active_schema=ixtla01_dep02` y `schema_ok=true`.

## Validación local

```bash
./scripts/check_project.sh
```

El script valida sintaxis PHP y, si Node está disponible, sintaxis JavaScript.

## GitHub / Azure

El workflow se encuentra en `.github/workflows/main_pro_ixtla.yml` y despliega al App Service `pro-ixtla` utilizando el secret:

```text
AZURE_WEBAPP_PUBLISH_PROFILE
```

## Inicialización segura de contraseñas demo

Los SQL versionados no contienen contraseñas ni hashes. Para una instalación nueva, después de ejecutar el DDL/seed y con la conexión configurada en `.env`, ejecutar:

```bash
php scripts/set_demo_passwords.php
```

El script solicita la contraseña de forma interactiva y genera el hash con `password_hash()` en tiempo de ejecución. Así, ningún hash o secreto queda versionado en Git.

## V4.3 - Rediseño responsivo Ixtla

La V4.3 mantiene la lógica de negocio y servicios de la V4.2, pero actualiza la experiencia web y móvil:

- identidad visual alineada a Ixtlahuacán;
- sidebar institucional para escritorio;
- dashboard ejecutivo y KPIs más legibles;
- navegación rápida inferior para móvil;
- tablas responsivas que se convierten en tarjetas;
- registro de movimientos con flujo guiado por etapas;
- formularios y modales preparados para operación táctil;
- jerarquía visual de usuarios y mejor lectura de presupuestos, solicitudes y bitácoras.

La base sigue siendo `ixtla01_dep02` y la aplicación conserva el gateway `api.php`.

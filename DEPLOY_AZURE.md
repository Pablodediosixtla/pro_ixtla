# Despliegue de Presupuesto Ixtlahuacán en Azure

## 1. Base de datos

Confirmar que el schema activo sea:

```sql
USE ixtla01_dep02;
SHOW TABLES;
```

Después ejecutar:

```sql
SOURCE sql/06_validate_install.sql;
```

## 2. Variables de entorno de App Service

En `pro-ixtla` configurar:

```text
APP_ENV=production
APP_URL=https://pro-ixtla-c7azh2cagpfvfede.mexicocentral-01.azurewebsites.net
APP_TIMEZONE=America/Mexico_City
SESSION_NAME=PROIXTLA_SESSION
DB_HOST=<host MySQL>
DB_PORT=3306
DB_NAME=ixtla01_dep02
DB_USER=<usuario MySQL>
DB_PASS=<contraseña MySQL>
DB_SSL=true
DB_SSL_CA=db/conn/DigiCertGlobalRootG2.crt.pem
DB_CONNECT_TIMEOUT=8
```

No colocar `DB_PASS` dentro del repositorio.

## 3. GitHub

Repository secret requerido:

```text
AZURE_WEBAPP_PUBLISH_PROFILE
```

## 4. Validación posterior al push

Abrir:

```text
/db/web/system/health.php
```

Confirmar:

```text
database.reachable = true
active_schema = ixtla01_dep02
schema_ok = true
```

Luego iniciar sesión con `admin.demo` usando la contraseña inicializada fuera de Git y probar en este orden:

1. Dashboard
2. Departamentos
3. Usuarios y jerarquía
4. Roles y permisos
5. Presupuestos
6. Solicitudes
7. Registro por Tesorería
8. Aclaraciones
9. Bitácora
10. Roles de Cultura para validar aislamiento de datos

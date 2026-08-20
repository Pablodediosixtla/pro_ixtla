# Presupuesto Ixtlahuacán — V2

Aplicación web PHP + MySQL para control presupuestal municipal. Mantiene la estructura del proyecto `pro_ixtla` (`css/`, `js/`, `img/`, `views/`, `db/conn/`, `db/web/`, `sql/` y `.github/workflows/`) y adopta el lenguaje visual de **Ixtla App** mediante sus logotipos, paleta institucional y recursos gráficos.

## Qué cambia en esta V2

- Login visual renovado con identidad de Ixtla App y composición fotográfica.
- `AUTH_MODE=demo`: permite entrar sin consultar `empleado_cuenta`.
- `DATA_MODE=demo`: dashboard, departamentos, sub-items, presupuestos, entradas, salidas, folios y bitácora funcionan con datos de demostración guardados en la sesión.
- Servicios PHP viven dentro de **este mismo proyecto** (`db/web/...`), por lo que no es necesario llamar a servicios publicados en `ixtla-app.com`.
- `AUTH_MODE=db` y `DATA_MODE=db` habilitan la conexión real a Azure MySQL usando variables de entorno.
- Endpoint de diagnóstico: `db/web/system/health.php?check_db=1` (requiere sesión).
- No se incluyen credenciales reales en `.env.example`.

## 1. Modo revisión inmediato

La aplicación usa `demo` por defecto si no existen variables de entorno.

Local:

```bash
cp .env.example .env
php -S localhost:8080
```

Abre:

```text
http://localhost:8080
```

En el login puedes usar **Entrar directamente a revisión**. No se valida ninguna tabla de usuarios y tampoco se requiere conexión a base de datos.

## 2. Modos disponibles

### Revisión completa sin DB

```env
AUTH_MODE=demo
DATA_MODE=demo
```

Sirve para validar UX, navegación y flujo funcional completo.

### Login temporal + datos reales

```env
AUTH_MODE=demo
DATA_MODE=db
```

Permite entrar sin validar usuarios, pero todos los catálogos y movimientos se consultan contra MySQL.

### Producción completa

```env
AUTH_MODE=db
DATA_MODE=db
```

El login valida `empleado_cuenta`, `empleado`, `empleado_rol` y `rol`; el módulo usa las tablas presupuestales reales.

## 3. Variables de Azure

En **App Service > Environment variables / Application settings** configura, cuando quieras usar DB:

```text
AUTH_MODE=db
DATA_MODE=db
DB_HOST=...
DB_PORT=3306
DB_NAME=...
DB_USER=...
DB_PASS=...
DB_SSL=true
DB_SSL_CA=db/conn/DigiCertGlobalRootG2.crt.pem
APP_URL=https://pro-ixtla-c7azh2cagpfvfede.mexicocentral-01.azurewebsites.net
APP_TIMEZONE=America/Mexico_City
```

Para probar primero el backend real manteniendo el login de revisión usa:

```text
AUTH_MODE=demo
DATA_MODE=db
```

## 4. Servicios locales del proyecto

```text
db/web/auth/login.php
db/web/auth/logout.php
db/web/auth/me.php

db/web/dashboard/resumen.php

db/web/departamentos/list.php
db/web/departamentos/employees.php
db/web/departamentos/save.php

db/web/subitems/list.php
db/web/subitems/save.php

db/web/presupuestos/save.php

db/web/movimientos/list.php
db/web/movimientos/get.php
db/web/movimientos/create.php
db/web/movimientos/cancel.php
db/web/movimientos/file.php

db/web/system/health.php
```

Todos se consumen con rutas relativas, por ejemplo:

```text
db/web/auth/login.php
```

por lo que navegador y servicio están en el mismo dominio de `pro-ixtla`.

## 5. DDL

Mantiene los scripts:

1. `sql/00_precheck.sql`
2. `sql/01_presupuesto_schema.sql`
3. `sql/02_seed_catalogos.sql`
4. `sql/03_bootstrap_permiso.sql`

No ejecutes los scripts nuevamente si las tablas ya fueron creadas y contienen información que debas conservar sin antes revisar el DDL.

## 6. GitHub / Azure

El workflow incluido despliega `main` sobre la Web App:

```text
pro-ixtla
```

Requiere el Repository Secret:

```text
AZURE_WEBAPP_PUBLISH_PROFILE
```

## 7. Git

```bash
git status
git add .
git commit -m "V2 modo demo y estilo Ixtla App"
git push origin main
```

## Seguridad

El proyecto nuevo no incluye usuario ni contraseña real de MySQL. Las credenciales deben mantenerse únicamente en `.env` local o en las Application Settings de Azure.

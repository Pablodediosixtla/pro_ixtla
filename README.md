# Presupuesto Ixtlahuacán — V1

Aplicación web PHP 8.2 + MySQL para control presupuestal municipal. La estructura sigue el patrón del proyecto CARPRIX proporcionado: raíz PHP, `css/`, `js/`, `img/`, `views/`, `db/conn/`, `db/web/` y `.github/workflows/`.

## 1. Clonar en Visual Studio Code

```bash
cd ~/Documents

git clone https://github.com/Pablodediosixtla/pro_ixtla.git
cd pro_ixtla
code .

git remote -v
git branch --show-current
```

Si `code .` no está disponible, abre VS Code y usa **File > Open Folder...** sobre `pro_ixtla`.

## 2. Configuración local

```bash
cp .env.example .env
```

Edita `.env` con las credenciales de la misma base Azure MySQL usada por el proyecto DB de referencia. `.env` está ignorado por Git.

Ejecuta los scripts SQL en este orden:

1. `sql/00_precheck.sql`
2. `sql/01_presupuesto_schema.sql`
3. `sql/02_seed_catalogos.sql`
4. `sql/03_bootstrap_permiso.sql` (edita el username antes de ejecutar)

Arranque local:

```bash
php -S localhost:8080
```

Abre `http://localhost:8080`.

## 3. Dependencias existentes reutilizadas

La aplicación reutiliza las tablas encontradas en el proyecto DB proporcionado:

- `departamento`
- `empleado`
- `empleado_cuenta`
- `rol`
- `empleado_rol`

No se recrean para evitar colisiones con la plataforma municipal actual.

## 4. Tablas nuevas del módulo Presupuesto

- `presupuesto_departamento`
- `presupuesto_subitem`
- `presupuesto_usuario_permiso`
- `presupuesto_folio_anual`
- `presupuesto_movimiento`
- `presupuesto_movimiento_archivo`

El disponible se calcula como:

`Presupuesto asignado + Entradas registradas - Salidas registradas`

Los movimientos cancelados no afectan el disponible.

## 5. Azure Web App

Dominio objetivo:

`https://pro-ixtla-c7azh2cagpfvfede.mexicocentral-01.azurewebsites.net`

En **Azure App Service > Configuration > Application settings** agrega las mismas variables de `.env` (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_SSL`, `DB_SSL_CA`, `APP_URL`, `APP_TIMEZONE` y opcionalmente `BUDGET_BOOTSTRAP_ADMIN_USERNAME`).

Para GitHub Actions agrega el secreto:

`AZURE_WEBAPP_PUBLISH_PROFILE`

con el Publish Profile de la Web App. El workflow incluido despliega la rama `main` a la aplicación `pro-ixtla-c7azh2cagpfvfede`.

## 6. Flujo Git recomendado

```bash
git status
git add .
git commit -m "V1 plataforma de presupuesto Ixtlahuacan"
git push origin main
```

## 7. Funcionalidad incluida

- Login usando `empleado_cuenta` y `password_verify`.
- Sesión PHP segura y token CSRF para operaciones de escritura.
- Dashboard con asignado, entradas, salidas, disponible y resumen por departamento.
- Catálogo y edición de departamentos reutilizando la tabla municipal actual.
- Asignación de presupuesto anual por departamento.
- Gestión de sub-items globales o por departamento.
- Registro de entradas y salidas con folio anual automático.
- Evidencia PDF/JPG/JPEG/PNG de hasta 10 MB.
- Bitácora con filtros y detalle de movimientos.
- Cancelación auditada de movimientos para usuarios ADMIN.
- Responsive layout para escritorio y móvil.

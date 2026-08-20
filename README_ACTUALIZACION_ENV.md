# Actualización V2.1 — preserva `.env`

Este paquete **no contiene un archivo `.env`** y no debe sustituir el que ya existe en tu clon local.

## Opción recomendada

Desde la carpeta descomprimida ejecuta:

```bash
./ACTUALIZAR_SIN_TOCAR_ENV.sh /ruta/a/tu/pro_ixtla
```

El script:

- conserva `.git` del repositorio existente;
- conserva `.env` exactamente como está;
- no borra evidencias ya existentes en `uploads/presupuesto/`;
- actualiza el código, vistas, CSS, JS, servicios PHP, SQL e imágenes;
- deja `.env.example` solo como plantilla sin credenciales reales.

Después ejecuta en el repositorio:

```bash
git status
```

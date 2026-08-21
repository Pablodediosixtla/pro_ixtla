# V4.12.0 — Privacidad de perfiles subordinados

- Los perfiles con alcance `PROPIO` ya no reciben ni visualizan totalizadores presupuestales del departamento.
- No pueden abrir Presupuestos, Resumen de departamento ni Detalle de subcategoría financiera.
- En Movimientos ven exclusivamente salidas solicitadas por ellos mismos.
- En Solicitudes ven exclusivamente solicitudes originadas por ellos mismos.
- No ven entradas de dinero del departamento.
- Supervisores (`JERARQUIA`) sí pueden consultar las entradas de su departamento y los movimientos de su jerarquía.
- Directores (`DEPARTAMENTO`) mantienen visión completa de su departamento.
- Se reforzó la misma regla en APIs, archivos/evidencias y aclaraciones.
- Azure App Settings siguen teniendo prioridad sobre `.env`; este ajuste no modifica `.env`.

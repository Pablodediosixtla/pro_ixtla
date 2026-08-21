# V4.16.0

- ADMIN, PRESIDENTE y TESORERIA pueden registrar movimientos en cualquier departamento de su alcance global.
- Los selectores `Solicitado por` y `Otorgado a` cargan exclusivamente usuarios ACTIVOS asignados al departamento seleccionado.
- El backend valida que los usuarios seleccionados pertenezcan al departamento del movimiento.
- Una SALIDA que exceda el disponible ya no se bloquea. La UI muestra una advertencia con disponible, salida y excedente.
- El movimiento se registra y la respuesta del API incluye `warning.code = OVER_BUDGET`; la bitácora registra el sobreejercicio.
- No hay cambios de DDL ni de variables de Azure.

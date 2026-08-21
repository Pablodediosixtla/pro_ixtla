# V4.12.1 - Fix Concepto / uso en Solicitudes

## Causa
`db/web/solicitudes/create.php` enviaba el campo `concepto` a `mysqli::bind_param()` con tipo entero (`i`) en vez de texto (`s`). MySQL recibía `0` aunque el usuario hubiera escrito texto.

## Corrección
- `concepto` se enlaza como `string`.
- `otorgado_a_usuario_id` se enlaza como entero.
- Se valida el error real del INSERT.
- Se relee la solicitud después de crearla y se confirma que `concepto` coincida exactamente con lo enviado.
- No hay cambios de DDL.

## Recuperación de solicitudes ya afectadas
`sql/12_v4121_recover_solicitud_concepto.sql` puede recuperar el texto original desde `bitacora.datos_despues` para solicitudes cuyo concepto quedó como `0`, siempre que exista el evento `SOLICITUD_CREAR` correspondiente.

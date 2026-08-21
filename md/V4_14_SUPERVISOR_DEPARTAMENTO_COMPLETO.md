# V4.14.0 — Supervisor con visión departamental completa

## Corrección

Se unifica la frontera de información del Supervisor con la del departamento al que pertenece:

- Home: totalizadores de Presupuesto, Entradas, Disponible y Ejercido del departamento completo.
- Resumen de departamento: utiliza exactamente los mismos movimientos del departamento.
- Gráfica mensual: incluye todas las entradas y salidas del departamento.
- Subcategorías: incluye movimientos del departamento aunque hayan sido registrados por Admin, Presidencia o Tesorería.
- Movimientos, solicitudes y aclaraciones: el Supervisor puede consultar todos los registros de su departamento.
- Subordinados: mantienen la regla de privacidad propia; solo ven lo que ellos solicitaron.

También se corrigió la resolución del alcance para que una asignación histórica secundaria con alcance GLOBAL no pueda ampliar accidentalmente la visibilidad de un perfil departamental.

No requiere DDL ni cambios de variables en Azure.

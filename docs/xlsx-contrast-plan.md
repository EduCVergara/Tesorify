# Contraste XLSX vs Tesorify

Analisis realizado sobre:

- `Nomina Socios.xlsx`
- `Libro-Banco.xlsx`
- `Cuotas.xlsx`

## Resumen de estructura detectada

### Nomina Socios.xlsx

Hojas relevantes:

- `Socios`: 99 socios base.
- `Cuadrantes`: socios agrupados por sector/cuadrante.
- `Cuotas`: pagos mensuales mezclados dentro del archivo de nomina.
- `Asistencia`, `Hoja1`, `Asistencia Sector`: asistencia a reuniones.
- `Rifa 1`: registro auxiliar de rifa, no pertenece al nucleo de tesoreria.

Columnas principales en `Socios`:

- Nombre y Apellidos
- Fecha de Incorporacion
- Direccion
- Numero/casa/parcela
- Poblacion/sector
- RUT
- Fecha Nacimiento
- Telefono

Resumen detectado:

- 99 socios.
- 67 con RUT.
- 34 con fecha de nacimiento.
- 6 con fecha de incorporacion.
- Sectores: Amarillo, Azul, Rojo, Verde.

### Libro-Banco.xlsx

Hojas relevantes:

- `Hoja1`: libro banco principal.
- `Hoja2`: detalle de gastos/proveedores.
- `Hoja3`: resumen tesoreria 2026 y gastos 2026.

Columnas principales en `Hoja1`:

- Fecha
- Nombre
- Finalidad
- Abonos
- Cargos
- Saldo

Resumen detectado:

- 198 movimientos.
- Abonos totales detectados: 6.383.260.
- Cargos totales detectados: 2.901.378.

Columnas principales en `Hoja2`:

- Fecha
- Proveedor
- Concepto
- Valor

Resumen detectado:

- 27 gastos.
- Monto total detectado: 888.480.

### Cuotas.xlsx

Hojas relevantes:

- `Cuotas2025`: contiene Octubre, Noviembre, Diciembre y tambien meses 2026 en columnas posteriores.
- `CUOTAS2026`: Enero a Diciembre 2026.

Formato actual del Excel:

- Una fila por socio.
- Meses como columnas.
- El valor de la celda representa pago registrado para ese mes.

Resumen detectado:

- `Cuotas2025`: 103 filas de socios, 298 celdas pagadas, total 1.980.000.
- `CUOTAS2026`: 90 filas de socios, 163 celdas pagadas, total 815.000.

## Contraste con el sistema actual

### Lo que Tesorify ya cubre

- Socios con nombre, RUT, direccion, numero de casa, sector, telefono, email, codigo de pago, estado y observaciones.
- Cuotas normalizadas por socio/anio/mes.
- Movimientos de libro banco con fecha, tipo, categoria, descripcion, origen, monto, saldo, fuente, conciliacion, socio y cuota.
- Tipo de deposito en movimientos: transferencia, efectivo, cheque.
- Reportes Excel principales.
- Dashboard con graficos Chart.js.

### Lo que falta para representar toda la informacion de los XLSX

#### Socios

Faltan campos:

- `fecha_incorporacion`
- `fecha_nacimiento`
- `datos_originales` JSON para conservar fila exacta importada.

Campos existentes que ya sirven:

- `direccion`
- `numero_casa`
- `sector`
- `rut`
- `telefono`

Mejora recomendada:

- Generar `codigo_pago` automaticamente como `CASA-{numero_casa}` cuando exista numero.
- Mantener `sector` como select editable con Tom Select.

#### Cuotas

El modelo actual es correcto porque usa una fila por socio/anio/mes, pero falta trazabilidad de importacion:

- `origen_importacion`
- `datos_originales` JSON

Problema de origen:

- Las planillas no indican fecha exacta de pago por cuota, solo que hay monto en la celda del mes.
- Para seeders, una celda con monto debe crear una cuota `pagada`.
- Una celda vacia debe crear una cuota `pendiente` solo si se decide generar el calendario completo de ese periodo.

Regla propuesta para evitar duplicados:

- Usar `socio_id + anio + mes` como llave natural.
- Para 2026, preferir datos de `CUOTAS2026` sobre columnas 2026 duplicadas en `Cuotas2025`.
- Usar `Cuotas2025` principalmente para Octubre-Diciembre 2025.

#### Movimientos / Libro Banco

El modelo actual cubre casi todo `Hoja1`.

Mapeo recomendado:

- Fecha -> `fecha`
- Nombre -> `nombre_origen`
- Finalidad -> `categoria`
- Abonos -> `monto` con `tipo=ingreso`
- Cargos -> `monto` con `tipo=egreso`
- Saldo -> `saldo`
- Fuente -> `importacion_excel`
- Datos originales -> `datos_originales`

Faltan campos opcionales para mayor fidelidad:

- `proveedor` o usar `nombre_origen` para gastos.
- `numero_documento` nullable si en el futuro aparecen comprobantes.

#### Gastos

`Hoja2` puede cargarse como movimientos tipo `egreso`.

Mapeo recomendado:

- Fecha -> `fecha`
- Proveedor -> `nombre_origen`
- Concepto -> `descripcion`
- Valor -> `monto`
- Categoria -> `categoria` derivada del concepto o `Gastos`
- Fuente -> `importacion_excel`

#### Asistencias y cuadrantes

No son estrictamente tesoreria, pero estan en las planillas.

Opciones:

- Fase simple: no crear modulo aun; preservar cuadrante/sector en socios.
- Fase posterior: crear tablas `reuniones` y `asistencias` si el comite necesita registrar asistencia.

Tablas sugeridas solo si se implementa asistencia:

- `reuniones`: fecha, titulo, observaciones.
- `asistencias`: reunion_id, socio_id, estado, datos_originales.

## Migraciones recomendadas antes de seeders reales

1. Agregar campos a `socios`:

```php
$table->date('fecha_incorporacion')->nullable();
$table->date('fecha_nacimiento')->nullable();
$table->json('datos_originales')->nullable();
```

2. Agregar trazabilidad a `cuotas`:

```php
$table->string('origen_importacion')->nullable();
$table->json('datos_originales')->nullable();
```

3. Opcional para movimientos:

```php
$table->string('proveedor')->nullable();
$table->string('numero_documento')->nullable();
```

## Plan de seeders desde XLSX

### Paso 1: guardar archivos fuente

Copiar los XLSX a una ruta versionable o documentada, por ejemplo:

- `database/seeders/data/Nomina Socios.xlsx`
- `database/seeders/data/Libro-Banco.xlsx`
- `database/seeders/data/Cuotas.xlsx`

Si no se desea versionar datos reales, usar `storage/app/imports/seeders` y documentar que se deben copiar manualmente.

### Paso 2: crear seeders especializados

Crear:

- `SociosDesdeExcelSeeder`
- `CuotasDesdeExcelSeeder`
- `MovimientosDesdeExcelSeeder`

Y llamarlos desde `DatabaseSeeder`.

### Paso 3: SociosDesdeExcelSeeder

Leer hoja `Socios`, desde fila 4.

Normalizar:

- Nombre trim.
- RUT trim.
- Telefono como string.
- Fechas Excel/texto a `Y-m-d`.
- Codigo de pago: `CASA-{numero_casa}` si existe, si no `SOCIO-{fila}`.

Upsert por:

- `codigo_pago` cuando exista.
- Alternativa: nombre + numero_casa.

Guardar fila original en `datos_originales`.

### Paso 4: CuotasDesdeExcelSeeder

Leer:

- `Cuotas.xlsx` hoja `Cuotas2025`: Octubre-Diciembre 2025.
- `Cuotas.xlsx` hoja `CUOTAS2026`: Enero-Diciembre 2026.

Por cada socio/fila:

- Resolver socio por numero de casa + nombre aproximado o por codigo `CASA-{numero}`.
- Por cada mes:
  - Si celda tiene monto mayor a 0: crear cuota pagada con ese monto.
  - Si celda vacia y se quiere calendario completo: crear cuota pendiente con monto base configurable.

Upsert por:

- `socio_id`
- `anio`
- `mes`

Guardar:

- `origen_importacion`
- `datos_originales`

### Paso 5: MovimientosDesdeExcelSeeder

Leer `Libro-Banco.xlsx`:

- `Hoja1`: desde fila 6.
- `Hoja2`: desde fila 3 como gastos.
- `Hoja3`: gastos 2026 desde fila 12 si se decide importar esa hoja.

Reglas:

- Si `Abonos > 0`: `tipo=ingreso`.
- Si `Cargos > 0`: `tipo=egreso`.
- Si texto contiene deposito efectivo: `tipo_deposito=efectivo`.
- Si no hay pista: `tipo_deposito=transferencia` para ingresos.
- `estado_conciliacion=pendiente` inicialmente.
- `fuente=importacion_excel`.

Evitar duplicados con hash natural:

- fecha
- monto
- tipo
- descripcion/nombre_origen
- saldo

### Paso 6: conciliacion posterior

No marcar cuotas como conciliadas automaticamente en seeders.

Despues de importar:

- Ejecutar sugerencias de conciliacion.
- Permitir confirmacion humana.
- Asociar movimiento con socio/cuotas.

## Cambios UI recomendados

Ya se agrego Tom Select en formularios y filtros existentes.

Siguientes selects utiles:

- Socio en conciliacion asistida.
- Cuotas pendientes al confirmar conciliacion, multiple select.
- Categoria de movimientos con creacion permitida.
- Sector/cuadrante de socios con creacion permitida.
- Tipo de deposito.
- Estados de cuota y conciliacion.
- Mes/anio en filtros y reportes.

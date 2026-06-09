# Tesorify

Tesorify es una aplicacion web para administrar la tesoreria de un comite vecinal, comite de seguridad, condominio o villa. La primera version prioriza trabajo operativo real: socios, cuotas mensuales, libro banco manual y base para importacion/conciliacion de pagos.

## Stack

- Laravel 13
- PHP 8.3
- MySQL 8
- Blade
- Tailwind CSS
- Alpine.js
- Vite
- Laravel Breeze para autenticacion simple
- Maatwebsite Excel para futuras importaciones/exportaciones

## Estado inicial

Esta base implementa el inicio de la Fase 1:

- Autenticacion con Breeze.
- Dashboard con indicadores de tesoreria.
- Graficos de ingresos, egresos y deudores en el dashboard.
- CRUD basico de socios.
- Generacion masiva de cuotas mensuales para socios activos.
- Listado y marcado manual de cuotas pagadas.
- Libro banco con registro manual de ingresos, egresos, ajustes y tipo de deposito.
- Importador Excel/CSV de movimientos con mapeo manual de columnas y control de duplicados.
- Descarga Excel de libro banco, nomina de cuotas, deuda de cuotas, gastos del mes, deudores e ingresos/gastos por rango de fechas.
- Seeders con datos de prueba.

## Modelo de datos principal

### users

- `rol`: `admin` o `tesorero`.

### socios

- `nombre`
- `rut`
- `direccion`
- `numero_casa`
- `sector`
- `telefono`
- `email`
- `codigo_pago`
- `estado`
- `observaciones`
- soft deletes

### cuotas

- `socio_id`
- `anio`
- `mes`
- `monto`
- `estado`: `pendiente`, `pagada`, `parcial`, `eximida`
- `fecha_pago`
- `movimiento_id`
- `observaciones`
- soft deletes

### movimientos

- `fecha`
- `tipo`: `ingreso`, `egreso`, `ajuste`
- `tipo_deposito`: `transferencia`, `efectivo`, `cheque`
- `categoria`
- `descripcion`
- `nombre_origen`
- `monto`
- `saldo`
- `fuente`: `manual`, `mercado_pago`, `importacion_excel`, `ajuste`
- `estado_conciliacion`: `conciliado`, `pendiente`, `dudoso`
- `socio_id`
- `cuota_id`
- `datos_originales`
- soft deletes

## Instalacion local

1. Crear una base de datos MySQL llamada `tesorify`.
2. Ajustar credenciales en `.env` si tu MySQL no usa `root` sin password.
3. Instalar dependencias:

```bash
composer install
npm install
```

4. Generar clave si hace falta:

```bash
php artisan key:generate
```

5. Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed
```

6. Levantar la aplicacion:

```bash
npm run dev
php artisan serve
```

Usuario de prueba:

- Email: `tesorero@tesorify.test`
- Password: `password`

## Orden de implementacion

### Fase 1

- Completar CRUD de socios con importacion Excel.
- Mejorar generacion y gestion de cuotas.
- Consolidar libro banco manual.

### Fase 2

- Implementada: importador Excel/CSV de movimientos.
- Implementada: mapeo manual de fecha, descripcion, nombre origen, monto, tipo, categoria y saldo.
- Implementada: normalizacion de fechas y montos.
- Implementada: deteccion de duplicados por hash natural y por fecha/tipo/monto/descripcion/origen.
- Implementada: guardado de `datos_originales`.

### Fase 3

- Crear conciliacion asistida.
- Sugerir socio por `codigo_pago`, parecido de nombre, monto y movimientos historicos.
- Confirmar conciliacion manualmente.
- Asociar movimientos a una o varias cuotas.

### Fase 4

- Ampliar reportes con detalle por socio y formatos imprimibles.
- Agregar exportaciones adicionales si aparece una necesidad real.
- Refinar indicadores y graficos del dashboard con datos historicos reales.

## Criterios de desarrollo

- Mantener MVC simple.
- Usar Form Requests para formularios.
- Evitar eliminar registros fisicamente cuando haya trazabilidad contable.
- No asumir webhooks de Mercado Pago para transferencias manuales.
- Priorizar pantallas claras para una persona no tecnica.

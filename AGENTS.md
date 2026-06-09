# AGENTS.md - Tesorify

## Contexto del proyecto

Tesorify es una aplicación web en Laravel para administrar la tesorería de un comité vecinal o comité de seguridad.

El objetivo es reemplazar planillas manuales de:
- Nómina de socios
- Cuotas mensuales
- Libro banco
- Ingresos y egresos
- Conciliación de pagos recibidos por Mercado Pago o ingresados manualmente

La aplicación debe ser simple, mantenible y fácil de usar por personas no técnicas.

## Stack

- PHP 8.2+
- Laravel 12+
- MySQL 8+
- Blade
- Tailwind CSS
- Alpine.js
- Vite
- Laravel Excel / Maatwebsite Excel

## Principios de desarrollo

Aplicar buenas prácticas sin sobreingeniería:

- DRY: evitar duplicar lógica.
- KISS: mantener soluciones simples.
- YAGNI: no crear funcionalidades que aún no son necesarias.
- SOLID: aplicar cuando realmente aporte claridad.
- SLAP: mantener funciones con un solo nivel de abstracción.
- PIT: las pruebas deben ser aisladas y no depender entre sí.

## Reglas importantes

- No romper funcionalidad existente.
- No modificar archivos no relacionados sin necesidad.
- Antes de hacer cambios grandes, explicar brevemente el enfoque.
- Usar migraciones para cambios de base de datos.
- Usar Form Requests para validaciones.
- Usar relaciones Eloquent claras.
- Evitar lógica compleja dentro de las vistas Blade.
- Mantener controladores ordenados.
- Crear servicios solo cuando exista lógica de negocio reutilizable.
- No sobreingenierizar con patrones innecesarios.

## Dominio

Entidades principales:

- Socio
- Cuota
- Movimiento
- Conciliación
- Reporte

Los pagos pueden venir desde:
- Registro manual
- Importación Excel/CSV
- Reporte descargado desde Mercado Pago

No asumir integración automática con Webhooks para transferencias manuales. La primera versión debe funcionar con importación de archivos y conciliación asistida.

## Criterio de conciliación

La conciliación no debe ser automática al principio. El sistema puede sugerir coincidencias, pero el usuario debe confirmar.

Criterios sugeridos:
- codigo_pago del socio encontrado en descripción
- similitud entre nombre_origen y nombre del socio
- monto coincidente con una o varias cuotas pendientes
- historial previo del mismo nombre_origen asociado al socio

## Estilo de interfaz

La interfaz debe priorizar claridad:
- Botones visibles
- Tablas limpias
- Filtros simples
- Confirmaciones antes de acciones sensibles
- Estados visuales para pendiente, conciliado, dudoso, pagado y moroso

## Orden recomendado de trabajo

1. Autenticación.
2. CRUD de socios.
3. Generación de cuotas.
4. Libro banco manual.
5. Importación de movimientos.
6. Conciliación asistida.
7. Reportes.
8. Dashboard.
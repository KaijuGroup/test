# Panel interno de caja (Museo)

Pequeña base en **PHP + HTML + SQLite** para registrar el cierre diario de recepción.

## Qué incluye

- Formulario diario con:
  - Fecha
  - Recepcionista
  - Fondo de caja
  - Efectivo
  - Tarjeta
  - Otros cobros
  - Observaciones
- Persistencia local en SQLite (`data/museum_cashier.sqlite`)
- Tabla con los últimos 20 registros

## Requisitos

- PHP 8+ con extensión `pdo_sqlite` habilitada.

## Ejecutar en local

```bash
php -S 0.0.0.0:8000
```

Después abre: `http://localhost:8000`

## Próximos pasos recomendados

1. Añadir login por usuario/rol (recepción, administración).
2. Bloquear edición de días ya cerrados.
3. Exportar cierres a CSV/Excel.
4. Añadir cuadre automático (`fondo + efectivo esperado`) y alertas de descuadre.

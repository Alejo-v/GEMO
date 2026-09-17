# GEMO — Reportes separados (zoocriadero / terreno)

## Qué cambió

El admin tenía una sola pantalla `reportes.php` y un solo botón "Descargar PDF"
que metía todos los reportes en un mismo archivo. Ahora:

| Página | Contenido |
|---|---|
| `views/admin/reportes.php` | Menú: elige Zoocriadero o Terreno |
| `views/admin/reportes_zoocriadero.php` | Reportes 1, 2 y 3 del zoocriadero |
| `views/admin/reportes_terreno.php` | Reportes 1, 2, 3 y 4 de terreno |

Cada reporte tiene su propio botón **Descargar reporte N** que genera un PDF
solo con ese reporte, respetando los filtros que estén aplicados en pantalla.
Además queda un botón "Descargar los 3/4 en un PDF" por si necesitan el
consolidado.

## Cómo se separan los PDF

Los dos controladores ahora reciben un parámetro `reporte`:

```
controllers/ReporteController.php?accion=pdf&reporte=1|2|3|todos
controllers/ReporteTerrenoController.php?accion=pdf&reporte=1|2|3|4|todos
```

Si no se manda `reporte`, sale `todos` — así que las pantallas de los
coordinadores siguen funcionando igual sin tocarlas.

Nombres de archivo generados:

- `zoocriadero_r1_seguimiento_actividades_2026-08-17_a_2026-09-16.pdf`
- `zoocriadero_r2_nacidos_muertos_por_tanque_...pdf`
- `zoocriadero_r3_tanques_por_zoocriadero_...pdf`
- `terreno_r1_detalle_sitios_2026-09-16.pdf`
- `terreno_r2_por_actividad_...pdf`, `terreno_r3_por_auxiliar_...pdf`, `terreno_r4_por_tipo_deposito_...pdf`

## Instalación

1. **Ejecuta la migración** (obligatoria, si no el admin recibe
   "No tienes permisos para acceder a esa página" al abrir las páginas nuevas):

   ```bash
   psql -U tu_usuario -d bd_gemo -f sql/alter_reportes_separados.sql
   ```

2. **Copia los archivos** respetando las rutas:

   ```
   includes/admin_header.php              (reemplaza)
   models/Tanque.php                      (reemplaza)
   controllers/ReporteController.php      (reemplaza)
   controllers/ReporteTerrenoController.php (reemplaza)
   views/admin/reportes.php               (reemplaza)
   views/admin/reportes_zoocriadero.php   (nuevo)
   views/admin/reportes_terreno.php       (nuevo)
   sql/alter_reportes_separados.sql       (nuevo)
   ```

3. Entra como admin. En el menú lateral aparece una sección **Reportes** con
   tres entradas: Centro de reportes, Reportes zoocriadero y Reportes de terreno.

## Detalle extra

- `Tanque::obtenerReportePorZoocriadero()` ahora acepta un id opcional, así el
  Reporte 3 respeta el filtro "Zoocriadero" de la pantalla en vez de mostrar
  siempre todos. La llamada sin argumentos sigue funcionando igual.
- El subtítulo del PDF de terreno ahora muestra el nombre real de la comuna,
  barrio y tipo de depósito filtrados, en vez de "Comuna filtrada".
- En el PDF de terreno se validan las fechas y se intercambian si vienen al
  revés, igual que ya hacía el de zoocriadero.

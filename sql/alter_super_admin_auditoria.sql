-- =====================================================================
-- Migración: reemplazar Auxiliar Ecosalud por Super Administrador
-- y preparar auditoría de registros de terreno.
-- =====================================================================

BEGIN;

-- Se conserva id_rol = 6 para no romper posibles FK existentes.
UPDATE rol
SET nombre_rol = 'Super Administrador'
WHERE id_rol = 6;

-- Si en una instalación nueva no existiera el rol 6, lo crea.
INSERT INTO rol (id_rol, nombre_rol)
SELECT 6, 'Super Administrador'
WHERE NOT EXISTS (SELECT 1 FROM rol WHERE id_rol = 6);

COMMIT;

-- Nota:
-- seguimiento_terreno ya funciona como historial de actividad de terreno:
-- guarda el usuario que realizó el registro, la fecha y la actividad,
-- además de los datos tomados en campo. La aplicación del Super Administrador
-- consultará ese historial y permitirá filtrarlo.

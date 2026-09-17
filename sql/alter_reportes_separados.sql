-- =====================================================================
-- Migración: separar los reportes del Administrador del Sistema (rol 1)
-- en dos módulos independientes.
--
--   reportes.php              -> ahora es solo un menú (elige el módulo)
--   reportes_zoocriadero.php  -> los 3 reportes del proceso Zoocriadero
--   reportes_terreno.php      -> los 4 reportes del proceso Terreno
--
-- Por qué es necesaria: includes/auth.php valida cada página contra la
-- tabla permiso_rol. Si las dos páginas nuevas no están registradas ahí,
-- el admin recibe "No tienes permisos para acceder a esa página".
--
-- Es segura de ejecutar varias veces (idempotente) y no borra nada.
-- Si permiso_rol todavía no existe (no se aplicó sql/alter_permisos.sql),
-- el bloque se omite sin error.
-- =====================================================================

BEGIN;

DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'permiso_rol') THEN
        INSERT INTO permiso_rol (id_rol, pagina, etiqueta) VALUES
        (1, 'reportes_zoocriadero.php', 'Reportes del zoocriadero'),
        (1, 'reportes_terreno.php',     'Reportes de terreno')
        ON CONFLICT (id_rol, pagina) DO NOTHING;
    END IF;
END $$;

COMMIT;

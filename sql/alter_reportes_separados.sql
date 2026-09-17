
















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

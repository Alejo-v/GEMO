


















BEGIN;




DELETE FROM permiso_rol
WHERE id_rol = 4
  AND pagina IN ('sitios.php', 'depositos.php', 'tipos_deposito.php', 'actividades.php');

DELETE FROM permiso_rol
WHERE id_rol = 3
  AND pagina IN ('zoocriaderos.php', 'tanques.php', 'tipos_tanque.php', 'acciones_zoocriadero.php');




INSERT INTO permiso_rol (id_rol, pagina, etiqueta, activo) VALUES

(5, 'sitios.php',               'Sitios',                    TRUE),
(5, 'depositos.php',            'Depósitos',                 TRUE),
(5, 'tipos_deposito.php',       'Tipos de depósito',         TRUE),
(5, 'actividades.php',          'Actividades',               TRUE),


(2, 'zoocriaderos.php',         'Zoocriaderos',              TRUE),
(2, 'tanques.php',              'Tanques',                   TRUE),
(2, 'tipos_tanque.php',         'Tipos de tanque',           TRUE),
(2, 'acciones_zoocriadero.php', 'Acciones del zoocriadero',  TRUE),


(1, 'sitios.php',               'Sitios',                    TRUE),
(1, 'depositos.php',            'Depósitos',                 TRUE),
(1, 'tipos_deposito.php',       'Tipos de depósito',         TRUE),
(1, 'actividades.php',          'Actividades',               TRUE),
(1, 'zoocriaderos.php',         'Zoocriaderos',              TRUE),
(1, 'tanques.php',              'Tanques',                   TRUE),
(1, 'tipos_tanque.php',         'Tipos de tanque',           TRUE),
(1, 'acciones_zoocriadero.php', 'Acciones del zoocriadero',  TRUE)

ON CONFLICT (id_rol, pagina) DO UPDATE
    SET etiqueta = EXCLUDED.etiqueta,
        activo   = TRUE;

COMMIT;












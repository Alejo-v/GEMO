




BEGIN;


UPDATE rol
SET nombre_rol = 'Super Administrador'
WHERE id_rol = 6;


INSERT INTO rol (id_rol, nombre_rol)
SELECT 6, 'Super Administrador'
WHERE NOT EXISTS (SELECT 1 FROM rol WHERE id_rol = 6);

COMMIT;







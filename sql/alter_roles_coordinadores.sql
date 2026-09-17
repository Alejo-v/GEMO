













BEGIN;


UPDATE public.rol
   SET nombre_rol = 'Coordinador Zoocriadero'
 WHERE id_rol = 2;


UPDATE public.rol
   SET nombre_rol = 'Coordinador Terreno'
 WHERE id_rol = 5;



DELETE FROM public.rol
 WHERE id_rol = 7
   AND NOT EXISTS (SELECT 1 FROM public.usuario WHERE id_rol = 7);





UPDATE public.usuario
   SET id_rol = 2,
       "contraseña" = '$2b$10$p.BwBXwMiOuPt8gk/7ovce8APiDohw0dR2lOUz2l0qGaaYBwz1JrS'
 WHERE id_usuario = 4;

COMMIT;

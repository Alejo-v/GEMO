-- =====================================================================
-- Migración: Roles de coordinadores (versión corregida)
--   - id_rol = 2 "Coordinador Control Biológico"  -> "Coordinador Zoocriadero"
--   - id_rol = 5 "Coordinador Ecosalud"           -> "Coordinador Terreno"
--   - Se elimina el id_rol = 7 creado por error en una migración anterior
--     (si tu base ya lo tiene y NINGÚN usuario lo usa; si algún usuario
--      ya quedó con id_rol = 7, muévelo primero a otro rol antes de
--      ejecutar este script).
--   - Se restablece la contraseña de Santiago Morales (id_usuario = 4)
--     para entregar credenciales de acceso al rol Coordinador Zoocriadero.
-- Ejecutar sobre la base de datos GEMO.
-- Es segura de ejecutar varias veces (idempotente).
-- =====================================================================

BEGIN;

-- 1. Rol 2: Coordinador Zoocriadero
UPDATE public.rol
   SET nombre_rol = 'Coordinador Zoocriadero'
 WHERE id_rol = 2;

-- 2. Rol 5: Coordinador Terreno (antes "Coordinador Ecosalud")
UPDATE public.rol
   SET nombre_rol = 'Coordinador Terreno'
 WHERE id_rol = 5;

-- 3. Elimina el rol 7 sobrante (creado por error en una migración previa),
--    solo si existe y no tiene usuarios asignados.
DELETE FROM public.rol
 WHERE id_rol = 7
   AND NOT EXISTS (SELECT 1 FROM public.usuario WHERE id_rol = 7);

-- 4. Santiago Morales (id_usuario = 4) queda como Coordinador Zoocriadero
--    (ya tenía id_rol = 2; esta línea es solo para dejarlo explícito/seguro)
--    y se le restablece la contraseña.
--    Nueva contraseña: Zoocriadero#2026
UPDATE public.usuario
   SET id_rol = 2,
       "contraseña" = '$2b$10$p.BwBXwMiOuPt8gk/7ovce8APiDohw0dR2lOUz2l0qGaaYBwz1JrS'
 WHERE id_usuario = 4;

COMMIT;

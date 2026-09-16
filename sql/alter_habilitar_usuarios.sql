-- =====================================================================
-- Migración: Habilitar / inhabilitar usuarios
-- Permite al Administrador del Sistema (rol id_rol = 1) deshabilitar el
-- acceso de un usuario sin borrar su registro. Un usuario inhabilitado
-- no podrá iniciar sesión.
-- Ejecutar sobre la base de datos GEMO ya creada con sql/bd_gemo.sql
-- =====================================================================
--
-- Por qué es necesaria: la tabla usuario no tiene ninguna columna de
-- estado. Se agrega "activo" (booleano) con valor por defecto TRUE para
-- que todos los usuarios existentes queden habilitados automáticamente.
-- Es segura de ejecutar aunque ya tengas datos cargados: no borra nada,
-- solo agrega la columna.
-- =====================================================================

BEGIN;

ALTER TABLE public.usuario
    ADD COLUMN IF NOT EXISTS activo BOOLEAN NOT NULL DEFAULT TRUE;

COMMIT;

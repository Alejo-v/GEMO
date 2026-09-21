-- Inhabilitación lógica de comunas y barrios (reemplaza la eliminación física).
-- Ejecutar en pgAdmin (Query Tool) sobre bd_gemo y, si se usa, gemo_test.
-- Es idempotente: se puede correr más de una vez.

BEGIN;

ALTER TABLE comuna
    ADD COLUMN IF NOT EXISTS activo BOOLEAN NOT NULL DEFAULT TRUE;

ALTER TABLE barrio
    ADD COLUMN IF NOT EXISTS activo BOOLEAN NOT NULL DEFAULT TRUE;

COMMIT;

-- =====================================================================
-- Migración: Gestión de permisos por rol (panel visual del Super Admin)
--   - Crea la tabla permiso_rol: una fila por cada página que un rol
--     puede tener habilitada, con un booleano "activo" que el Super
--     Administrador podrá encender/apagar desde views/super_admin/permisos.php.
--   - Carga (seed) exactamente los mismos permisos que hoy están
--     hardcodeados en includes/permisos.php, para que el comportamiento
--     de la aplicación no cambie al aplicar esta migración.
--   - Agrega la página "permisos.php" al rol 6 (Super Administrador),
--     que es la única página nueva que introduce esta funcionalidad.
-- Ejecutar sobre la base de datos GEMO ya creada con sql/bd_gemo.sql
-- Es segura de ejecutar varias veces (idempotente).
-- =====================================================================

BEGIN;

CREATE TABLE IF NOT EXISTS permiso_rol (
    id_permiso  SMALLINT GENERATED ALWAYS AS IDENTITY,
    id_rol      SMALLINT     NOT NULL,
    pagina      VARCHAR(100) NOT NULL,
    etiqueta    VARCHAR(120) NOT NULL,
    activo      BOOLEAN      NOT NULL DEFAULT TRUE,
    CONSTRAINT pk_permiso_rol PRIMARY KEY (id_permiso),
    CONSTRAINT uq_permiso_rol UNIQUE (id_rol, pagina),
    CONSTRAINT fk_permiso_rol_rol FOREIGN KEY (id_rol)
        REFERENCES rol (id_rol)
);

-- Semilla: mismos permisos que hoy existen en includes/permisos.php
INSERT INTO permiso_rol (id_rol, pagina, etiqueta) VALUES
-- 1 - Administrador del Sistema
(1, 'dashboard.php',          'Panel principal'),
(1, 'usuarios.php',           'Usuarios'),
(1, 'registrar_usuario.php',  'Registrar usuario'),
(1, 'reportes.php',           'Reportes'),
(1, 'informacion_personal.php','Información personal'),

-- 2 - Coordinador Zoocriadero
(2, 'inicio.php',             'Inicio'),
(2, 'reportes.php',           'Reportes'),
(2, 'informacion_personal.php','Información personal'),

-- 3 - Auxiliar Zoocriadero
(3, 'inicio.php',             'Inicio'),
(3, 'mis_registros.php',      'Mis registros'),
(3, 'registrar_seguimiento.php','Registrar seguimiento'),
(3, 'informacion_personal.php','Información personal'),

-- 4 - Auxiliar Terreno
(4, 'inicio.php',             'Inicio'),
(4, 'actividades.php',        'Actividades'),
(4, 'depositos.php',          'Depósitos'),
(4, 'estadisticas.php',       'Estadísticas'),
(4, 'mis_registros.php',      'Mis registros'),
(4, 'registrar_seguimiento.php','Registrar seguimiento'),
(4, 'reportes.php',           'Reportes'),
(4, 'sitios.php',             'Sitios'),
(4, 'Formulario_seguimiento.php','Formulario de seguimiento'),
(4, 'informacion_personal.php','Información personal'),

-- 5 - Coordinador Terreno
(5, 'inicio.php',             'Inicio'),
(5, 'reportes.php',           'Reportes'),
(5, 'informacion_personal.php','Información personal'),

-- 6 - Super Administrador (no se administra desde el panel visual,
--     para evitar que el propio Super Admin se bloquee el acceso)
(6, 'dashboard.php',          'Panel principal'),
(6, 'auditoria_terreno.php',  'Auditoría de terreno'),
(6, 'informacion_personal.php','Información personal'),
(6, 'permisos.php',           'Gestión de permisos')

ON CONFLICT (id_rol, pagina) DO NOTHING;

COMMIT;

<?php

require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/../../models/Permiso.php';

class PermisoTest extends IntegrationTestCase
{
    private Permiso $permiso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->exec('TRUNCATE permiso_rol RESTART IDENTITY CASCADE');
        $this->sembrarRolesYPermisos();

        $this->permiso = new Permiso();
    }

    private function sembrarRolesYPermisos(): void
    {
        $this->db->exec(
            "INSERT INTO rol (id_rol, nombre_rol) VALUES
                (2, 'Coordinador'),
                (3, 'Auxiliar'),
                (6, 'Super administrador')"
        );

        $this->db->exec(
            "INSERT INTO permiso_rol (id_rol, pagina, etiqueta, activo) VALUES
                (1, 'usuarios.php',      'Usuarios',      true),
                (1, 'auditoria.php',     'Auditoria',     true),
                (1, 'reportes.php',      'Reportes',      false),
                (2, 'zoocriaderos.php',  'Zoocriaderos',  true),
                (2, 'sitios.php',        'Sitios',        false),
                (3, 'seguimiento.php',   'Seguimiento',   true),
                (6, 'panel.php',         'Panel general', true),
                (6, 'roles.php',         'Roles',         true)"
        );
    }

    private function estadoDe(int $idPermiso): bool
    {
        $stmt = $this->db->prepare('SELECT activo FROM permiso_rol WHERE id_permiso = ?');
        $stmt->execute([$idPermiso]);

        return (bool) $stmt->fetchColumn();
    }

    private function totalActivos(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM permiso_rol WHERE activo = TRUE')->fetchColumn();
    }

    public function testObtenerAgrupadoPorRolAgrupaYOrdenaPorRolYEtiqueta(): void
    {
        $agrupado = $this->permiso->obtenerAgrupadoPorRol();

        $this->assertSame([1, 2, 3], array_keys($agrupado));

        $admin = $agrupado[1];
        $this->assertSame(1, $admin['id_rol']);
        $this->assertSame('Administrador', $admin['nombre_rol']);
        $this->assertSame(['Auditoria', 'Reportes', 'Usuarios'], array_column($admin['permisos'], 'etiqueta'));
        $this->assertSame(['auditoria.php', 'reportes.php', 'usuarios.php'], array_column($admin['permisos'], 'pagina'));
        $this->assertSame([2, 3, 1], array_column($admin['permisos'], 'id_permiso'));
        $this->assertSame([true, false, true], array_column($admin['permisos'], 'activo'));

        $coordinador = $agrupado[2];
        $this->assertSame('Coordinador', $coordinador['nombre_rol']);
        $this->assertSame(['Sitios', 'Zoocriaderos'], array_column($coordinador['permisos'], 'etiqueta'));
        $this->assertSame([5, 4], array_column($coordinador['permisos'], 'id_permiso'));
        $this->assertSame([false, true], array_column($coordinador['permisos'], 'activo'));

        $auxiliar = $agrupado[3];
        $this->assertSame('Auxiliar', $auxiliar['nombre_rol']);
        $this->assertCount(1, $auxiliar['permisos']);
        $this->assertSame('Seguimiento', $auxiliar['permisos'][0]['etiqueta']);
        $this->assertSame('seguimiento.php', $auxiliar['permisos'][0]['pagina']);
    }

    public function testObtenerAgrupadoPorRolExcluyeElRolNoAdministrable(): void
    {
        $agrupado = $this->permiso->obtenerAgrupadoPorRol();

        $this->assertArrayNotHasKey(6, $agrupado);
        $this->assertCount(3, $agrupado);

        $etiquetas = [];
        foreach ($agrupado as $grupo) {
            $etiquetas = array_merge($etiquetas, array_column($grupo['permisos'], 'etiqueta'));
        }
        $this->assertNotContains('Panel general', $etiquetas);
        $this->assertNotContains('Roles', $etiquetas);
    }

    public function testObtenerAgrupadoPorRolNoIncluyeRolesSinPermisos(): void
    {
        $this->db->exec("INSERT INTO rol (id_rol, nombre_rol) VALUES (4, 'Invitado')");

        $agrupado = $this->permiso->obtenerAgrupadoPorRol();

        $this->assertArrayNotHasKey(4, $agrupado);
        $this->assertSame([1, 2, 3], array_keys($agrupado));
    }

    public function testObtenerAgrupadoPorRolSinPermisosDevuelveListaVacia(): void
    {
        $this->db->exec('DELETE FROM permiso_rol');

        $this->assertSame([], $this->permiso->obtenerAgrupadoPorRol());
    }

    public function testContarActivosPorRol(): void
    {
        $this->assertSame(2, $this->permiso->contarActivosPorRol(1));
        $this->assertSame(1, $this->permiso->contarActivosPorRol(2));
        $this->assertSame(1, $this->permiso->contarActivosPorRol(3));
        $this->assertSame(0, $this->permiso->contarActivosPorRol(32000));

        $this->db->exec('UPDATE permiso_rol SET activo = false WHERE id_permiso = 1');

        $this->assertSame(1, $this->permiso->contarActivosPorRol(1));
        $this->assertSame(1, $this->permiso->contarActivosPorRol(2));
    }

    public function testObtenerRolDelPermiso(): void
    {
        $this->assertSame(1, $this->permiso->obtenerRolDelPermiso(1));
        $this->assertSame(2, $this->permiso->obtenerRolDelPermiso(4));
        $this->assertSame(3, $this->permiso->obtenerRolDelPermiso(6));
        $this->assertNull($this->permiso->obtenerRolDelPermiso(32000));
    }

    public function testEsRolAdministrable(): void
    {
        $this->assertFalse($this->permiso->esRolAdministrable(6));

        $this->assertTrue($this->permiso->esRolAdministrable(1));
        $this->assertTrue($this->permiso->esRolAdministrable(2));
        $this->assertTrue($this->permiso->esRolAdministrable(3));
        $this->assertTrue($this->permiso->esRolAdministrable(4));
    }

    public function testActualizarEstadoActivaUnPermisoSinAfectarOtros(): void
    {
        $this->assertFalse($this->estadoDe(3));

        $this->assertTrue($this->permiso->actualizarEstado(3, true));

        $this->assertTrue($this->estadoDe(3));
        $this->assertSame(3, $this->permiso->contarActivosPorRol(1));
        $this->assertFalse($this->estadoDe(5));
        $this->assertTrue($this->estadoDe(1));
    }

    public function testActualizarEstadoConPermisoInexistenteNoModificaNada(): void
    {
        $antes = $this->totalActivos();

        $this->permiso->actualizarEstado(32000, true);

        $this->assertSame($antes, $this->totalActivos());
        $this->assertSame(2, $this->permiso->contarActivosPorRol(1));
        $this->assertSame(1, $this->permiso->contarActivosPorRol(2));
        $this->assertSame(1, $this->permiso->contarActivosPorRol(3));
    }

    public function testActualizarEstadoDesactivaYReactiva(): void
    {
        $this->assertTrue($this->permiso->actualizarEstado(1, false));
        $this->assertFalse($this->estadoDe(1));
        $this->assertSame(1, $this->permiso->contarActivosPorRol(1));

        $this->assertTrue($this->permiso->actualizarEstado(1, true));
        $this->assertTrue($this->estadoDe(1));
        $this->assertSame(2, $this->permiso->contarActivosPorRol(1));
    }
}
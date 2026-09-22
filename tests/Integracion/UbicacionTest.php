<?php

require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/../../models/Ubicacion.php';
require_once __DIR__ . '/../../models/Sitio.php';
require_once __DIR__ . '/../../models/Zoocriadero.php';

/**
 * Requiere haber ejecutado sql/alter_crud_ubicacion.sql sobre gemo_test.
 */
class UbicacionTest extends IntegrationTestCase
{
    private Ubicacion $ubicacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->exec('TRUNCATE sitio_barrio, sitio_terreno, barrio, comuna RESTART IDENTITY CASCADE');
        $this->db->exec("INSERT INTO comuna (id_comuna, nombre) VALUES (1, 'Comuna A'), (2, 'Comuna B')");
        $this->db->exec(
            "INSERT INTO barrio (id_barrio, nombre, id_comuna) VALUES
                (1, 'Barrio Bosque', 1),
                (2, 'Barrio Alameda', 1),
                (3, 'Barrio Centro', 2)"
        );

        $this->ubicacion = new Ubicacion();
    }

    private function estadoBarrio(int $id): bool
    {
        return (bool) $this->db->query("SELECT activo FROM barrio WHERE id_barrio = $id")->fetchColumn();
    }

    private function estadoComuna(int $id): bool
    {
        return (bool) $this->db->query("SELECT activo FROM comuna WHERE id_comuna = $id")->fetchColumn();
    }

    public function testComunasYBarriosNuevosQuedanActivos(): void
    {
        foreach ($this->ubicacion->obtenerComunas() as $c) {
            $this->assertTrue((bool) $c['activo']);
        }
        foreach ($this->ubicacion->obtenerBarrios() as $b) {
            $this->assertTrue((bool) $b['activo']);
        }
    }

    public function testInhabilitarYHabilitarBarrio(): void
    {
        $this->assertTrue($this->ubicacion->cambiarEstadoBarrio(3, false));
        $this->assertFalse($this->estadoBarrio(3));
        $this->assertTrue($this->estadoBarrio(1));

        $this->assertTrue($this->ubicacion->cambiarEstadoBarrio(3, true));
        $this->assertTrue($this->estadoBarrio(3));
    }

    public function testInhabilitarBarrioNoBorraElRegistro(): void
    {
        $this->ubicacion->cambiarEstadoBarrio(2, false);

        $this->assertCount(3, $this->ubicacion->obtenerBarrios());
    }

    public function testComunaConBarriosActivosNoPuedeInhabilitarse(): void
    {
        $this->assertFalse($this->ubicacion->comunaPuedeInhabilitarse(1));

        $this->ubicacion->cambiarEstadoBarrio(1, false);
        $this->assertFalse($this->ubicacion->comunaPuedeInhabilitarse(1));

        $this->ubicacion->cambiarEstadoBarrio(2, false);
        $this->assertTrue($this->ubicacion->comunaPuedeInhabilitarse(1));
    }

    public function testInhabilitarYHabilitarComuna(): void
    {
        $this->ubicacion->cambiarEstadoBarrio(3, false);

        $this->assertTrue($this->ubicacion->cambiarEstadoComuna(2, false));
        $this->assertFalse($this->estadoComuna(2));
        $this->assertCount(1, $this->ubicacion->obtenerComunasActivas());

        $this->assertTrue($this->ubicacion->cambiarEstadoComuna(2, true));
        $this->assertTrue($this->estadoComuna(2));
        $this->assertCount(2, $this->ubicacion->obtenerComunasActivas());
    }

    public function testBarrioDeComunaInhabilitadaNoPuedeHabilitarse(): void
    {
        $this->ubicacion->cambiarEstadoBarrio(3, false);
        $this->ubicacion->cambiarEstadoComuna(2, false);

        $this->assertFalse($this->ubicacion->comunaDelBarrioActiva(3));
        $this->assertTrue($this->ubicacion->comunaDelBarrioActiva(1));
        $this->assertFalse($this->ubicacion->comunaActiva(2));
    }

    public function testBarrioEnSitioActivoNoPuedeInhabilitarse(): void
    {
        $sitio = new Sitio();
        $idSitio = $sitio->crear([':direccion' => 'Finca La Esperanza', ':latitud' => 3.45, ':longitud' => -76.53], [1]);

        $this->assertFalse($this->ubicacion->barrioPuedeInhabilitarse(1));
        $this->assertTrue($this->ubicacion->barrioPuedeInhabilitarse(2));

        $sitio->cambiarEstado($idSitio, false);
        $this->assertTrue($this->ubicacion->barrioPuedeInhabilitarse(1));
    }

    public function testSitiosYZoocriaderosSoloOfrecenBarriosActivos(): void
    {
        $this->ubicacion->cambiarEstadoBarrio(2, false);

        $idsSitio = array_map('intval', array_column((new Sitio())->obtenerBarrios(), 'id_barrio'));
        $idsZoo = array_map('intval', array_column((new Zoocriadero())->obtenerBarrios(), 'id_barrio'));

        $this->assertSame([1, 3], array_values(array_intersect([1, 2, 3], $idsSitio)));
        $this->assertSame([1, 3], array_values(array_intersect([1, 2, 3], $idsZoo)));
        $this->assertFalse((new Sitio())->barrioExiste(2));
        $this->assertFalse((new Zoocriadero())->barrioExiste(2));
        $this->assertTrue((new Sitio())->barrioExiste(1));
    }

    public function testBarriosDeComunaInhabilitadaNoSeOfrecen(): void
    {
        $this->ubicacion->cambiarEstadoBarrio(3, false);
        $this->ubicacion->cambiarEstadoComuna(2, false);

        $ids = array_map('intval', array_column((new Sitio())->obtenerBarrios(), 'id_barrio'));

        $this->assertNotContains(3, $ids);
    }
}

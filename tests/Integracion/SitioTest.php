<?php

require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/../../models/Sitio.php';

class SitioTest extends IntegrationTestCase
{
    private Sitio $sitio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->exec('TRUNCATE sitio_barrio, sitio_terreno, barrio, comuna RESTART IDENTITY CASCADE');
        $this->sembrarUbicaciones();

        $this->sitio = new Sitio();
    }

    
    private function sembrarUbicaciones(): void
    {
        
        $this->db->exec("INSERT INTO comuna (id_comuna, nombre) VALUES (1, 'Comuna A'), (2, 'Comuna B')");
        $this->db->exec(
            "INSERT INTO barrio (id_barrio, nombre, id_comuna) VALUES
                (1, 'Barrio Bosque', 1),
                (2, 'Barrio Alameda', 1),
                (3, 'Barrio Centro', 2)"
        );
    }

    

    private function datos(string $direccion, float $lat = 3.451647, float $lng = -76.531985): array
    {
        return [
            ':direccion' => $direccion,
            ':latitud'   => $lat,
            ':longitud'  => $lng,
        ];
    }

    private function crearSitio(
        string $direccion = 'Finca La Esperanza',
        array $barrios = [],
        float $lat = 3.451647,
        float $lng = -76.531985
    ): int {
        return $this->sitio->crear($this->datos($direccion, $lat, $lng), $barrios);
    }

    private function idsBarrios(int $idSitio): array
    {
        return array_map('intval', $this->sitio->obtenerPorId($idSitio)['barrios']);
    }

    private function contar(string $tabla): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM $tabla")->fetchColumn();
    }

    

    public function testCrearGuardaElSitioSusBarriosYQuedaActivo(): void
    {
        $id = $this->crearSitio('Finca La Esperanza', [1, 2]);

        $this->assertGreaterThan(0, $id);

        $s = $this->sitio->obtenerPorId($id);
        $this->assertNotNull($s);
        $this->assertSame('Finca La Esperanza', $s['direccion']);
        $this->assertEqualsWithDelta(3.451647, (float) $s['latitud'], 0.000001);
        $this->assertEqualsWithDelta(-76.531985, (float) $s['longitud'], 0.000001);
        $this->assertTrue($s['activo'], 'Un sitio nuevo debe quedar activo por defecto');
        $this->assertEqualsCanonicalizing([1, 2], $this->idsBarrios($id));
    }

    public function testCrearSinBarriosEsValido(): void
    {
        $id = $this->crearSitio('Lote sin barrio', []);

        $this->assertSame([], $this->sitio->obtenerPorId($id)['barrios']);
    }

    public function testObtenerPorIdInexistenteDevuelveNull(): void
    {
        $this->assertNull($this->sitio->obtenerPorId(32000));
    }

    

    public function testObtenerTodosTraeBarriosComoTextoYComoIds(): void
    {
        $this->crearSitio('Primero', [1, 2]);
        $this->crearSitio('Segundo', []);

        $todos = $this->sitio->obtenerTodos();

        $this->assertCount(2, $todos);

        
        $this->assertSame('Segundo', $todos[0]['direccion']);
        $this->assertSame('Sin barrio', $todos[0]['barrios']);
        $this->assertSame([], $todos[0]['ids_barrios']);

        
        $this->assertSame('Primero', $todos[1]['direccion']);
        $this->assertSame('Barrio Alameda, Barrio Bosque', $todos[1]['barrios']);
        $this->assertEqualsCanonicalizing([1, 2], $todos[1]['ids_barrios']);
    }

    public function testObtenerActivosExcluyeInactivosYOrdenaPorId(): void
    {
        $id1 = $this->crearSitio('Uno', [3]);
        $id2 = $this->crearSitio('Dos', [1, 2]);
        $id3 = $this->crearSitio('Tres', []);

        
        $this->db->exec("UPDATE sitio_terreno SET activo = false WHERE id_sitio = $id2");

        $activos = $this->sitio->obtenerActivos();

        $this->assertCount(2, $activos);
        $this->assertSame($id1, (int) $activos[0]['id_sitio']);
        $this->assertSame('Barrio Centro', $activos[0]['barrios']);
        $this->assertSame($id3, (int) $activos[1]['id_sitio']);
        $this->assertSame('Sin barrio', $activos[1]['barrios']);
    }

    public function testObtenerBarriosIncluyeComunaYSeOrdenaPorComunaYNombre(): void
    {
        $barrios = $this->sitio->obtenerBarrios();

        $this->assertSame(
            ['Barrio Alameda', 'Barrio Bosque', 'Barrio Centro'],
            array_column($barrios, 'nombre')
        );
        $this->assertSame(
            ['Comuna A', 'Comuna A', 'Comuna B'],
            array_column($barrios, 'comuna')
        );
    }

    public function testBarrioExiste(): void
    {
        $this->assertTrue($this->sitio->barrioExiste(1));
        $this->assertFalse($this->sitio->barrioExiste(32000));
    }

    

    public function testActualizarReemplazaLosDatosYLosBarrios(): void
    {
        $id = $this->crearSitio('Dirección vieja', [1, 2]);

        $ok = $this->sitio->actualizar($id, $this->datos('Dirección nueva', 4.5, -75.5), [3]);

        $this->assertTrue($ok);

        $s = $this->sitio->obtenerPorId($id);
        $this->assertSame('Dirección nueva', $s['direccion']);
        $this->assertEqualsWithDelta(4.5, (float) $s['latitud'], 0.000001);
        $this->assertEqualsWithDelta(-75.5, (float) $s['longitud'], 0.000001);
        
        $this->assertSame([3], $this->idsBarrios($id));
    }

    public function testActualizarSinBarriosQuitaTodosLosVinculos(): void
    {
        $id = $this->crearSitio('Con barrios', [1, 2]);

        $this->sitio->actualizar($id, $this->datos('Con barrios'), []);

        $this->assertSame([], $this->sitio->obtenerPorId($id)['barrios']);
    }

    public function testActualizarSoloAfectaAlSitioIndicado(): void
    {
        $id1 = $this->crearSitio('Uno', [1]);
        $id2 = $this->crearSitio('Dos', [2]);

        $this->sitio->actualizar($id1, $this->datos('Uno editado'), [3]);

        $otro = $this->sitio->obtenerPorId($id2);
        $this->assertSame('Dos', $otro['direccion']);
        $this->assertSame([2], $this->idsBarrios($id2));
    }

    public function testCambiarEstadoDesactivaYReactiva(): void
    {
        
        $id = $this->crearSitio();

        $this->assertTrue($this->sitio->cambiarEstado($id, false));
        $this->assertFalse($this->sitio->obtenerPorId($id)['activo']);

        $this->assertTrue($this->sitio->cambiarEstado($id, true));
        $this->assertTrue($this->sitio->obtenerPorId($id)['activo']);
    }

   

    public function testSiFallaLaCreacionNoQuedaNadaGuardado(): void
    {
        try {
            
            $this->crearSitio('Sitio fallido', [1, 32000]);
            $this->fail('Se esperaba una PDOException por el barrio inexistente');
        } catch (PDOException $e) {
            
        }

        
        $this->assertSame(0, $this->contar('sitio_terreno'));
        $this->assertSame(0, $this->contar('sitio_barrio'));
    }

    public function testSiFallaLaActualizacionSeConservanLosDatosOriginales(): void
    {
        $id = $this->crearSitio('Original', [1]);

        try {
            $this->sitio->actualizar($id, $this->datos('Cambiada'), [32000]);
            $this->fail('Se esperaba una PDOException por el barrio inexistente');
        } catch (PDOException $e) {
            // esperado
        }

        $s = $this->sitio->obtenerPorId($id);
        $this->assertSame('Original', $s['direccion']);
        $this->assertSame([1], $this->idsBarrios($id));
    }

    public function testNoSeGuardaUnaDireccionMayorA50Caracteres(): void
    {
        try {
            $this->crearSitio(str_repeat('A', 51), [1]);
            $this->fail('Se esperaba una PDOException: direccion es varchar(50)');
        } catch (PDOException $e) {
            // esperado
        }

        $this->assertSame(0, $this->contar('sitio_terreno'));
    }

    public function testNoSeGuardanCoordenadasFueraDelRangoDeLaColumna(): void
    {
        try {
            // latitud es numeric(9,6): admite hasta 3 dígitos enteros (máx. 999.999999)
            $this->crearSitio('Coordenadas absurdas', [1], 1000.0, -76.531985);
            $this->fail('Se esperaba una PDOException por desbordamiento numérico');
        } catch (PDOException $e) {
            // esperado
        }

        $this->assertSame(0, $this->contar('sitio_terreno'));
    }
}
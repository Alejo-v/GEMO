<?php

require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/SeguimientoTerreno.php';

class SeguimientoTerrenoTest extends IntegrationTestCase
{
    private SeguimientoTerreno $seguimiento;
    private int $idLuis;
    private int $idMarta;
    private int $idPedro;
    private int $idDep1;
    private int $idDep2;
    private int $idDep3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->exec(
            'TRUNCATE seguimiento_terreno, actividad_terreno, deposito, tipo_deposito,
                      sitio_barrio, sitio_terreno, barrio, comuna RESTART IDENTITY CASCADE'
        );
        $this->sembrarCatalogos();

        $this->seguimiento = new SeguimientoTerreno();
    }

    private function sembrarCatalogos(): void
    {
        $this->db->exec("INSERT INTO rol (id_rol, nombre_rol) VALUES (2, 'Coordinador'), (3, 'Auxiliar')");

        $usuario = new Usuario();
        $this->registrarUsuario($usuario, 2, 'Luis', 'Gómez', '2222222222', 'luis@example.com');
        $this->registrarUsuario($usuario, 3, 'Marta', 'Díaz', '3333333333', 'marta@example.com');
        $this->registrarUsuario($usuario, 2, 'Pedro', 'Ruiz', '4444444444', 'pedro@example.com');
        $this->idLuis  = (int) $usuario->buscarPorCorreo('luis@example.com')['id_usuario'];
        $this->idMarta = (int) $usuario->buscarPorCorreo('marta@example.com')['id_usuario'];
        $this->idPedro = (int) $usuario->buscarPorCorreo('pedro@example.com')['id_usuario'];

        $this->db->exec("INSERT INTO comuna (id_comuna, nombre) VALUES (1, 'Comuna A'), (2, 'Comuna B')");
        $this->db->exec(
            "INSERT INTO barrio (id_barrio, nombre, id_comuna) VALUES
                (1, 'Barrio Bosque', 1),
                (2, 'Barrio Centro', 2)"
        );

        $this->db->exec(
            "INSERT INTO sitio_terreno (id_sitio, direccion, latitud, longitud, activo) VALUES
                (1, 'Finca La Esperanza', 3.451647, -76.531985, true),
                (2, 'Lote El Roble',      3.437000, -76.522000, true),
                (3, 'Sitio cerrado',      3.400000, -76.500000, false)"
        );
        $this->db->exec("INSERT INTO sitio_barrio (id_sitio, id_barrio) VALUES (1, 1), (1, 2)");

        $this->db->exec(
            "INSERT INTO tipo_deposito (id_tipo_deposito, descripcion, activo) VALUES
                (1, 'Tanque elevado', true),
                (2, 'Pozo', true)"
        );
        $this->db->exec(
            "INSERT INTO actividad_terreno (id_actividad_terreno, nombre, activo) VALUES
                (1, 'Inspeccion', true),
                (2, 'Larvicida', true),
                (3, 'Eliminacion', false)"
        );

        $this->idDep1 = $this->insertar("INSERT INTO deposito (id_sitio, id_tipo_deposito) VALUES (2, 1) RETURNING id_deposito");
        $this->idDep2 = $this->insertar("INSERT INTO deposito (id_sitio, id_tipo_deposito) VALUES (1, 2) RETURNING id_deposito");
        $this->idDep3 = $this->insertar("INSERT INTO deposito (id_sitio, id_tipo_deposito) VALUES (1, 1) RETURNING id_deposito");
    }

    private function insertar(string $sql): int
    {
        return (int) $this->db->query($sql)->fetchColumn();
    }

    private function registrarUsuario(Usuario $usuario, int $rol, string $nombres, string $apellidos, string $documento, string $correo): void
    {
        $usuario->registrar([
            ':id_rol'            => $rol,
            ':documento'         => $documento,
            ':nombres'           => $nombres,
            ':apellidos'         => $apellidos,
            ':id_tipo_documento' => 1,
            ':contrasena'        => password_hash('Clave123*', PASSWORD_DEFAULT),
            ':correo'            => $correo,
            ':fecha_nacimiento'  => '1995-05-20',
            ':telefono'          => '3001234567',
        ]);
    }

    private function visita(
        int $idUsuario,
        int $idDeposito,
        string $fecha,
        int $idActividad,
        float $ph,
        float $temperatura,
        int $aedes = 0,
        int $pupas = 0,
        int $culex = 0
    ): bool {
        return $this->seguimiento->registrar([
            'id_deposito'          => $idDeposito,
            'id_usuario'           => $idUsuario,
            'fecha'                => $fecha,
            'id_actividad_terreno' => $idActividad,
            'ph'                   => $ph,
            'temperatura'          => $temperatura,
            'larvas_aedes'         => $aedes,
            'pupas'                => $pupas,
            'larvas_culex'         => $culex,
        ]);
    }

    private function sembrarVisitas(): void
    {
        $this->visita($this->idLuis,  $this->idDep3, '2026-03-01', 1, 7.1, 24.0, 3, 1, 0);
        $this->visita($this->idLuis,  $this->idDep2, '2026-03-15', 2, 7.2, 25.0, 5, 2, 1);
        $this->visita($this->idLuis,  $this->idDep1, '2026-03-15', 1, 7.3, 26.0, 0, 0, 0);
        $this->visita($this->idMarta, $this->idDep3, '2026-04-10', 1, 6.8, 27.5, 8, 4, 2);
        $this->visita($this->idMarta, $this->idDep2, '2026-04-20', 2, 6.9, 28.0, 1, 0, 0);
    }

    private function phs(array $filas): array
    {
        return array_map(fn ($f) => (float) $f['ph'], $filas);
    }

    private function totalSeguimientos(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM seguimiento_terreno')->fetchColumn();
    }

    public function testRegistrarGuardaLaVisitaConTodosSusCampos(): void
    {
        $ok = $this->visita($this->idLuis, $this->idDep3, '2026-03-10', 1, 7.2, 25.5, 4, 2, 1);

        $this->assertTrue($ok);

        $registros = $this->seguimiento->obtenerRegistrosPorUsuario($this->idLuis);
        $this->assertCount(1, $registros);

        $r = $registros[0];
        $this->assertStringStartsWith('2026-03-10', (string) $r['fecha']);
        $this->assertSame('Inspeccion', $r['actividad']);
        $this->assertSame('Tanque elevado', $r['tipo_deposito']);
        $this->assertSame('Finca La Esperanza', $r['sitio_direccion']);
        $this->assertEqualsWithDelta(7.2, (float) $r['ph'], 0.001);
        $this->assertEqualsWithDelta(25.5, (float) $r['temperatura'], 0.001);
        $this->assertEquals(4, $r['larvas_aedes']);
        $this->assertEquals(2, $r['pupas']);
        $this->assertEquals(1, $r['larvas_culex']);
    }

    public function testRegistrarConDepositoInexistenteFalla(): void
    {
        $this->expectException(PDOException::class);

        $this->visita($this->idLuis, 32000, '2026-03-10', 1, 7.0, 25.0);
    }

    public function testRegistrarConUsuarioInexistenteFalla(): void
    {
        $this->expectException(PDOException::class);

        $this->visita(32000, $this->idDep1, '2026-03-10', 1, 7.0, 25.0);
    }

    public function testRegistrarConActividadInexistenteFalla(): void
    {
        $this->expectException(PDOException::class);

        $this->visita($this->idLuis, $this->idDep1, '2026-03-10', 32000, 7.0, 25.0);
    }

    public function testNoSeGuardaUnPhFueraDelRangoDeLaColumna(): void
    {
        try {
            $this->visita($this->idLuis, $this->idDep1, '2026-03-10', 1, 100.0, 25.0);
            $this->fail('Se esperaba una PDOException por desbordamiento numérico');
        } catch (PDOException $e) {
        }

        $this->assertSame(0, $this->totalSeguimientos());
    }

    public function testNoSeGuardaUnConteoDeLarvasMayorAlLimiteDeSmallint(): void
    {
        try {
            $this->visita($this->idLuis, $this->idDep1, '2026-03-10', 1, 7.0, 25.0, 40000);
            $this->fail('Se esperaba una PDOException por valor fuera de rango');
        } catch (PDOException $e) {
        }

        $this->assertSame(0, $this->totalSeguimientos());
    }

    public function testRegistrosPorUsuarioSoloTraeLosSuyosOrdenadosPorFechaDesc(): void
    {
        $this->sembrarVisitas();

        $registros = $this->seguimiento->obtenerRegistrosPorUsuario($this->idLuis);

        $this->assertCount(3, $registros);
        $this->assertEquals([7.3, 7.2, 7.1], $this->phs($registros));
        $this->assertSame('Lote El Roble', $registros[0]['sitio_direccion']);
    }

    public function testRegistrosPorUsuarioSinVisitasDevuelveListaVacia(): void
    {
        $this->sembrarVisitas();

        $this->assertSame([], $this->seguimiento->obtenerRegistrosPorUsuario($this->idPedro));
    }

    public function testConteoPorTipoDepositoAgrupaVariosDepositosDelMismoTipo(): void
    {
        $this->sembrarVisitas();

        $conteo = $this->seguimiento->obtenerConteoPorTipoDeposito($this->idLuis);

        $this->assertSame(['Pozo', 'Tanque elevado'], array_column($conteo, 'etiqueta'));
        $this->assertEquals([1, 2], array_column($conteo, 'total'));
    }

    public function testConteoPorActividadNoMezclaUsuarios(): void
    {
        $this->sembrarVisitas();

        $luis = $this->seguimiento->obtenerConteoPorActividad($this->idLuis);
        $this->assertSame(['Inspeccion', 'Larvicida'], array_column($luis, 'etiqueta'));
        $this->assertEquals([2, 1], array_column($luis, 'total'));

        $marta = $this->seguimiento->obtenerConteoPorActividad($this->idMarta);
        $this->assertEquals([1, 1], array_column($marta, 'total'));
    }

    public function testObtenerSitiosSoloActivosConBarriosYComunas(): void
    {
        $sitios = $this->seguimiento->obtenerSitios();

        $this->assertCount(2, $sitios);

        $this->assertSame('Finca La Esperanza', $sitios[0]['direccion']);
        $this->assertSame('Barrio Bosque, Barrio Centro', $sitios[0]['barrios']);
        $this->assertSame('Comuna A, Comuna B', $sitios[0]['comunas']);

        $this->assertSame('Lote El Roble', $sitios[1]['direccion']);
        $this->assertSame('Sin barrio', $sitios[1]['barrios']);
        $this->assertSame('Sin comuna', $sitios[1]['comunas']);
    }

    public function testObtenerDepositosSoloActivosOrdenadosPorSitioYDeposito(): void
    {
        $depositos = $this->seguimiento->obtenerDepositos();
        $this->assertSame(
            [$this->idDep2, $this->idDep3, $this->idDep1],
            array_map('intval', array_column($depositos, 'id_deposito'))
        );
        $this->assertSame('Pozo', $depositos[0]['tipo_deposito']);

        $this->db->exec("UPDATE deposito SET activo = false WHERE id_deposito = {$this->idDep3}");

        $depositos = $this->seguimiento->obtenerDepositos();
        $this->assertSame(
            [$this->idDep2, $this->idDep1],
            array_map('intval', array_column($depositos, 'id_deposito'))
        );
    }

    public function testObtenerActividadesSoloIncluyeLasActivas(): void
    {
        $actividades = $this->seguimiento->obtenerActividades();

        $this->assertEquals([1, 2], array_column($actividades, 'id_actividad_terreno'));
        $this->assertSame(['Inspeccion', 'Larvicida'], array_column($actividades, 'nombre'));
    }

    public function testDepositoExisteYActividadExiste(): void
    {
        $this->assertTrue($this->seguimiento->depositoExiste($this->idDep1));
        $this->assertFalse($this->seguimiento->depositoExiste(32000));

        $this->assertTrue($this->seguimiento->actividadExiste(1));
        $this->assertFalse($this->seguimiento->actividadExiste(32000));
    }

    public function testAuditoriaSinFiltrosTraeTodoConSusDatosRelacionados(): void
    {
        $this->sembrarVisitas();

        $filas = $this->seguimiento->obtenerAuditoriaTerreno();

        $this->assertCount(5, $filas);
        $this->assertEquals([6.9, 6.8, 7.3, 7.2, 7.1], $this->phs($filas));

        $v5 = $filas[0];
        $this->assertSame($this->idMarta, (int) $v5['id_usuario']);
        $this->assertSame('Marta Díaz', $v5['usuario']);
        $this->assertSame('marta@example.com', $v5['correo']);
        $this->assertEquals('3001234567', $v5['telefono']);
        $this->assertSame('Auxiliar', $v5['nombre_rol']);
        $this->assertSame('Larvicida', $v5['actividad']);
        $this->assertSame('Finca La Esperanza', $v5['sitio']);
        $this->assertSame($this->idDep2, (int) $v5['id_deposito']);
        $this->assertSame('Pozo', $v5['tipo_deposito']);
        $this->assertEquals(1, $v5['larvas_aedes']);
    }

    public function testAuditoriaFiltraPorRangoDeFechasIncluyendoLosExtremos(): void
    {
        $this->sembrarVisitas();

        $rango = $this->seguimiento->obtenerAuditoriaTerreno('2026-03-15', '2026-04-10');
        $this->assertEquals([6.8, 7.3, 7.2], $this->phs($rango));

        $desde = $this->seguimiento->obtenerAuditoriaTerreno('2026-04-01');
        $this->assertEquals([6.9, 6.8], $this->phs($desde));

        $hasta = $this->seguimiento->obtenerAuditoriaTerreno(null, '2026-03-15');
        $this->assertEquals([7.3, 7.2, 7.1], $this->phs($hasta));
    }

    public function testAuditoriaFiltraPorUsuarioYPorActividad(): void
    {
        $this->sembrarVisitas();

        $deMarta = $this->seguimiento->obtenerAuditoriaTerreno(null, null, $this->idMarta);
        $this->assertEquals([6.9, 6.8], $this->phs($deMarta));

        $larvicida = $this->seguimiento->obtenerAuditoriaTerreno(null, null, null, 2);
        $this->assertEquals([6.9, 7.2], $this->phs($larvicida));
    }

    public function testAuditoriaCombinaVariosFiltrosAlMismoTiempo(): void
    {
        $this->sembrarVisitas();

        $a = $this->seguimiento->obtenerAuditoriaTerreno(null, null, $this->idLuis, 2);
        $this->assertEquals([7.2], $this->phs($a));

        $b = $this->seguimiento->obtenerAuditoriaTerreno('2026-03-10', '2026-03-31', $this->idLuis, 1);
        $this->assertEquals([7.3], $this->phs($b));

        $c = $this->seguimiento->obtenerAuditoriaTerreno('2027-01-01');
        $this->assertSame([], $c);
    }

    public function testAuditoriaIgnoraFiltrosVaciosCerosONulos(): void
    {
        $this->sembrarVisitas();

        $this->assertCount(5, $this->seguimiento->obtenerAuditoriaTerreno('', '', 0, 0));
        $this->assertCount(5, $this->seguimiento->obtenerAuditoriaTerreno(null, null, null, null));
    }

    public function testUsuariosConRegistrosSoloIncluyeALosQueHanVisitado(): void
    {
        $this->sembrarVisitas();

        $usuarios = $this->seguimiento->obtenerUsuariosConRegistrosTerreno();

        $this->assertSame(['Luis Gómez', 'Marta Díaz'], array_column($usuarios, 'nombre'));
    }
}
<?php

require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/SeguimientoZoocriadero.php';

class SeguimientoZoocriaderoTest extends IntegrationTestCase
{
    private SeguimientoZoocriadero $seguimiento;
    private int $idLuis;
    private int $idMarta;
    private int $idPedro;
    private int $idZooNorte;
    private int $idZooSur;
    private int $idT1;
    private int $idT2;
    private int $idT3;
    private int $idT4;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->exec(
            'TRUNCATE seguimiento_zoo_actividad, seguimiento_zoocriadero, actividad_zoocriadero,
                      tanque, tipo_tanque, zoocriadero, barrio, comuna RESTART IDENTITY CASCADE'
        );
        $this->sembrarCatalogos();

        $this->seguimiento = new SeguimientoZoocriadero();
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

        $this->db->exec("INSERT INTO comuna (id_comuna, nombre) VALUES (1, 'Comuna A')");
        $this->db->exec("INSERT INTO barrio (id_barrio, nombre, id_comuna) VALUES (1, 'Barrio Centro', 1)");

        $this->idZooNorte = $this->insertar(
            "INSERT INTO zoocriadero (direccion, id_usuario, id_barrio)
             VALUES ('Zoocriadero Norte', {$this->idLuis}, 1) RETURNING id_zoocriadero"
        );
        $this->idZooSur = $this->insertar(
            "INSERT INTO zoocriadero (direccion, id_usuario, id_barrio)
             VALUES ('Zoocriadero Sur', {$this->idMarta}, 1) RETURNING id_zoocriadero"
        );

        $this->db->exec(
            "INSERT INTO tipo_tanque (id_tipo_tanque, descripcion, activo) VALUES
                (1, 'Geomembrana', true),
                (2, 'Concreto', true)"
        );

        $this->idT1 = $this->insertar(
            "INSERT INTO tanque (id_zoocriadero, id_tipo_tanque, estado)
             VALUES ({$this->idZooNorte}, 1, 'Activo') RETURNING id_tanque"
        );
        $this->idT2 = $this->insertar(
            "INSERT INTO tanque (id_zoocriadero, id_tipo_tanque, estado)
             VALUES ({$this->idZooNorte}, 2, 'Mantenimiento') RETURNING id_tanque"
        );
        $this->idT3 = $this->insertar(
            "INSERT INTO tanque (id_zoocriadero, id_tipo_tanque, estado)
             VALUES ({$this->idZooSur}, 2, 'Activo') RETURNING id_tanque"
        );
        $this->idT4 = $this->insertar(
            "INSERT INTO tanque (id_zoocriadero, id_tipo_tanque, estado)
             VALUES ({$this->idZooSur}, 1, 'Activo') RETURNING id_tanque"
        );

        $this->db->exec(
            "INSERT INTO actividad_zoocriadero (id_actividad_zoocriadero, nombre, activo) VALUES
                (1, 'Alimentacion', true),
                (2, 'Limpieza', true),
                (3, 'Cosecha', false)"
        );
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

    private function datos(
        int $idTanque,
        int $idUsuario,
        string $fecha,
        float $ph = 7.0,
        int $vivos = 0,
        int $machos = 0,
        int $hembras = 0,
        string $observaciones = ''
    ): array {
        return [
            ':id_tanque'        => $idTanque,
            ':id_usuario'       => $idUsuario,
            ':fecha'            => $fecha,
            ':ph'               => $ph,
            ':temperatura'      => 26.5,
            ':cloro'            => 0.75,
            ':alevines_nacidos' => $vivos,
            ':muertos_macho'    => $machos,
            ':muertos_hembra'   => $hembras,
            ':observaciones'    => $observaciones,
        ];
    }

    private function registro(
        int $idTanque,
        int $idUsuario,
        string $fecha,
        float $ph,
        int $vivos,
        int $machos,
        int $hembras,
        array $actividades = [],
        string $observaciones = ''
    ): bool {
        return $this->seguimiento->registrar(
            $this->datos($idTanque, $idUsuario, $fecha, $ph, $vivos, $machos, $hembras, $observaciones),
            $actividades
        );
    }

    private function sembrarVisitas(): void
    {
        $this->registro($this->idT1, $this->idLuis,  '2026-03-01', 7.1, 100, 2, 3, [1], 'Primera visita');
        $this->registro($this->idT1, $this->idLuis,  '2026-03-15', 7.2, 50, 1, 1, [1, 2]);
        $this->registro($this->idT3, $this->idLuis,  '2026-03-15', 7.3, 30, 0, 0, []);
        $this->registro($this->idT3, $this->idMarta, '2026-04-10', 6.8, 200, 5, 5, [2]);
        $this->registro($this->idT4, $this->idMarta, '2026-04-20', 6.9, 80, 2, 0, [1, 2], 'Sin novedad');
    }

    private function phs(array $filas): array
    {
        return array_map(fn ($f) => (float) $f['ph'], $filas);
    }

    private function ids(array $filas, string $columna): array
    {
        return array_map('intval', array_column($filas, $columna));
    }

    private function fechas(array $filas): array
    {
        return array_map(fn ($f) => substr((string) $f['fecha'], 0, 10), $filas);
    }

    private function contar(string $tabla): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM $tabla")->fetchColumn();
    }

    private function verificarQueFallaSinGuardar(array $datos, array $actividades = []): void
    {
        try {
            $this->seguimiento->registrar($datos, $actividades);
            $this->fail('Se esperaba una PDOException');
        } catch (PDOException $e) {
        }

        $this->assertSame(0, $this->contar('seguimiento_zoocriadero'));
        $this->assertSame(0, $this->contar('seguimiento_zoo_actividad'));
    }

    public function testRegistrarGuardaLaVisitaConSusDatosYActividades(): void
    {
        $datos = $this->datos($this->idT1, $this->idLuis, '2026-03-10', 7.2, 120, 3, 2, 'Todo normal');

        $this->assertTrue($this->seguimiento->registrar($datos, [1, 2]));

        $filas = $this->seguimiento->obtenerRegistrosFiltrados([]);
        $this->assertCount(1, $filas);

        $r = $filas[0];
        $this->assertStringStartsWith('2026-03-10', (string) $r['fecha']);
        $this->assertSame($this->idT1, (int) $r['id_tanque']);
        $this->assertSame('Zoocriadero Norte', $r['zoocriadero']);
        $this->assertSame('Geomembrana', $r['tipo_tanque']);
        $this->assertSame('Luis Gómez', $r['registrado_por']);
        $this->assertEqualsWithDelta(7.2, (float) $r['ph'], 0.001);
        $this->assertEqualsWithDelta(26.5, (float) $r['temperatura'], 0.001);
        $this->assertEqualsWithDelta(0.75, (float) $r['cloro'], 0.001);
        $this->assertEquals(120, $r['alevines_nacidos']);
        $this->assertEquals(3, $r['muertos_macho']);
        $this->assertEquals(2, $r['muertos_hembra']);
        $this->assertEquals(5, $r['muertos_total']);
        $this->assertSame('Todo normal', $r['observaciones']);
        $this->assertSame('Alimentacion, Limpieza', $r['actividades']);
        $this->assertSame(2, $this->contar('seguimiento_zoo_actividad'));
    }

    public function testRegistrarSinActividadesGuardaSoloLaVisita(): void
    {
        $this->assertTrue(
            $this->seguimiento->registrar($this->datos($this->idT1, $this->idLuis, '2026-03-10'), [])
        );

        $this->assertSame(1, $this->contar('seguimiento_zoocriadero'));
        $this->assertSame(0, $this->contar('seguimiento_zoo_actividad'));
        $this->assertSame('—', $this->seguimiento->obtenerRegistrosFiltrados([])[0]['actividades']);
    }

    public function testRegistrarIgnoraActividadesRepetidas(): void
    {
        $this->assertTrue(
            $this->seguimiento->registrar($this->datos($this->idT1, $this->idLuis, '2026-03-10'), [1, 1, 2, 2])
        );

        $this->assertSame(2, $this->contar('seguimiento_zoo_actividad'));
    }

    public function testSiUnaActividadNoExisteNoQuedaNadaGuardado(): void
    {
        $this->verificarQueFallaSinGuardar(
            $this->datos($this->idT1, $this->idLuis, '2026-03-10'),
            [1, 32000]
        );
    }

    public function testRegistrarConTanqueInexistenteFalla(): void
    {
        $this->verificarQueFallaSinGuardar(
            $this->datos(32000, $this->idLuis, '2026-03-10'),
            [1]
        );
    }

    public function testRegistrarConUsuarioInexistenteFalla(): void
    {
        $this->verificarQueFallaSinGuardar(
            $this->datos($this->idT1, 32000, '2026-03-10'),
            [1]
        );
    }

    public function testNoSeGuardaUnPhFueraDelRangoDeLaColumna(): void
    {
        $this->verificarQueFallaSinGuardar(
            $this->datos($this->idT1, $this->idLuis, '2026-03-10', 100.0)
        );
    }

    public function testNoSeGuardaUnCloroFueraDelRangoDeLaColumna(): void
    {
        $datos = $this->datos($this->idT1, $this->idLuis, '2026-03-10');
        $datos[':cloro'] = 100.0;

        $this->verificarQueFallaSinGuardar($datos);
    }

    public function testNoSeGuardaUnConteoDeAlevinesMayorAlLimiteDeSmallint(): void
    {
        $this->verificarQueFallaSinGuardar(
            $this->datos($this->idT1, $this->idLuis, '2026-03-10', 7.0, 40000)
        );
    }

    public function testObtenerTanquesSoloIncluyeLosActivosEnEstadoActivo(): void
    {
        $tanques = $this->seguimiento->obtenerTanques();

        $this->assertSame([$this->idT1, $this->idT3, $this->idT4], $this->ids($tanques, 'id_tanque'));
        $this->assertSame(['Geomembrana', 'Concreto', 'Geomembrana'], array_column($tanques, 'tipo_tanque'));
        $this->assertSame(['Activo', 'Activo', 'Activo'], array_column($tanques, 'estado'));

        $this->db->exec("UPDATE tanque SET activo = false WHERE id_tanque = {$this->idT4}");

        $this->assertSame(
            [$this->idT1, $this->idT3],
            $this->ids($this->seguimiento->obtenerTanques(), 'id_tanque')
        );
    }

    public function testObtenerActividadesSoloIncluyeLasActivas(): void
    {
        $actividades = $this->seguimiento->obtenerActividades();

        $this->assertEquals([1, 2], array_column($actividades, 'id_actividad_zoocriadero'));
        $this->assertSame(['Alimentacion', 'Limpieza'], array_column($actividades, 'nombre'));
    }

    public function testTanqueExiste(): void
    {
        $this->assertTrue($this->seguimiento->tanqueExiste($this->idT1));
        $this->assertFalse($this->seguimiento->tanqueExiste(32000));
    }

    public function testActividadesValidas(): void
    {
        $this->assertTrue($this->seguimiento->actividadesValidas([]));
        $this->assertTrue($this->seguimiento->actividadesValidas([1, 2]));
        $this->assertTrue($this->seguimiento->actividadesValidas([1, 1, 2]));
        $this->assertTrue($this->seguimiento->actividadesValidas(['1', '2']));
        $this->assertFalse($this->seguimiento->actividadesValidas([1, 32000]));
        $this->assertFalse($this->seguimiento->actividadesValidas([32000]));
    }

    public function testObtenerZoocriaderosOrdenadosPorId(): void
    {
        $zoos = $this->seguimiento->obtenerZoocriaderos();

        $this->assertSame([$this->idZooNorte, $this->idZooSur], $this->ids($zoos, 'id_zoocriadero'));
        $this->assertSame(['Zoocriadero Norte', 'Zoocriadero Sur'], array_column($zoos, 'direccion'));
    }

    public function testRegistrosFiltradosSinFiltrosTraeTodoOrdenadoPorFechaDesc(): void
    {
        $this->sembrarVisitas();

        $filas = $this->seguimiento->obtenerRegistrosFiltrados([]);

        $this->assertCount(5, $filas);
        $this->assertEquals([6.9, 6.8, 7.3, 7.2, 7.1], $this->phs($filas));

        $v5 = $filas[0];
        $this->assertSame($this->idZooSur, (int) $v5['id_zoocriadero']);
        $this->assertSame('Zoocriadero Sur', $v5['zoocriadero']);
        $this->assertSame($this->idT4, (int) $v5['id_tanque']);
        $this->assertSame('Geomembrana', $v5['tipo_tanque']);
        $this->assertSame('Marta Díaz', $v5['registrado_por']);
        $this->assertSame('Alimentacion, Limpieza', $v5['actividades']);
        $this->assertEquals(80, $v5['alevines_nacidos']);
        $this->assertEquals(2, $v5['muertos_total']);
        $this->assertSame('Sin novedad', $v5['observaciones']);

        $this->assertSame('—', $filas[2]['actividades']);
    }

    public function testRegistrosFiltradosPorRangoDeFechasIncluyendoLosExtremos(): void
    {
        $this->sembrarVisitas();

        $rango = $this->seguimiento->obtenerRegistrosFiltrados([
            'fecha_inicio' => '2026-03-15',
            'fecha_fin'    => '2026-04-10',
        ]);
        $this->assertEquals([6.8, 7.3, 7.2], $this->phs($rango));

        $desde = $this->seguimiento->obtenerRegistrosFiltrados(['fecha_inicio' => '2026-04-01']);
        $this->assertEquals([6.9, 6.8], $this->phs($desde));

        $hasta = $this->seguimiento->obtenerRegistrosFiltrados(['fecha_fin' => '2026-03-15']);
        $this->assertEquals([7.3, 7.2, 7.1], $this->phs($hasta));
    }

    public function testRegistrosFiltradosPorZoocriadero(): void
    {
        $this->sembrarVisitas();

        $sur = $this->seguimiento->obtenerRegistrosFiltrados(['id_zoocriadero' => $this->idZooSur]);
        $this->assertEquals([6.9, 6.8, 7.3], $this->phs($sur));

        $norte = $this->seguimiento->obtenerRegistrosFiltrados(['id_zoocriadero' => (string) $this->idZooNorte]);
        $this->assertEquals([7.2, 7.1], $this->phs($norte));

        $this->assertSame([], $this->seguimiento->obtenerRegistrosFiltrados(['id_zoocriadero' => 32000]));
    }

    public function testRegistrosFiltradosPorActividadNoDuplicaFilas(): void
    {
        $this->sembrarVisitas();

        $limpieza = $this->seguimiento->obtenerRegistrosFiltrados(['id_actividad' => 2]);
        $this->assertEquals([6.9, 6.8, 7.2], $this->phs($limpieza));

        $alimentacion = $this->seguimiento->obtenerRegistrosFiltrados(['id_actividad' => 1]);
        $this->assertEquals([6.9, 7.2, 7.1], $this->phs($alimentacion));
    }

    public function testRegistrosFiltradosCombinaVariosFiltros(): void
    {
        $this->sembrarVisitas();

        $a = $this->seguimiento->obtenerRegistrosFiltrados([
            'id_zoocriadero' => $this->idZooSur,
            'id_actividad'   => 2,
        ]);
        $this->assertEquals([6.9, 6.8], $this->phs($a));

        $b = $this->seguimiento->obtenerRegistrosFiltrados([
            'id_zoocriadero' => $this->idZooNorte,
            'id_actividad'   => 2,
            'fecha_inicio'   => '2026-03-10',
            'fecha_fin'      => '2026-03-31',
        ]);
        $this->assertEquals([7.2], $this->phs($b));

        $c = $this->seguimiento->obtenerRegistrosFiltrados([
            'id_zoocriadero' => $this->idZooSur,
            'id_actividad'   => 1,
        ]);
        $this->assertEquals([6.9], $this->phs($c));

        $d = $this->seguimiento->obtenerRegistrosFiltrados([
            'id_zoocriadero' => $this->idZooNorte,
            'fecha_inicio'   => '2026-04-01',
        ]);
        $this->assertSame([], $d);
    }

    public function testRegistrosFiltradosIgnoraFiltrosVaciosCerosONulos(): void
    {
        $this->sembrarVisitas();

        $this->assertCount(5, $this->seguimiento->obtenerRegistrosFiltrados([]));
        $this->assertCount(5, $this->seguimiento->obtenerRegistrosFiltrados([
            'fecha_inicio'   => '',
            'fecha_fin'      => '',
            'id_zoocriadero' => 0,
            'id_actividad'   => 0,
        ]));
        $this->assertCount(5, $this->seguimiento->obtenerRegistrosFiltrados([
            'fecha_inicio'   => null,
            'fecha_fin'      => null,
            'id_zoocriadero' => '',
            'id_actividad'   => '',
        ]));
    }

    public function testResumenFiltradoSinFiltrosSumaTodo(): void
    {
        $this->sembrarVisitas();

        $r = $this->seguimiento->obtenerResumenFiltrado([]);

        $this->assertEquals(5, $r['total_registros']);
        $this->assertEquals(460, $r['total_vivos']);
        $this->assertEquals(10, $r['total_muertos_macho']);
        $this->assertEquals(9, $r['total_muertos_hembra']);
        $this->assertEquals(19, $r['total_muertos']);
    }

    public function testResumenFiltradoConFiltrosNoDuplicaPorActividades(): void
    {
        $this->sembrarVisitas();

        $sur = $this->seguimiento->obtenerResumenFiltrado(['id_zoocriadero' => $this->idZooSur]);
        $this->assertEquals(3, $sur['total_registros']);
        $this->assertEquals(310, $sur['total_vivos']);
        $this->assertEquals(7, $sur['total_muertos_macho']);
        $this->assertEquals(5, $sur['total_muertos_hembra']);
        $this->assertEquals(12, $sur['total_muertos']);

        $limpieza = $this->seguimiento->obtenerResumenFiltrado(['id_actividad' => 2]);
        $this->assertEquals(3, $limpieza['total_registros']);
        $this->assertEquals(330, $limpieza['total_vivos']);
        $this->assertEquals(8, $limpieza['total_muertos_macho']);
        $this->assertEquals(6, $limpieza['total_muertos_hembra']);
        $this->assertEquals(14, $limpieza['total_muertos']);

        $unaVisita = $this->seguimiento->obtenerResumenFiltrado([
            'id_zoocriadero' => $this->idZooNorte,
            'fecha_inicio'   => '2026-03-10',
            'fecha_fin'      => '2026-03-31',
        ]);
        $this->assertEquals(1, $unaVisita['total_registros']);
        $this->assertEquals(50, $unaVisita['total_vivos']);
    }

    public function testResumenFiltradoSinCoincidenciasDevuelveCerosYNoNulos(): void
    {
        $this->sembrarVisitas();

        $r = $this->seguimiento->obtenerResumenFiltrado(['id_zoocriadero' => 32000]);

        $this->assertNotNull($r['total_vivos']);
        $this->assertEquals(0, $r['total_registros']);
        $this->assertEquals(0, $r['total_vivos']);
        $this->assertEquals(0, $r['total_muertos_macho']);
        $this->assertEquals(0, $r['total_muertos_hembra']);
        $this->assertEquals(0, $r['total_muertos']);
    }

    public function testSerieDiariaAgrupaPorFechaYRespetaLosFiltros(): void
    {
        $this->sembrarVisitas();

        $serie = $this->seguimiento->obtenerSerieDiariaFiltrada([]);
        $this->assertSame(['2026-03-01', '2026-03-15', '2026-04-10', '2026-04-20'], $this->fechas($serie));
        $this->assertEquals([100, 80, 200, 80], array_column($serie, 'vivos'));
        $this->assertEquals([5, 2, 10, 2], array_column($serie, 'muertos'));

        $sur = $this->seguimiento->obtenerSerieDiariaFiltrada(['id_zoocriadero' => $this->idZooSur]);
        $this->assertSame(['2026-03-15', '2026-04-10', '2026-04-20'], $this->fechas($sur));
        $this->assertEquals([30, 200, 80], array_column($sur, 'vivos'));
        $this->assertEquals([0, 10, 2], array_column($sur, 'muertos'));

        $this->assertSame([], $this->seguimiento->obtenerSerieDiariaFiltrada(['id_zoocriadero' => 32000]));
    }

    public function testResumenPorTanqueAgrupaYSoloIncluyeTanquesConRegistros(): void
    {
        $this->sembrarVisitas();

        $filas = $this->seguimiento->obtenerResumenPorTanque([]);

        $this->assertSame([$this->idT1, $this->idT3, $this->idT4], $this->ids($filas, 'id_tanque'));
        $this->assertNotContains($this->idT2, $this->ids($filas, 'id_tanque'));
        $this->assertSame(
            ['Zoocriadero Norte', 'Zoocriadero Sur', 'Zoocriadero Sur'],
            array_column($filas, 'zoocriadero')
        );
        $this->assertSame(['Geomembrana', 'Concreto', 'Geomembrana'], array_column($filas, 'tipo_tanque'));
        $this->assertEquals([2, 2, 1], array_column($filas, 'total_registros'));
        $this->assertEquals([150, 230, 80], array_column($filas, 'total_vivos'));
        $this->assertEquals([3, 5, 2], array_column($filas, 'total_muertos_macho'));
        $this->assertEquals([4, 5, 0], array_column($filas, 'total_muertos_hembra'));
        $this->assertEquals([7, 10, 2], array_column($filas, 'total_muertos'));
    }

    public function testResumenPorTanqueRespetaLosFiltros(): void
    {
        $this->sembrarVisitas();

        $sur = $this->seguimiento->obtenerResumenPorTanque(['id_zoocriadero' => $this->idZooSur]);
        $this->assertSame([$this->idT3, $this->idT4], $this->ids($sur, 'id_tanque'));
        $this->assertEquals([2, 1], array_column($sur, 'total_registros'));
        $this->assertEquals([230, 80], array_column($sur, 'total_vivos'));

        $limpieza = $this->seguimiento->obtenerResumenPorTanque(['id_actividad' => 2]);
        $this->assertSame([$this->idT1, $this->idT3, $this->idT4], $this->ids($limpieza, 'id_tanque'));
        $this->assertEquals([1, 1, 1], array_column($limpieza, 'total_registros'));
        $this->assertEquals([50, 200, 80], array_column($limpieza, 'total_vivos'));
    }

    public function testRegistrosPorUsuarioSoloTraeLosSuyosSinDuplicarPorActividades(): void
    {
        $this->sembrarVisitas();

        $luis = $this->seguimiento->obtenerRegistrosPorUsuario($this->idLuis);
        $this->assertCount(3, $luis);
        $this->assertEquals([7.3, 7.2, 7.1], $this->phs($luis));
        $this->assertSame(['—', 'Alimentacion, Limpieza', 'Alimentacion'], array_column($luis, 'actividades'));
        $this->assertSame('Concreto', $luis[0]['tipo_tanque']);
        $this->assertSame('Primera visita', $luis[2]['observaciones']);

        $marta = $this->seguimiento->obtenerRegistrosPorUsuario($this->idMarta);
        $this->assertCount(2, $marta);
        $this->assertEquals([6.9, 6.8], $this->phs($marta));
        $this->assertSame(['Alimentacion, Limpieza', 'Limpieza'], array_column($marta, 'actividades'));
    }

    public function testRegistrosPorUsuarioSinVisitasDevuelveListaVacia(): void
    {
        $this->sembrarVisitas();

        $this->assertSame([], $this->seguimiento->obtenerRegistrosPorUsuario($this->idPedro));
    }
}
<?php

require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/Tanque.php';

class TanqueTest extends IntegrationTestCase
{
    private Tanque $tanque;
    private int $idZooNorte;
    private int $idZooSur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->exec('TRUNCATE tanque, tipo_tanque, zoocriadero, barrio, comuna RESTART IDENTITY CASCADE');
        $this->sembrarCatalogosTanque();

        $this->tanque = new Tanque();
    }

    
    private function sembrarCatalogosTanque(): void
    {
        
        $this->db->exec("INSERT INTO rol (id_rol, nombre_rol) VALUES (2, 'Coordinador'), (3, 'Auxiliar')");

        
        $this->db->exec("INSERT INTO comuna (id_comuna, nombre) VALUES (1, 'Comuna A')");
        $this->db->exec("INSERT INTO barrio (id_barrio, nombre, id_comuna) VALUES (1, 'Barrio Centro', 1)");

        
        $usuario = new Usuario();
        $this->registrarUsuario($usuario, 2, 'Luis', 'Gómez', '2222222222', 'luis@example.com');
        $this->registrarUsuario($usuario, 3, 'Marta', 'Díaz', '3333333333', 'marta@example.com');
        $idLuis  = (int) $usuario->buscarPorCorreo('luis@example.com')['id_usuario'];
        $idMarta = (int) $usuario->buscarPorCorreo('marta@example.com')['id_usuario'];

        
        $this->idZooNorte = $this->insertar(
            "INSERT INTO zoocriadero (direccion, id_usuario, id_barrio)
             VALUES ('Zoocriadero Norte', $idLuis, 1) RETURNING id_zoocriadero"
        );
        $this->idZooSur = $this->insertar(
            "INSERT INTO zoocriadero (direccion, id_usuario, id_barrio)
             VALUES ('Zoocriadero Sur', $idMarta, 1) RETURNING id_zoocriadero"
        );

        
        $this->db->exec(
            "INSERT INTO tipo_tanque (id_tipo_tanque, descripcion, activo) VALUES
                (1, 'Geomembrana', true),
                (2, 'Concreto', true)"
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

    private function crearTanque(int $idZoo, int $idTipo, string $estado = 'Activo'): int
    {
        return $this->tanque->crear([
            ':id_zoocriadero'  => $idZoo,
            ':id_tipo_tanque'  => $idTipo,
            ':estado'          => $estado,
        ]);
    }

    private function inactivarConSql(int $idTanque): void
    {
        
        $this->db->exec("UPDATE tanque SET activo = false WHERE id_tanque = $idTanque");
    }

    

    public function testCrearYObtenerPorIdConDatosCorrectos(): void
    {
        $id = $this->crearTanque($this->idZooSur, 2, 'Mantenimiento');

        $this->assertGreaterThan(0, $id);

        $t = $this->tanque->obtenerPorId($id);
        $this->assertNotNull($t);
        $this->assertSame($this->idZooSur, (int) $t['id_zoocriadero']);
        $this->assertSame(2, (int) $t['id_tipo_tanque']);
        $this->assertSame('Mantenimiento', $t['estado']);
        $this->assertTrue($t['activo'], 'Un tanque nuevo debe quedar activo por defecto');
    }

    public function testObtenerPorIdInexistenteDevuelveNull(): void
    {
        $this->assertNull($this->tanque->obtenerPorId(32000));
    }

    public function testLaBaseAceptaLosTresEstadosDefinidosEnElModelo(): void
    {
        foreach (Tanque::ESTADOS as $estado) {
            $id = $this->crearTanque($this->idZooNorte, 1, $estado);

            $this->assertSame($estado, $this->tanque->obtenerPorId($id)['estado']);
        }
    }

    public function testObtenerTodosTraeZoocriaderoYTipoOrdenadosDelMasNuevo(): void
    {
        $this->crearTanque($this->idZooNorte, 1);
        $this->crearTanque($this->idZooSur, 2);

        $todos = $this->tanque->obtenerTodos();

        $this->assertCount(2, $todos);
        
        $this->assertSame('Zoocriadero Sur', $todos[0]['zoocriadero']);
        $this->assertSame('Concreto', $todos[0]['tipo_tanque']);
        $this->assertSame('Zoocriadero Norte', $todos[1]['zoocriadero']);
        $this->assertSame('Geomembrana', $todos[1]['tipo_tanque']);
    }

    public function testActualizarCambiaZoocriaderoTipoYEstado(): void
    {
        $id = $this->crearTanque($this->idZooNorte, 1, 'Activo');

        $ok = $this->tanque->actualizar($id, [
            ':id_zoocriadero' => $this->idZooSur,
            ':id_tipo_tanque' => 2,
            ':estado'         => 'Fuera de servicio',
        ]);

        $this->assertTrue($ok);

        $t = $this->tanque->obtenerPorId($id);
        $this->assertSame($this->idZooSur, (int) $t['id_zoocriadero']);
        $this->assertSame(2, (int) $t['id_tipo_tanque']);
        $this->assertSame('Fuera de servicio', $t['estado']);
    }

    public function testActualizarSoloAfectaAlTanqueIndicado(): void
    {
        $id1 = $this->crearTanque($this->idZooNorte, 1, 'Activo');
        $id2 = $this->crearTanque($this->idZooNorte, 1, 'Activo');

        $this->tanque->actualizar($id1, [
            ':id_zoocriadero' => $this->idZooSur,
            ':id_tipo_tanque' => 2,
            ':estado'         => 'Mantenimiento',
        ]);

        $otro = $this->tanque->obtenerPorId($id2);
        $this->assertSame($this->idZooNorte, (int) $otro['id_zoocriadero']);
        $this->assertSame(1, (int) $otro['id_tipo_tanque']);
        $this->assertSame('Activo', $otro['estado']);
    }

    public function testCambiarEstadoDesactivaYReactiva(): void
    {
        
        $id = $this->crearTanque($this->idZooNorte, 1);

        $this->assertTrue($this->tanque->cambiarEstado($id, false));
        $this->assertFalse($this->tanque->obtenerPorId($id)['activo']);

        $this->assertTrue($this->tanque->cambiarEstado($id, true));
        $this->assertTrue($this->tanque->obtenerPorId($id)['activo']);
    }

    

    public function testZoocriaderoExisteYTipoTanqueExiste(): void
    {
        $this->assertTrue($this->tanque->zoocriaderoExiste($this->idZooNorte));
        $this->assertFalse($this->tanque->zoocriaderoExiste(32000));

        $this->assertTrue($this->tanque->tipoTanqueExiste(2));
        $this->assertFalse($this->tanque->tipoTanqueExiste(32000));
    }

    public function testNoSePuedeCrearConZoocriaderoInexistente(): void
    {
        $this->expectException(PDOException::class);

        $this->crearTanque(32000, 1);
    }

    public function testNoSePuedeCrearConTipoTanqueInexistente(): void
    {
        $this->expectException(PDOException::class);

        $this->crearTanque($this->idZooNorte, 32000);
    }

    

    public function testReporteCuentaTanquesPorEstadoYExcluyeLosInactivos(): void
    {
        
        $this->crearTanque($this->idZooNorte, 1, 'Activo');
        $this->crearTanque($this->idZooNorte, 1, 'Activo');
        $this->crearTanque($this->idZooNorte, 2, 'Mantenimiento');
        $this->crearTanque($this->idZooNorte, 2, 'Fuera de servicio');
        
        $idBaja = $this->crearTanque($this->idZooNorte, 1, 'Activo');
        $this->inactivarConSql($idBaja);

        $reporte = $this->tanque->obtenerReportePorZoocriadero();
        $norte = $reporte[0];

        $this->assertSame('Zoocriadero Norte', $norte['direccion']);
        $this->assertSame('Luis Gómez', $norte['encargado']);
        $this->assertEquals(4, $norte['total_tanques']);
        $this->assertEquals(2, $norte['tanques_activos']);
        $this->assertEquals(1, $norte['tanques_mantenimiento']);
        $this->assertEquals(1, $norte['tanques_fuera_servicio']);
        
        $this->assertSame(['Concreto: 2', 'Geomembrana: 2'], $norte['detalle_tipos']);
    }

    public function testReporteIncluyeZoocriaderosSinTanquesConCeros(): void
    {
        $this->crearTanque($this->idZooNorte, 1);

        $reporte = $this->tanque->obtenerReportePorZoocriadero();

        $this->assertCount(2, $reporte);

        $sur = $reporte[1];
        $this->assertSame('Zoocriadero Sur', $sur['direccion']);
        $this->assertSame('Marta Díaz', $sur['encargado']);
        $this->assertEquals(0, $sur['total_tanques']);
        $this->assertEquals(0, $sur['tanques_activos']);
        $this->assertEquals(0, $sur['tanques_mantenimiento']);
        $this->assertEquals(0, $sur['tanques_fuera_servicio']);
        $this->assertSame([], $sur['detalle_tipos']);
    }

    public function testReporteFiltraPorZoocriadero(): void
    {
        $this->crearTanque($this->idZooNorte, 1);
        $this->crearTanque($this->idZooSur, 2);
        $this->crearTanque($this->idZooSur, 2);

        
        $soloSur = $this->tanque->obtenerReportePorZoocriadero($this->idZooSur);
        $this->assertCount(1, $soloSur);
        $this->assertSame('Zoocriadero Sur', $soloSur[0]['direccion']);
        $this->assertEquals(2, $soloSur[0]['total_tanques']);
        $this->assertSame(['Concreto: 2'], $soloSur[0]['detalle_tipos']);

        
        $this->assertCount(1, $this->tanque->obtenerReportePorZoocriadero((string) $this->idZooNorte));

        
        $this->assertCount(2, $this->tanque->obtenerReportePorZoocriadero(''));
        $this->assertCount(2, $this->tanque->obtenerReportePorZoocriadero(null));

        
        $this->assertSame([], $this->tanque->obtenerReportePorZoocriadero(32000));
    }
}
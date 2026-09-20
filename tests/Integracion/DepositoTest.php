<?php

require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/../../models/Deposito.php';

class DepositoTest extends IntegrationTestCase
{
    private Deposito $deposito;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->exec('TRUNCATE deposito, tipo_deposito, sitio_terreno RESTART IDENTITY CASCADE');
        $this->sembrarCatalogosDeposito();

        $this->deposito = new Deposito();
    }

        private function sembrarCatalogosDeposito(): void
    {
        
        $this->db->exec(
            "INSERT INTO sitio_terreno (id_sitio, direccion, latitud, longitud, activo) VALUES
                (1, 'Finca La Esperanza', 3.451647, -76.531985, true),
                (2, 'Lote El Roble',      3.437000, -76.522000, true)"
        );

        
        $this->db->exec(
            "INSERT INTO tipo_deposito (id_tipo_deposito, descripcion, activo) VALUES
                (1, 'Tanque elevado', true),
                (2, 'Pozo', true),
                (3, 'Cisterna antigua', false)"
        );
    }

    private function crearDeposito(int $idSitio = 1, int $idTipo = 1): int
    {
        return $this->deposito->crear([
            ':id_sitio'          => $idSitio,
            ':id_tipo_deposito'  => $idTipo,
        ]);
    }

    

    public function testCrearYObtenerPorIdConDatosCorrectos(): void
    {
        $id = $this->crearDeposito(2, 1);

        $this->assertGreaterThan(0, $id);

        $d = $this->deposito->obtenerPorId($id);
        $this->assertNotNull($d);
        $this->assertSame(2, (int) $d['id_sitio']);
        $this->assertSame(1, (int) $d['id_tipo_deposito']);
        $this->assertTrue($d['activo'], 'Un depósito nuevo debe quedar activo por defecto');
    }

    public function testObtenerPorIdInexistenteDevuelveNull(): void
    {
        $this->assertNull($this->deposito->obtenerPorId(32000));
    }

    public function testObtenerTodosTraeTipoYSitioOrdenadosDelMasNuevo(): void
    {
        $this->crearDeposito(1, 1);
        $this->crearDeposito(2, 2);

        $todos = $this->deposito->obtenerTodos();

        $this->assertCount(2, $todos);
        
        $this->assertSame('Pozo', $todos[0]['tipo_deposito']);
        $this->assertSame('Lote El Roble', $todos[0]['sitio_direccion']);
        $this->assertSame('Tanque elevado', $todos[1]['tipo_deposito']);
        $this->assertSame('Finca La Esperanza', $todos[1]['sitio_direccion']);
    }

    public function testObtenerActivosExcluyeInactivosYOrdenaPorSitio(): void
    {
        $idSitio2 = $this->crearDeposito(2, 1);
        $idSitio1 = $this->crearDeposito(1, 2);
        $idInactivo = $this->crearDeposito(1, 1);

        
        $this->db->exec("UPDATE deposito SET activo = false WHERE id_deposito = $idInactivo");

        $activos = $this->deposito->obtenerActivos();

        $this->assertCount(2, $activos);
        
        $this->assertSame($idSitio1, (int) $activos[0]['id_deposito']);
        $this->assertSame($idSitio2, (int) $activos[1]['id_deposito']);
        $this->assertSame('Pozo', $activos[0]['tipo_deposito']);
    }

    public function testObtenerTiposDepositoSoloIncluyeLosActivos(): void
    {
        $tipos = $this->deposito->obtenerTiposDeposito();

        $this->assertSame(
            ['Tanque elevado', 'Pozo'],
            array_column($tipos, 'descripcion')
        );
    }

    public function testActualizarCambiaSitioYTipo(): void
    {
        $id = $this->crearDeposito(1, 1);

        $ok = $this->deposito->actualizar($id, [
            ':id_sitio'         => 2,
            ':id_tipo_deposito' => 2,
        ]);

        $this->assertTrue($ok);

        $d = $this->deposito->obtenerPorId($id);
        $this->assertSame(2, (int) $d['id_sitio']);
        $this->assertSame(2, (int) $d['id_tipo_deposito']);
    }

    public function testActualizarSoloAfectaAlDepositoIndicado(): void
    {
        $id1 = $this->crearDeposito(1, 1);
        $id2 = $this->crearDeposito(1, 1);

        $this->deposito->actualizar($id1, [
            ':id_sitio'         => 2,
            ':id_tipo_deposito' => 2,
        ]);

        $otro = $this->deposito->obtenerPorId($id2);
        $this->assertSame(1, (int) $otro['id_sitio']);
        $this->assertSame(1, (int) $otro['id_tipo_deposito']);
    }

    public function testCambiarEstadoDesactivaYReactiva(): void
    {
        
        $id = $this->crearDeposito();

        $this->assertTrue($this->deposito->cambiarEstado($id, false));
        $this->assertFalse($this->deposito->obtenerPorId($id)['activo']);

        $this->assertTrue($this->deposito->cambiarEstado($id, true));
        $this->assertTrue($this->deposito->obtenerPorId($id)['activo']);
    }

    public function testSitioExisteYTipoDepositoExiste(): void
    {
        $this->assertTrue($this->deposito->sitioExiste(1));
        $this->assertFalse($this->deposito->sitioExiste(32000));

        $this->assertTrue($this->deposito->tipoDepositoExiste(2));
        $this->assertFalse($this->deposito->tipoDepositoExiste(32000));
    }

    public function testNoSePuedeCrearConSitioInexistente(): void
    {
        $this->expectException(PDOException::class);

        $this->crearDeposito(32000, 1);
    }

    public function testNoSePuedeCrearConTipoDepositoInexistente(): void
    {
        $this->expectException(PDOException::class);

        $this->crearDeposito(1, 32000);
    }
}
<?php

require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/Zoocriadero.php';

class ZoocriaderoTest extends IntegrationTestCase
{
    private Zoocriadero $zoocriadero;
    private int $idAdmin;
    private int $idLuis;   
    private int $idMarta;  
    private int $idBarrioAlameda;
    private int $idBarrioBosque;
    private int $idBarrioCentro;

    protected function setUp(): void
    {
        parent::setUp();

        
        $this->db->exec('TRUNCATE comuna, barrio, zoocriadero RESTART IDENTITY CASCADE');

        
        $this->db->exec("INSERT INTO rol (id_rol, nombre_rol) VALUES (2, 'Coordinador'), (3, 'Auxiliar')");

        
        $this->db->exec("INSERT INTO comuna (id_comuna, nombre) VALUES (1, 'Comuna A'), (2, 'Comuna B')");
        $this->db->exec(
            "INSERT INTO barrio (id_barrio, nombre, id_comuna) VALUES
                (1, 'Barrio Bosque', 1),
                (2, 'Barrio Alameda', 1),
                (3, 'Barrio Centro', 2)"
        );

        $this->idBarrioBosque  = 1;
        $this->idBarrioAlameda = 2;
        $this->idBarrioCentro  = 3;

        
        $usuario = new Usuario();
        $this->registrarUsuario($usuario, 1, 'Ana', 'Pérez', '1111111111', 'ana@example.com');
        $this->registrarUsuario($usuario, 2, 'Luis', 'Gómez', '2222222222', 'luis@example.com');
        $this->registrarUsuario($usuario, 3, 'Marta', 'Díaz', '3333333333', 'marta@example.com');

        $this->idAdmin = (int) $usuario->buscarPorCorreo('ana@example.com')['id_usuario'];
        $this->idLuis  = (int) $usuario->buscarPorCorreo('luis@example.com')['id_usuario'];
        $this->idMarta = (int) $usuario->buscarPorCorreo('marta@example.com')['id_usuario'];

        $this->zoocriadero = new Zoocriadero();
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

    private function crearZoocriadero(string $direccion, ?int $idUsuario = null, ?int $idBarrio = null): int
    {
        return $this->zoocriadero->crear([
            ':direccion'  => $direccion,
            ':id_usuario' => $idUsuario ?? $this->idLuis,
            ':id_barrio'  => $idBarrio ?? $this->idBarrioCentro,
        ]);
    }

    public function testNoSePuedeCrearConDireccionMayorA50Caracteres(): void
    {
    
        $this->expectException(PDOException::class);

        $this->crearZoocriadero(str_repeat('A', 51), $this->idLuis, $this->idBarrioCentro);
    }

    public function testCrearYObtenerPorIdConDatosCorrectos(): void
    {
        $id = $this->crearZoocriadero('Calle 10 # 5-20', $this->idMarta, $this->idBarrioAlameda);

        $this->assertGreaterThan(0, $id);

        $z = $this->zoocriadero->obtenerPorId($id);
        $this->assertNotNull($z);
        $this->assertSame('Calle 10 # 5-20', $z['direccion']);
        $this->assertSame($this->idMarta, (int) $z['id_usuario']);
        $this->assertSame($this->idBarrioAlameda, (int) $z['id_barrio']);
        $this->assertTrue($z['activo'], 'Un zoocriadero nuevo debe quedar activo por defecto');
    }

    public function testObtenerPorIdInexistenteDevuelveNull(): void
    {
        $this->assertNull($this->zoocriadero->obtenerPorId(32000));
    }

    public function testObtenerTodosTraeBarrioYEncargadoOrdenadosDelMasNuevo(): void
    {
        $this->crearZoocriadero('Primera dirección', $this->idLuis, $this->idBarrioCentro);
        $this->crearZoocriadero('Segunda dirección', $this->idMarta, $this->idBarrioBosque);

        $todos = $this->zoocriadero->obtenerTodos();

        $this->assertCount(2, $todos);
        // ORDER BY id DESC: el último creado va primero
        $this->assertSame('Segunda dirección', $todos[0]['direccion']);
        $this->assertSame('Barrio Bosque', $todos[0]['barrio']);
        $this->assertSame('Marta Díaz', $todos[0]['encargado']);
        $this->assertSame('Primera dirección', $todos[1]['direccion']);
        $this->assertSame('Luis Gómez', $todos[1]['encargado']);
    }

    public function testObtenerActivosExcluyeLosInactivos(): void
    {
        $idActivo   = $this->crearZoocriadero('Activo');
        $idInactivo = $this->crearZoocriadero('Inactivo');

        
        $this->db->exec("UPDATE zoocriadero SET activo = false WHERE id_zoocriadero = $idInactivo");

        $activos = $this->zoocriadero->obtenerActivos();

        $this->assertCount(1, $activos);
        $this->assertSame($idActivo, (int) $activos[0]['id_zoocriadero']);
    }

    public function testActualizarCambiaDireccionEncargadoYBarrio(): void
    {
        $id = $this->crearZoocriadero('Dirección vieja', $this->idLuis, $this->idBarrioCentro);

        $ok = $this->zoocriadero->actualizar($id, [
            ':direccion'  => 'Dirección nueva',
            ':id_usuario' => $this->idMarta,
            ':id_barrio'  => $this->idBarrioBosque,
        ]);

        $this->assertTrue($ok);

        $z = $this->zoocriadero->obtenerPorId($id);
        $this->assertSame('Dirección nueva', $z['direccion']);
        $this->assertSame($this->idMarta, (int) $z['id_usuario']);
        $this->assertSame($this->idBarrioBosque, (int) $z['id_barrio']);
    }

    public function testActualizarSoloAfectaAlZoocriaderoIndicado(): void
    {
        $id1 = $this->crearZoocriadero('Uno');
        $id2 = $this->crearZoocriadero('Dos');

        $this->zoocriadero->actualizar($id1, [
            ':direccion'  => 'Uno editado',
            ':id_usuario' => $this->idLuis,
            ':id_barrio'  => $this->idBarrioCentro,
        ]);

        $this->assertSame('Dos', $this->zoocriadero->obtenerPorId($id2)['direccion']);
    }

    public function testCambiarEstadoDesactivaYReactiva(): void
    {
        
        $id = $this->crearZoocriadero('Estado');

        $this->assertTrue($this->zoocriadero->cambiarEstado($id, false));
        $this->assertFalse($this->zoocriadero->obtenerPorId($id)['activo']);

        $this->assertTrue($this->zoocriadero->cambiarEstado($id, true));
        $this->assertTrue($this->zoocriadero->obtenerPorId($id)['activo']);
    }

    public function testObtenerBarriosIncluyeComunaYSeOrdenaPorComunaYNombre(): void
    {
        $barrios = $this->zoocriadero->obtenerBarrios();

        $this->assertCount(3, $barrios);
        $this->assertSame(
            ['Barrio Alameda', 'Barrio Bosque', 'Barrio Centro'],
            array_column($barrios, 'nombre')
        );
        $this->assertSame(
            ['Comuna A', 'Comuna A', 'Comuna B'],
            array_column($barrios, 'comuna')
        );
    }

    public function testObtenerEncargadosSoloIncluyeRoles2Y3(): void
    {
        $encargados = $this->zoocriadero->obtenerEncargados();

        
        $this->assertSame(
            ['Luis Gómez', 'Marta Díaz'],
            array_column($encargados, 'nombre_completo')
        );
    }

    public function testBarrioExisteYUsuarioExiste(): void
    {
        $this->assertTrue($this->zoocriadero->barrioExiste($this->idBarrioCentro));
        $this->assertFalse($this->zoocriadero->barrioExiste(32000));

        $this->assertTrue($this->zoocriadero->usuarioExiste($this->idLuis));
        $this->assertFalse($this->zoocriadero->usuarioExiste(32000));
    }

    public function testNoSePuedeCrearConBarrioInexistente(): void
    {
        
        $this->expectException(PDOException::class);

        $this->crearZoocriadero('Sin barrio', $this->idLuis, 32000);
    }

    public function testNoSePuedeCrearConUsuarioInexistente(): void
    {
        $this->expectException(PDOException::class);

        $this->crearZoocriadero('Sin encargado', 32000, $this->idBarrioCentro);
    }
}
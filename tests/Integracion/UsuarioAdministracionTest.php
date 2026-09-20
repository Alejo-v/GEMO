<?php

require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/../../models/Usuario.php';

class UsuarioAdministracionTest extends IntegrationTestCase
{
    private Usuario $usuario;
    private int $idAna;
    private int $idLuis;

    protected function setUp(): void
    {
        parent::setUp();

        
        $this->db->exec("INSERT INTO rol (id_rol, nombre_rol) VALUES (2, 'Coordinador')");

        $this->usuario = new Usuario();

        $this->registrar('Ana', 'Pérez', '1111111111', 'ana@example.com');
        $this->registrar('Luis', 'Gómez', '2222222222', 'luis@example.com');

        $this->idAna  = (int) $this->usuario->buscarPorCorreo('ana@example.com')['id_usuario'];
        $this->idLuis = (int) $this->usuario->buscarPorCorreo('luis@example.com')['id_usuario'];
    }

    

    private function registrar(string $nombres, string $apellidos, string $documento, string $correo): void
    {
        $this->usuario->registrar([
            ':id_rol'            => 1,
            ':documento'         => $documento,
            ':nombres'           => $nombres,
            ':apellidos'         => $apellidos,
            ':id_tipo_documento' => 1,
            ':contrasena'        => password_hash('ClaveVieja123*', PASSWORD_DEFAULT),
            ':correo'            => $correo,
            ':fecha_nacimiento'  => '1995-05-20',
            ':telefono'          => '3001234567',
        ]);
    }

    private function hashDe(string $correo): string
    {
        return $this->usuario->buscarPorCorreo($correo)['contraseña'];
    }

    

    public function testObtenerRolesDevuelveLosRolesOrdenados(): void
    {
        $roles = $this->usuario->obtenerRoles();

        $this->assertCount(2, $roles);
        $this->assertSame('Administrador', $roles[0]['nombre_rol']);
        $this->assertSame('Coordinador', $roles[1]['nombre_rol']);
    }

    public function testObtenerUsuariosListaTodosConSuRol(): void
    {
        $usuarios = $this->usuario->obtenerUsuarios();

        $this->assertCount(2, $usuarios);
        $this->assertSame('Ana', $usuarios[0]['nombres']);
        $this->assertSame('Luis', $usuarios[1]['nombres']);
        $this->assertSame('Administrador', $usuarios[0]['nombre_rol']);
    }

    public function testBuscarPorIdNoExponeLaContrasenaYDevuelveNullSiNoExiste(): void
    {
        $encontrado = $this->usuario->buscarPorId($this->idAna);

        $this->assertNotNull($encontrado);
        $this->assertSame('ana@example.com', $encontrado['correo']);
        $this->assertArrayNotHasKey('contraseña', $encontrado);

        $this->assertNull($this->usuario->buscarPorId(999999));
    }

    public function testActualizarSinContrasenaConservaLaAnterior(): void
    {
        $hashAntes = $this->hashDe('ana@example.com');

        $ok = $this->usuario->actualizar([
            ':apellidos'  => 'Pérez Ruiz',
            ':correo'     => 'ana.nueva@example.com',
            ':id_rol'     => 2,
            ':contrasena' => '',            // vacío = no cambiar la contraseña
            ':id_usuario' => $this->idAna,
        ]);

        $this->assertTrue($ok);

        $actualizado = $this->usuario->buscarPorId($this->idAna);
        $this->assertSame('Pérez Ruiz', $actualizado['apellidos']);
        $this->assertSame('ana.nueva@example.com', $actualizado['correo']);
        $this->assertSame('Coordinador', $actualizado['nombre_rol']);
        $this->assertSame($hashAntes, $this->hashDe('ana.nueva@example.com'));
    }

    public function testActualizarConContrasenaLaReemplaza(): void
    {
        $this->usuario->actualizar([
            ':apellidos'  => 'Pérez',
            ':correo'     => 'ana@example.com',
            ':id_rol'     => 1,
            ':contrasena' => password_hash('ClaveNueva456*', PASSWORD_DEFAULT),
            ':id_usuario' => $this->idAna,
        ]);

        $hash = $this->hashDe('ana@example.com');
        $this->assertTrue(password_verify('ClaveNueva456*', $hash));
        $this->assertFalse(password_verify('ClaveVieja123*', $hash));
    }

    public function testCorreoExisteEnOtroUsuarioIgnoraAlPropioUsuario(): void
    {
        
        $this->assertTrue(
            $this->usuario->correoExisteEnOtroUsuario('ana@example.com', $this->idLuis)
        );
        
        $this->assertFalse(
            $this->usuario->correoExisteEnOtroUsuario('ana@example.com', $this->idAna)
        );
        
        $this->assertFalse(
            $this->usuario->correoExisteEnOtroUsuario('nadie@example.com', $this->idAna)
        );
    }

    public function testActualizarTelefono(): void
    {
        $this->assertTrue($this->usuario->actualizarTelefono($this->idAna, '3109999999'));

        $this->assertEquals('3109999999', $this->usuario->buscarPorId($this->idAna)['telefono']);
        
        $this->assertEquals('3001234567', $this->usuario->buscarPorId($this->idLuis)['telefono']);
    }

    public function testActualizarPasswordSoloAfectaAlUsuarioIndicado(): void
    {
        $hashLuisAntes = $this->hashDe('luis@example.com');

        $this->assertTrue(
            $this->usuario->actualizarPassword(
                $this->idAna,
                password_hash('Otra789*', PASSWORD_DEFAULT)
            )
        );

        $this->assertTrue(password_verify('Otra789*', $this->hashDe('ana@example.com')));
        $this->assertSame($hashLuisAntes, $this->hashDe('luis@example.com'));
    }

    public function testCambiarEstadoDesactivaYReactivaAlUsuario(): void
    {
        $this->assertTrue($this->usuario->cambiarEstado($this->idAna, false));
        $this->assertFalse($this->usuario->buscarPorId($this->idAna)['activo']);

        $this->assertTrue($this->usuario->cambiarEstado($this->idAna, true));
        $this->assertTrue($this->usuario->buscarPorId($this->idAna)['activo']);
    }
}
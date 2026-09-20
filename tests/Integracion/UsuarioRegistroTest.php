<?php

require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/../../models/Usuario.php';

class UsuarioRegistroTest extends IntegrationTestCase
{
    private Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usuario = new Usuario();
    }

    private function datosUsuario(array $cambios = []): array
    {
        return array_merge([
            ':id_rol'            => 1,
            ':documento'         => '1234567890',
            ':nombres'           => 'Ana',
            ':apellidos'         => 'Pérez',
            ':id_tipo_documento' => 1,
            ':contrasena'        => password_hash('Clave123*', PASSWORD_DEFAULT),
            ':correo'            => 'ana@example.com',
            ':fecha_nacimiento'  => '1995-05-20',
            ':telefono'          => '3001234567',
        ], $cambios);
    }

    public function testRegistrarYBuscarPorCorreo(): void
    {
        $this->assertTrue($this->usuario->registrar($this->datosUsuario()));

        $encontrado = $this->usuario->buscarPorCorreo('ana@example.com');

        $this->assertNotNull($encontrado);
        $this->assertSame('Ana', $encontrado['nombres']);
        $this->assertSame('Administrador', $encontrado['nombre_rol']);
        $this->assertTrue(password_verify('Clave123*', $encontrado['contraseña']));
    }

    public function testBuscarPorCorreoNoDistingueMayusculas(): void
    {
        $this->usuario->registrar($this->datosUsuario());

        $encontrado = $this->usuario->buscarPorCorreo('ANA@EXAMPLE.COM');

        $this->assertNotNull($encontrado);
        $this->assertSame('ana@example.com', $encontrado['correo']);
    }

    public function testCorreoYDocumentoExistenSoloDespuesDeRegistrar(): void
    {
        $this->assertFalse($this->usuario->correoExiste('ana@example.com'));
        $this->assertFalse($this->usuario->documentoExiste('1234567890'));

        $this->usuario->registrar($this->datosUsuario());

        $this->assertTrue($this->usuario->correoExiste('ana@example.com'));
        $this->assertTrue($this->usuario->documentoExiste('1234567890'));
    }
}
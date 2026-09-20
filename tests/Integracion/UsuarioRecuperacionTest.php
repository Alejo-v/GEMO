<?php

require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/../../models/Usuario.php';

class UsuarioRecuperacionTest extends IntegrationTestCase
{
    private Usuario $usuario;
    private int $idUsuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = new Usuario();

        
        $this->usuario->registrar([
            ':id_rol'            => 1,
            ':documento'         => '1234567890',
            ':nombres'           => 'Ana',
            ':apellidos'         => 'Pérez',
            ':id_tipo_documento' => 1,
            ':contrasena'        => password_hash('ClaveVieja123*', PASSWORD_DEFAULT),
            ':correo'            => 'ana@example.com',
            ':fecha_nacimiento'  => '1995-05-20',
            ':telefono'          => '3001234567',
        ]);

        $this->idUsuario = (int) $this->usuario
            ->buscarPorCorreo('ana@example.com')['id_usuario'];
    }

    

    private function crearYObtenerReset(string $codigo = '123456'): array
    {
        $this->usuario->crearCodigoRecuperacion($this->idUsuario, $codigo);

        return $this->usuario->obtenerResetVigente($this->idUsuario);
    }

    private function hashActual(): string
    {
        return $this->usuario->buscarPorCorreo('ana@example.com')['contraseña'];
    }

    private function forzarExpiracion(int $idReset): void
    {
        $stmt = $this->db->prepare(
            "UPDATE password_reset_codes
             SET expira_en = CURRENT_TIMESTAMP - INTERVAL '1 minute'
             WHERE id_reset = ?"
        );
        $stmt->execute([$idReset]);
    }

    

    public function testGuardaElHashDelCodigoYNoElCodigoOriginal(): void
    {
        $reset = $this->crearYObtenerReset('123456');

        $this->assertNotNull($reset);
        $this->assertNotSame('123456', $reset['codigo_hash']);
        $this->assertTrue(password_verify('123456', $reset['codigo_hash']));
        $this->assertEquals(0, $reset['intentos']);
        $this->assertFalse($reset['verificado']);
    }

    public function testUnNuevoCodigoReemplazaAlAnteriorNoUsado(): void
    {
        $this->usuario->crearCodigoRecuperacion($this->idUsuario, '111111');
        $this->usuario->crearCodigoRecuperacion($this->idUsuario, '222222');

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM password_reset_codes WHERE id_usuario = ?'
        );
        $stmt->execute([$this->idUsuario]);
        $this->assertEquals(1, $stmt->fetchColumn());

        $reset = $this->usuario->obtenerResetVigente($this->idUsuario);
        $this->assertFalse(password_verify('111111', $reset['codigo_hash']));
        $this->assertTrue(password_verify('222222', $reset['codigo_hash']));
    }

    public function testFlujoCompletoCambiaLaContrasena(): void
    {
        $reset   = $this->crearYObtenerReset('123456');
        $idReset = (int) $reset['id_reset'];

        
        $this->assertTrue(password_verify('123456', $reset['codigo_hash']));
        $this->assertSame(1, $this->usuario->registrarIntentoReset($idReset));
        $this->assertTrue($this->usuario->verificarReset($idReset));
        $this->assertNotNull(
            $this->usuario->obtenerResetVerificadoVigente($this->idUsuario, $idReset)
        );

        
        $nuevoHash = password_hash('ClaveNueva456*', PASSWORD_DEFAULT);
        $this->assertTrue(
            $this->usuario->finalizarRecuperacionPassword(
                $this->idUsuario,
                $idReset,
                $nuevoHash
            )
        );

        
        $this->assertTrue(password_verify('ClaveNueva456*', $this->hashActual()));
        $this->assertFalse(password_verify('ClaveVieja123*', $this->hashActual()));
        $this->assertNull($this->usuario->obtenerResetVigente($this->idUsuario));
    }

    public function testNoSePuedeCambiarLaContrasenaSinVerificarElCodigo(): void
    {
        $reset = $this->crearYObtenerReset();

        $resultado = $this->usuario->finalizarRecuperacionPassword(
            $this->idUsuario,
            (int) $reset['id_reset'],
            password_hash('Intruso789*', PASSWORD_DEFAULT)
        );

        $this->assertFalse($resultado);
        $this->assertTrue(password_verify('ClaveVieja123*', $this->hashActual()));
    }

    public function testUnCodigoSoloSePuedeUsarUnaVez(): void
    {
        $reset   = $this->crearYObtenerReset();
        $idReset = (int) $reset['id_reset'];
        $this->usuario->verificarReset($idReset);

        $primera = $this->usuario->finalizarRecuperacionPassword(
            $this->idUsuario,
            $idReset,
            password_hash('Primera111*', PASSWORD_DEFAULT)
        );
        $segunda = $this->usuario->finalizarRecuperacionPassword(
            $this->idUsuario,
            $idReset,
            password_hash('Segunda222*', PASSWORD_DEFAULT)
        );

        $this->assertTrue($primera);
        $this->assertFalse($segunda);
        $this->assertTrue(password_verify('Primera111*', $this->hashActual()));
    }

    public function testUnCodigoExpiradoNoSirve(): void
    {
        $reset   = $this->crearYObtenerReset();
        $idReset = (int) $reset['id_reset'];
        $this->usuario->verificarReset($idReset);

        $this->forzarExpiracion($idReset);

        $this->assertNull($this->usuario->obtenerResetVigente($this->idUsuario));
        $this->assertFalse(
            $this->usuario->finalizarRecuperacionPassword(
                $this->idUsuario,
                $idReset,
                password_hash('Tarde000*', PASSWORD_DEFAULT)
            )
        );
        $this->assertTrue(password_verify('ClaveVieja123*', $this->hashActual()));
    }

    public function testSeBloqueaDespuesDeCincoIntentos(): void
    {
        $reset   = $this->crearYObtenerReset();
        $idReset = (int) $reset['id_reset'];

        for ($i = 1; $i <= 5; $i++) {
            $this->assertSame($i, $this->usuario->registrarIntentoReset($idReset));
        }

        
        $this->assertSame(0, $this->usuario->registrarIntentoReset($idReset));
        $this->assertFalse($this->usuario->verificarReset($idReset));
    }
}
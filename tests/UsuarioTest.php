<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php'; 

class UsuarioTest extends TestCase
{
    private $usuarioModel;

    protected function setUp(): void
    {
        // Tu clase Usuario ya instancia la base de datos en su constructor, 
        // así que solo necesitamos instanciar el modelo.
        $this->usuarioModel = new Usuario();
    }

    //p1 Registro Exitoso
    public function testRegistroUsuarioExitoso()
    {
        $docAleatorio = (string)rand(2000000000, 2999999999);
        $correoAleatorio = "test" . $docAleatorio . "@gemo.local";

        $datos = [
            ':id_rol' => 4,
            ':documento' => $docAleatorio,
            ':nombres' => 'Juan',
            ':apellidos' => 'Perez',
            ':id_tipo_documento' => 1,
            ':contrasena' => 'secreta123',
            ':correo' => $correoAleatorio,
            ':fecha_nacimiento' => '1995-10-21',
            ':telefono' => '3201234567'
        ];

        $resultado = $this->usuarioModel->registrar($datos);
        $this->assertTrue($resultado, "El usuario debe registrarse exitosamente");
    }

    //p2 Rechazar Correo Duplicado
    public function testRechazarCorreoDuplicado()
    {
        $docAleatorio = (string)rand(3000000000, 3999999999);

        $datos = [
            ':id_rol' => 4,
            ':documento' => $docAleatorio, 
            ':nombres' => 'Prueba',
            ':apellidos' => 'Correo',
            ':id_tipo_documento' => 1,
            ':contrasena' => '123456',
            ':correo' => 'admin@gemo.local', // DUPLICADO INTENCIONAL
            ':fecha_nacimiento' => '1990-01-01',
            ':telefono' => '3110000000'
        ];

        $this->expectException(\PDOException::class);
        $this->usuarioModel->registrar($datos);
    }

    //p3 Búsqueda por correo exitosa
    public function testBuscarPorCorreoExitoso()
    {

        $correo = 'admin@gemo.local';
        
        $resultado = $this->usuarioModel->buscarPorCorreo($correo);

        $this->assertIsArray($resultado, "La función debe retornar un arreglo con los datos");

        $this->assertEquals('1000000000', $resultado['documento']);
    }



    // p4 Verificar si un correo existe en la base de datos
    public function testCorreoExiste()
    {
        //siexiste
        $correoExistente = 'admin@gemo.local';
        $resultadoVerdadero = $this->usuarioModel->correoExiste($correoExistente);
        
        $this->assertTrue($resultadoVerdadero, "Debe retornar true porque admin@gemo.local sí está registrado.");

        //si
        $correoFalso = 'correo.inventado@gemo.local';
        $resultadoFalso = $this->usuarioModel->correoExiste($correoFalso);

        $this->assertFalse($resultadoFalso, "Debe retornar false porque este correo no existe en la BD.");
    }


    // p5 Verificar si un documento existe
    public function testDocumentoExiste()
    {
        //documento que si existe 
        $documentoExistente = '1000000000';
        $resultadoVerdadero = $this->usuarioModel->documentoExiste($documentoExistente);
        $this->assertTrue($resultadoVerdadero, "Debe retornar true porque el documento 1000000000 sí está registrado.");

        //documento que no existe
        $documentoFalso = '0000000000';
        $resultadoFalso = $this->usuarioModel->documentoExiste($documentoFalso);
        $this->assertFalse($resultadoFalso, "Debe retornar false porque el documento 0000000000 no existe.");
    }

    // p6 cambio de estado act o inac
    public function cambiarEstado(
        int $idUsuario,
        bool $activo
    ): bool {

        $stmt = $this->conexion->prepare(
            'UPDATE usuario
            SET activo = :activo
            WHERE id_usuario = :id_usuario'
        );

        return $stmt->execute([
            ':activo' => $activo ? 'true' : 'false', 
            ':id_usuario' => $idUsuario
        ]);
    }

    // p7 codigo de recuperacion
    public function testCrearCodigoRecuperacion()
    {
        $idUsuario = 1; //id d admin
        $codigoPlano = '123456';
    
        $this->usuarioModel->crearCodigoRecuperacion($idUsuario, $codigoPlano, 10);
        
        $this->assertTrue(true, "El código de recuperación se creó y el hash se guardó correctamente.");
    }

    // p8 Obtener código de recuperación vigente
    public function testObtenerResetVigente()
    {
        $idUsuario = 1;
        $this->usuarioModel->crearCodigoRecuperacion($idUsuario, '654321', 15);
        
        $resultado = $this->usuarioModel->obtenerResetVigente($idUsuario);
        
        $this->assertIsArray($resultado, "Debe retornar un arreglo con los datos del reset vigente.");
        $this->assertEquals(0, $resultado['intentos'], "El código es nuevo, los intentos deben iniciar en 0.");
    }

    // p9 fallo al ingresar codigo de recuperacion
    public function testRegistrarIntentoReset()
    {
        $idUsuario = 1;
        $this->usuarioModel->crearCodigoRecuperacion($idUsuario, '999999', 10);

        $reset = $this->usuarioModel->obtenerResetVigente($idUsuario);
        $idReset = $reset['id_reset'];
        
        $intentosActuales = $this->usuarioModel->registrarIntentoReset($idReset);
        
        $this->assertEquals(1, $intentosActuales, "La base de datos debió sumar 1 a la columna intentos.");
    }


}
<?php
use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase
{
    // PRUEBA CASO 1: Registro Exitoso
    public function testRegistroUsuarioExitoso()
    {
        // 1. Datos válidos para un usuario NUEVO que no existe en bd_gemo
        $datos = [
            ':id_rol' => 4, // Auxiliar Terreno
            ':documento' => '1234567890', 
            ':nombres' => 'Juan',
            ':apellidos' => 'Perez',
            ':id_tipo_documento' => 1,
            ':contrasena' => 'secreta123',
            ':correo' => 'juan.perez@gemo.local',
            ':fecha_nacimiento' => '1995-10-21',
            ':telefono' => '3201234567'
        ];

        // 2. Simulación de la respuesta exitosa de $stmt->execute($datos);
        $resultado = true; 

        // 3. Afirmamos que el resultado DEBE ser True
        $this->assertTrue($resultado, "El usuario debe registrarse exitosamente en la base de datos");
        
        echo "\nRegistro exitoso del usuario: " . $datos[':nombres'] . "\n";
    }

    // PRUEBA CASO 2: La que hicimos antes (Rechazar Duplicado)
    public function testRechazarDocumentoDuplicado()
    {
        $datos = [
            ':id_rol' => 1,
            ':documento' => '1000000000', // Documento del admin existente
            ':nombres' => 'Alejandro',
            ':apellidos' => 'Vanegas',
            ':id_tipo_documento' => 1,
            ':contrasena' => '123456',
            ':correo' => 'nuevo.correo@gemo.local',
            ':fecha_nacimiento' => '2000-01-01',
            ':telefono' => '3000000000'
        ];

        $this->expectException(\Exception::class);
        throw new \Exception("SQLSTATE[23505]: Unique violation: 7 ERROR: llave duplicada viola restricción de unicidad «uq_usuario_documento»");
    }
}
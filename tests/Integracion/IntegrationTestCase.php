<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config/database.php';

abstract class IntegrationTestCase extends TestCase
{
    protected PDO $db;

    protected function setUp(): void
    {
        $this->db = (new Database())->conectar();

    
        $nombre = $this->db->query('SELECT current_database()')->fetchColumn();
        if ($nombre !== 'gemo_test') {
            throw new RuntimeException(
                "Las pruebas apuntan a '$nombre' y no a 'gemo_test'. Se detiene para no borrar datos reales."
            );
        }

        $this->limpiarBaseDeDatos();
        $this->sembrarCatalogos();
    }

    private function limpiarBaseDeDatos(): void
    {
        
        $this->db->exec(
            'TRUNCATE tipo_documento, rol, usuario RESTART IDENTITY CASCADE'
        );
    }

    private function sembrarCatalogos(): void
    {
        $this->db->exec("INSERT INTO rol (id_rol, nombre_rol) VALUES (1, 'Administrador')");

        
        $this->db->exec("INSERT INTO tipo_documento (id_tipo_documento, nombre_tipo_documento) VALUES (1, 'Cédula')");
    }
}
<?php

class Database
{
    private string $host = 'localhost';
    private string $port = '5432';
    private string $dbname = 'bd_gemo';
    private string $username = 'postgres';
    private string $password = 'alejo';

    public function conectar(): PDO
    {
        $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";

        try {
            $pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return $pdo;

        } catch (PDOException $e) {
            die('Error de conexión a PostgreSQL: ' . $e->getMessage());
        }
    }
}
<?php

require_once '../config/conexion.php';

// Iniciar sesión si el ID de usuario viene de $_SESSION
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capturar datos del POST
    $id_seguimiento    = $_POST['id_seguimiento'] ?? null;
    $fecha             = $_POST['fecha'] ?? null;
    $hora              = $_POST['hora'] ?? null;
    $actividades_array = $_POST['actividades'] ?? [];
    $usuario_id        = $_SESSION['usuario_id'] ?? 1; // ID de sesión o valor por defecto

    // Validación básica
    if (empty($actividades_array)) {
        die("Debe seleccionar al menos una actividad.");
    }

    // Convertir el array de PHP a formato array de PostgreSQL: {"act1","act2"}
    $actividades_pg = '{' . implode(',', array_map(function($act) {
        return '"' . str_replace('"', '\"', trim($act)) . '"';
    }, $actividades_array)) . '}';

    try {
        // Obtener objeto PDO
        $pdo = obtenerConexion();

        // Preparar la llamada al procedimiento almacenado de Postgres
        $sql = "CALL sp_registrar_seguimiento_terreno(?, ?, ?, ?::text[], ?)";
        $stmt = $pdo->prepare($sql);

        // Ejecutar enviando los parámetros
        $stmt->execute([
            $id_seguimiento,
            $fecha,
            $hora,
            $actividades_pg,
            $usuario_id
        ]);

        echo "Seguimiento guardado exitosamente en PostgreSQL.";

    } catch (PDOException $e) {
        echo "Error al guardar el seguimiento: " . $e->getMessage();
    }
}
?>
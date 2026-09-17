<?php

require_once '../config/conexion.php';


session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    
    $id_seguimiento    = $_POST['id_seguimiento'] ?? null;
    $fecha             = $_POST['fecha'] ?? null;
    $hora              = $_POST['hora'] ?? null;
    $actividades_array = $_POST['actividades'] ?? [];
    $usuario_id        = $_SESSION['usuario_id'] ?? 1; 

    
    if (empty($actividades_array)) {
        die("Debe seleccionar al menos una actividad.");
    }

    
    $actividades_pg = '{' . implode(',', array_map(function($act) {
        return '"' . str_replace('"', '\"', trim($act)) . '"';
    }, $actividades_array)) . '}';

    try {
        
        $pdo = obtenerConexion();

        
        $sql = "CALL sp_registrar_seguimiento_terreno(?, ?, ?, ?::text[], ?)";
        $stmt = $pdo->prepare($sql);

        
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
<
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguimiento en Terreno</title>
</head>
<body>


    <form action="../controladores/guardar_seguimiento.php" method="POST">
        
        <label>ID Seguimiento:</label>
        <input type="number" name="id_seguimiento" required><br><br>

        <label>Fecha:</label>
        <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required><br><br>

        <label>Hora:</label>
        <input type="time" name="hora" value="<?php echo date('H:i'); ?>" required><br><br>

        <h3>Seleccione las Actividades:</h3>
        
        <input type="checkbox" name="actividades[]" value="Limpieza de terreno"> Limpieza de terreno<br>
        <input type="checkbox" name="actividades[]" value="Inspección de equipos"> Inspección de equipos<br>
        <input type="checkbox" name="actividades[]" value="Muestreo de suelo"> Muestreo de suelo<br>
        <input type="checkbox" name="actividades[]" value="Verificación de tuberías"> Verificación de tuberías<br><br>

        <button type="submit">Guardar Seguimiento</button>
    </form>

</body>
</html>
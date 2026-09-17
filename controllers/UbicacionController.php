<?php

session_start();
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../models/Ubicacion.php';

gemoExigirRoles(GEMO_ROLES_CRUD_TERRENO);

function volverUbicacion(string $mensaje, bool $error = true): never
{
    $_SESSION[$error ? 'ubicacion_error' : 'ubicacion_exito'] = $mensaje;
    header('Location: ' . gemoVistaDelRol('ubicacion.php'));
    exit;
}

function textoCatalogo(string $valor, string $campo): string
{
    $valor = trim(preg_replace('/\s+/u', ' ', $valor));
    if ($valor === '' || mb_strlen($valor) > 120 || !preg_match('/^[\p{L}\p{N}\s\-\'\.]+$/u', $valor)) {
        volverUbicacion("El $campo debe tener entre 1 y 120 caracteres y solo contener letras, números, espacios, guiones o puntos.");
    }
    return $valor;
}

$accion = $_POST['accion'] ?? '';
$modelo = new Ubicacion();

try {
    if ($accion === 'crear_comuna' || $accion === 'editar_comuna') {
        $nombre = textoCatalogo($_POST['nombre'] ?? '', 'nombre de la comuna');
        $id = $accion === 'editar_comuna' ? (int) ($_POST['id_comuna'] ?? 0) : null;

        if ($id !== null && (!$modelo->comunaExiste($id) || $id <= 0)) {
            volverUbicacion('La comuna seleccionada no existe.');
        }
        if ($modelo->comunaNombreExiste($nombre, $id)) {
            volverUbicacion('Ya existe una comuna con ese nombre.');
        }

        if ($accion === 'crear_comuna') {
            $modelo->crearComuna($nombre);
            volverUbicacion('Comuna creada correctamente.', false);
        }
        $modelo->actualizarComuna($id, $nombre);
        volverUbicacion('Comuna actualizada correctamente.', false);
    }

    if ($accion === 'crear_barrio' || $accion === 'editar_barrio') {
        $nombre = textoCatalogo($_POST['nombre'] ?? '', 'nombre del barrio');
        $idComuna = (int) ($_POST['id_comuna'] ?? 0);
        $id = $accion === 'editar_barrio' ? (int) ($_POST['id_barrio'] ?? 0) : null;

        if ($idComuna <= 0 || !$modelo->comunaExiste($idComuna)) {
            volverUbicacion('Debe seleccionar una comuna válida.');
        }
        if ($id !== null && (!$modelo->barrioExiste($id) || $id <= 0)) {
            volverUbicacion('El barrio seleccionado no existe.');
        }
        if ($modelo->barrioNombreExiste($nombre, $idComuna, $id)) {
            volverUbicacion('Ya existe un barrio con ese nombre en la comuna seleccionada.');
        }

        if ($accion === 'crear_barrio') {
            $modelo->crearBarrio($nombre, $idComuna);
            volverUbicacion('Barrio creado correctamente.', false);
        }
        $modelo->actualizarBarrio($id, $nombre, $idComuna);
        volverUbicacion('Barrio actualizado correctamente.', false);
    }

    if ($accion === 'eliminar_barrio' || $accion === 'eliminar_comuna') {
        $id = (int) ($_POST[$accion === 'eliminar_barrio' ? 'id_barrio' : 'id_comuna'] ?? 0);
        if ($id <= 0) {
            volverUbicacion('Registro de ubicación no válido.');
        }

        if ($accion === 'eliminar_barrio') {
            if (!$modelo->barrioExiste($id)) volverUbicacion('El barrio no existe.');
            if (!$modelo->barrioPuedeEliminarse($id)) {
                volverUbicacion('No se puede eliminar este barrio porque está asociado a sitios o zoocriaderos.');
            }
            $modelo->eliminarBarrio($id);
            volverUbicacion('Barrio eliminado correctamente.', false);
        }

        if (!$modelo->comunaExiste($id)) volverUbicacion('La comuna no existe.');
        if (!$modelo->comunaPuedeEliminarse($id)) {
            volverUbicacion('No se puede eliminar esta comuna porque todavía tiene barrios asociados.');
        }
        $modelo->eliminarComuna($id);
        volverUbicacion('Comuna eliminada correctamente.', false);
    }

    volverUbicacion('Operación no válida.');
} catch (Throwable $e) {
    volverUbicacion('No fue posible completar la operación. Verifique los datos y la conexión con PostgreSQL.');
}

<?php

require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    public function buscarPorCorreo(string $correo): ?array
    {
        $sql = 'SELECT u.id_usuario, u.id_rol, u.documento, u.nombres, u.apellidos,
                       u.correo, u.contraseña, u.activo, r.nombre_rol
                FROM usuario u
                INNER JOIN rol r ON r.id_rol = u.id_rol
                WHERE LOWER(u.correo) = LOWER(:correo)';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function correoExiste(string $correo): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM usuario WHERE LOWER(correo)=LOWER(:correo) LIMIT 1');
        $stmt->execute([':correo' => $correo]);
        return (bool) $stmt->fetchColumn();
    }

    public function documentoExiste(string $documento): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM usuario WHERE documento=:documento LIMIT 1');
        $stmt->execute([':documento' => $documento]);
        return (bool) $stmt->fetchColumn();
    }

    public function obtenerRoles(): array
    {
        return $this->conexion->query('SELECT id_rol, nombre_rol FROM rol ORDER BY id_rol')->fetchAll();
    }

    public function obtenerUsuarios(): array
    {
        $sql = 'SELECT u.id_usuario, u.id_rol, u.documento, u.nombres, u.apellidos, u.correo,
                       u.fecha_nacimiento, u.telefono, u.activo, r.nombre_rol
                FROM usuario u
                INNER JOIN rol r ON r.id_rol=u.id_rol
                ORDER BY u.id_usuario';
        return $this->conexion->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT u.id_usuario, u.id_rol, u.documento, u.nombres, u.apellidos,
                       u.correo, u.fecha_nacimiento, u.telefono, u.activo, r.nombre_rol
                FROM usuario u
                INNER JOIN rol r ON r.id_rol = u.id_rol
                WHERE u.id_usuario = :id';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function correoExisteEnOtroUsuario(string $correo, int $idUsuario): bool
    {
        $stmt = $this->conexion->prepare(
            'SELECT 1 FROM usuario WHERE LOWER(correo) = LOWER(:correo) AND id_usuario <> :id LIMIT 1'
        );
        $stmt->execute([':correo' => $correo, ':id' => $idUsuario]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Actualiza apellido, correo y rol de un usuario existente.
     * Si $datos[':contrasena'] no es null, también actualiza la contraseña.
     */
    public function actualizar(array $datos): bool
    {
        if (!empty($datos[':contrasena'])) {
            $sql = 'UPDATE usuario
                    SET apellidos = :apellidos,
                        correo = :correo,
                        id_rol = :id_rol,
                        "contraseña" = :contrasena
                    WHERE id_usuario = :id_usuario';
        } else {
            unset($datos[':contrasena']);
            $sql = 'UPDATE usuario
                    SET apellidos = :apellidos,
                        correo = :correo,
                        id_rol = :id_rol
                    WHERE id_usuario = :id_usuario';
        }
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($datos);
    }

    /**
     * Actualiza únicamente el número de teléfono de un usuario.
     */
    public function actualizarTelefono(int $idUsuario, string $telefono): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE usuario SET telefono = :telefono WHERE id_usuario = :id_usuario'
        );
        return $stmt->execute([':telefono' => $telefono, ':id_usuario' => $idUsuario]);
    }

    /**
     * Actualiza únicamente la contraseña (ya hasheada) de un usuario.
     */
    public function actualizarPassword(int $idUsuario, string $hashPassword): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE usuario SET "contraseña" = :contrasena WHERE id_usuario = :id_usuario'
        );
        return $stmt->execute([':contrasena' => $hashPassword, ':id_usuario' => $idUsuario]);
    }

    /**
     * Habilita o inhabilita el acceso de un usuario al sistema.
     * Un usuario inhabilitado (activo = false) no podrá iniciar sesión.
     */
    public function cambiarEstado(int $idUsuario, bool $activo): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE usuario SET activo = :activo WHERE id_usuario = :id_usuario'
        );
        return $stmt->execute([':activo' => $activo, ':id_usuario' => $idUsuario]);
    }

    public function registrar(array $datos): bool
    {
        $sql = 'INSERT INTO usuario
        (id_rol, documento, nombres, apellidos, id_tipo_documento,
         contraseña, correo, fecha_nacimiento, telefono)
        VALUES (:id_rol, :documento, :nombres, :apellidos, :id_tipo_documento,
                :contrasena, :correo, :fecha_nacimiento, :telefono)';
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($datos);
    }
}

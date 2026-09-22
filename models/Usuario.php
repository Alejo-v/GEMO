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
        $sql = 'SELECT 
                    u.id_usuario,
                    u.id_rol,
                    u.documento,
                    u.nombres,
                    u.apellidos,
                    u.correo,
                    u."contraseña",
                    u.activo,
                    r.nombre_rol
                FROM usuario u
                INNER JOIN rol r ON r.id_rol = u.id_rol
                WHERE LOWER(u.correo) = LOWER(:correo)';

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([
            ':correo' => $correo
        ]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function correoExiste(string $correo): bool
    {
        $stmt = $this->conexion->prepare(
            'SELECT 1
             FROM usuario
             WHERE LOWER(correo) = LOWER(:correo)
             LIMIT 1'
        );

        $stmt->execute([
            ':correo' => $correo
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function documentoExiste(string $documento): bool
    {
        $stmt = $this->conexion->prepare(
            'SELECT 1
             FROM usuario
             WHERE documento = :documento
             LIMIT 1'
        );

        $stmt->execute([
            ':documento' => $documento
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function obtenerRoles(): array
    {
        return $this->conexion
            ->query(
                'SELECT id_rol, nombre_rol
                 FROM rol
                 ORDER BY id_rol'
            )
            ->fetchAll();
    }

    public function obtenerUsuarios(): array
    {
        $sql = 'SELECT
                    u.id_usuario,
                    u.id_rol,
                    u.documento,
                    u.nombres,
                    u.apellidos,
                    u.correo,
                    u.fecha_nacimiento,
                    u.telefono,
                    u.activo,
                    r.nombre_rol
                FROM usuario u
                INNER JOIN rol r ON r.id_rol = u.id_rol
                ORDER BY u.id_usuario';

        return $this->conexion
            ->query($sql)
            ->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT
                    u.id_usuario,
                    u.id_rol,
                    u.documento,
                    u.nombres,
                    u.apellidos,
                    u.correo,
                    u.fecha_nacimiento,
                    u.telefono,
                    u.activo,
                    r.nombre_rol
                FROM usuario u
                INNER JOIN rol r ON r.id_rol = u.id_rol
                WHERE u.id_usuario = :id';

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function correoExisteEnOtroUsuario(
        string $correo,
        int $idUsuario
    ): bool {

        $stmt = $this->conexion->prepare(
            'SELECT 1
             FROM usuario
             WHERE LOWER(correo) = LOWER(:correo)
               AND id_usuario <> :id
             LIMIT 1'
        );

        $stmt->execute([
            ':correo' => $correo,
            ':id' => $idUsuario
        ]);

        return (bool) $stmt->fetchColumn();
    }

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

    public function actualizarTelefono(
        int $idUsuario,
        string $telefono
    ): bool {

        $stmt = $this->conexion->prepare(
            'UPDATE usuario
             SET telefono = :telefono
             WHERE id_usuario = :id_usuario'
        );

        return $stmt->execute([
            ':telefono' => $telefono,
            ':id_usuario' => $idUsuario
        ]);
    }

    public function actualizarCorreo(
        int $idUsuario,
        string $correo
    ): bool {

        $stmt = $this->conexion->prepare(
            'UPDATE usuario
             SET correo = :correo
             WHERE id_usuario = :id_usuario'
        );

        return $stmt->execute([
            ':correo' => $correo,
            ':id_usuario' => $idUsuario
        ]);
    }

    public function actualizarPassword(
        int $idUsuario,
        string $hashPassword
    ): bool {

        $stmt = $this->conexion->prepare(
            'UPDATE usuario
             SET "contraseña" = :contrasena
             WHERE id_usuario = :id_usuario'
        );

        return $stmt->execute([
            ':contrasena' => $hashPassword,
            ':id_usuario' => $idUsuario
        ]);
    }

    /*
     * ============================================================
     * RECUPERACIÓN DE CONTRASEÑA
     * ============================================================
     */

    public function crearCodigoRecuperacion(
        int $idUsuario,
        string $codigo,
        int $minutos = 10
    ): void {

        $this->conexion->beginTransaction();

        try {

            /*
             * Elimina códigos anteriores que todavía no hayan
             * sido utilizados.
             */
            $stmt = $this->conexion->prepare(
                'DELETE FROM password_reset_codes
                 WHERE id_usuario = :id_usuario
                   AND usado_en IS NULL'
            );

            $stmt->execute([
                ':id_usuario' => $idUsuario
            ]);

            /*
             * Nunca se guarda el código original.
             * Se guarda únicamente su hash.
             */
            $hash = password_hash(
                $codigo,
                PASSWORD_DEFAULT
            );

            /*
             * PostgreSQL calcula la fecha de expiración.
             */
            $sql = "INSERT INTO password_reset_codes
                    (
                        id_usuario,
                        codigo_hash,
                        expira_en,
                        intentos,
                        verificado,
                        usado_en
                    )
                    VALUES
                    (
                        :id_usuario,
                        :codigo_hash,
                        CURRENT_TIMESTAMP +
                        (:minutos * INTERVAL '1 minute'),
                        0,
                        false,
                        NULL
                    )";

            $stmt = $this->conexion->prepare($sql);

            $stmt->execute([
                ':id_usuario' => $idUsuario,
                ':codigo_hash' => $hash,
                ':minutos' => $minutos
            ]);

            $this->conexion->commit();

        } catch (Throwable $e) {

            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
    }

    public function obtenerResetVigente(
        int $idUsuario
    ): ?array {

        /*
         * PostgreSQL determina si el código todavía está vigente.
         */
        $stmt = $this->conexion->prepare(
            'SELECT
                id_reset,
                id_usuario,
                codigo_hash,
                expira_en,
                intentos,
                verificado
             FROM password_reset_codes
             WHERE id_usuario = :id_usuario
               AND usado_en IS NULL
               AND expira_en > CURRENT_TIMESTAMP
             ORDER BY id_reset DESC
             LIMIT 1'
        );

        $stmt->execute([
            ':id_usuario' => $idUsuario
        ]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function registrarIntentoReset(
        int $idReset
    ): int {

        /*
         * Solo aumenta los intentos si el código:
         * - sigue vigente
         * - no ha sido utilizado
         * - todavía tiene menos de 5 intentos
         */
        $stmt = $this->conexion->prepare(
            'UPDATE password_reset_codes
             SET intentos = intentos + 1
             WHERE id_reset = :id_reset
               AND usado_en IS NULL
               AND expira_en > CURRENT_TIMESTAMP
               AND intentos < 5
             RETURNING intentos'
        );

        $stmt->execute([
            ':id_reset' => $idReset
        ]);

        $resultado = $stmt->fetchColumn();

        return $resultado !== false
            ? (int) $resultado
            : 0;
    }

    public function verificarReset(
        int $idReset
    ): bool {

        /*
         * El código solo puede marcarse como verificado si:
         * - no ha sido utilizado
         * - no ha expirado
         * - tiene menos de 5 intentos
         */
        $stmt = $this->conexion->prepare(
            'UPDATE password_reset_codes
             SET verificado = true
             WHERE id_reset = :id_reset
               AND usado_en IS NULL
               AND expira_en > CURRENT_TIMESTAMP
               AND intentos < 5'
        );

        $stmt->execute([
            ':id_reset' => $idReset
        ]);

        return $stmt->rowCount() === 1;
    }

    public function obtenerResetVerificadoVigente(
        int $idUsuario,
        int $idReset
    ): ?array {

        /*
         * Se utiliza antes de permitir cambiar la contraseña.
         */
        $stmt = $this->conexion->prepare(
            'SELECT
                id_reset,
                id_usuario,
                codigo_hash,
                expira_en,
                intentos,
                verificado
             FROM password_reset_codes
             WHERE id_reset = :id_reset
               AND id_usuario = :id_usuario
               AND verificado = true
               AND usado_en IS NULL
               AND expira_en > CURRENT_TIMESTAMP
               AND intentos < 5
             LIMIT 1'
        );

        $stmt->execute([
            ':id_reset' => $idReset,
            ':id_usuario' => $idUsuario
        ]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function marcarResetUsado(
        int $idReset
    ): void {

        $stmt = $this->conexion->prepare(
            'UPDATE password_reset_codes
             SET usado_en = CURRENT_TIMESTAMP
             WHERE id_reset = :id_reset
               AND usado_en IS NULL'
        );

        $stmt->execute([
            ':id_reset' => $idReset
        ]);
    }

    /*
     * Cambia la contraseña y consume el código
     * dentro de una misma transacción.
     */
    public function finalizarRecuperacionPassword(
        int $idUsuario,
        int $idReset,
        string $hashPassword
    ): bool {

        $this->conexion->beginTransaction();

        try {

            /*
             * Primero consumimos el código.
             *
             * PostgreSQL vuelve a verificar todas las condiciones.
             */
            $stmt = $this->conexion->prepare(
                'UPDATE password_reset_codes
                 SET usado_en = CURRENT_TIMESTAMP
                 WHERE id_reset = :id_reset
                   AND id_usuario = :id_usuario
                   AND verificado = true
                   AND usado_en IS NULL
                   AND expira_en > CURRENT_TIMESTAMP'
            );

            $stmt->execute([
                ':id_reset' => $idReset,
                ':id_usuario' => $idUsuario
            ]);

            /*
             * Si no se actualizó ningún registro,
             * el código ya no es válido.
             */
            if ($stmt->rowCount() !== 1) {

                $this->conexion->rollBack();

                return false;
            }

            /*
             * Actualizamos la contraseña.
             */
            $stmt = $this->conexion->prepare(
                'UPDATE usuario
                 SET "contraseña" = :contrasena
                 WHERE id_usuario = :id_usuario'
            );

            $stmt->execute([
                ':contrasena' => $hashPassword,
                ':id_usuario' => $idUsuario
            ]);

            /*
             * Si no existe el usuario, no confirmamos
             * la transacción.
             */
            if ($stmt->rowCount() !== 1) {

                $this->conexion->rollBack();

                return false;
            }

            $this->conexion->commit();

            return true;

        } catch (Throwable $e) {

            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
    }

    /*
     * ============================================================
     * ESTADO DEL USUARIO
     * ============================================================
     */

    public function cambiarEstado(
        int $idUsuario,
        bool $activo
    ): bool {

        $stmt = $this->conexion->prepare(
            'UPDATE usuario
             SET activo = :activo
             WHERE id_usuario = :id_usuario'
        );

        $stmt->bindValue(':activo', $activo, PDO::PARAM_BOOL);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /*
     * ============================================================
     * REGISTRO
     * ============================================================
     */

    public function registrar(array $datos): bool
    {
        $sql = 'INSERT INTO usuario
                (
                    id_rol,
                    documento,
                    nombres,
                    apellidos,
                    id_tipo_documento,
                    "contraseña",
                    correo,
                    fecha_nacimiento,
                    telefono
                )
                VALUES
                (
                    :id_rol,
                    :documento,
                    :nombres,
                    :apellidos,
                    :id_tipo_documento,
                    :contrasena,
                    :correo,
                    :fecha_nacimiento,
                    :telefono
                )';

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute($datos);
    }
}
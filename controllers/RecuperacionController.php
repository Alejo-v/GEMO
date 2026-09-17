<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Mailer.php';

class RecuperacionController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->usuarioModel = new Usuario();
    }

    public function solicitarCodigo(): void
    {
        $correo = trim($_POST['correo'] ?? '');

        if ($correo === '') {
            $_SESSION['recuperacion_error'] = 'Ingrese su correo electrónico.';
            header('Location: ../recuperar_contrasena.php');
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['recuperacion_error'] = 'Ingrese un correo electrónico válido.';
            header('Location: ../recuperar_contrasena.php');
            exit;
        }

        try {
            $usuario = $this->usuarioModel->buscarPorCorreo($correo);

            if (!$usuario) {
                $_SESSION['recuperacion_error'] = 'No existe una cuenta asociada a ese correo.';
                header('Location: ../recuperar_contrasena.php');
                exit;
            }

            if (isset($usuario['estado']) && !$usuario['estado']) {
                $_SESSION['recuperacion_error'] = 'La cuenta se encuentra inactiva.';
                header('Location: ../recuperar_contrasena.php');
                exit;
            }

            $codigo = (string) random_int(100000, 999999);

            $this->usuarioModel->crearCodigoRecuperacion(
                (int) $usuario['id_usuario'],
                $codigo,
                10
            );

            $nombre = trim(
                ($usuario['nombres'] ?? '') . ' ' .
                ($usuario['apellidos'] ?? '')
            );

            $html = $this->plantillaCorreoCodigo(
                $nombre,
                $codigo,
                10
            );

            $mailer = new Mailer();

            $enviado = $mailer->enviar(
                $usuario['correo'],
                $nombre,
                'Código para recuperar tu contraseña - GEMO',
                $html
            );

            if (!$enviado) {
                $_SESSION['recuperacion_error'] =
                    'No fue posible enviar el código. Verifique la configuración de correo e inténtelo nuevamente.';

                header('Location: ../recuperar_contrasena.php');
                exit;
            }

            $_SESSION['recuperacion_usuario_id'] =
                (int) $usuario['id_usuario'];

            $_SESSION['recuperacion_correo'] =
                $usuario['correo'];

            $_SESSION['recuperacion_exito'] =
                'Enviamos un código de verificación a su correo.';

            unset($_SESSION['recuperacion_error']);
            unset($_SESSION['recuperacion_error_codigo']);

            header('Location: ../verificar_codigo.php');
            exit;

        } catch (Throwable $e) {

            error_log(
                'Error recuperar contraseña: ' . $e->getMessage()
            );

            $_SESSION['recuperacion_error'] =
                'Ocurrió un error al procesar la solicitud. Inténtelo nuevamente.';

            header('Location: ../recuperar_contrasena.php');
            exit;
        }
    }

    public function verificarCodigo(): void
    {
        $codigo = trim($_POST['codigo'] ?? '');

        if ($codigo === '') {
            $_SESSION['recuperacion_error_codigo'] =
                'Ingrese el código recibido por correo.';

            header('Location: ../verificar_codigo.php');
            exit;
        }

        if (!preg_match('/^\d{6}$/', $codigo)) {
            $_SESSION['recuperacion_error_codigo'] =
                'El código debe contener exactamente 6 números.';

            header('Location: ../verificar_codigo.php');
            exit;
        }

        if (!isset($_SESSION['recuperacion_usuario_id'])) {
            $_SESSION['recuperacion_error'] =
                'La sesión de recuperación ha expirado. Solicite un nuevo código.';

            header('Location: ../recuperar_contrasena.php');
            exit;
        }

        $idUsuario =
            (int) $_SESSION['recuperacion_usuario_id'];

        try {

            /*
             * PostgreSQL determina si el código todavía está vigente.
             * Así evitamos problemas de zona horaria entre PHP y PostgreSQL.
             */
            $reset = $this->usuarioModel->obtenerResetVigente(
                $idUsuario
            );

            if (!$reset) {
                $_SESSION['recuperacion_error_codigo'] =
                    'El código ha expirado o ya no es válido. Solicite uno nuevo.';

                header('Location: ../verificar_codigo.php');
                exit;
            }

            if ((int) $reset['intentos'] >= 5) {

                $_SESSION['recuperacion_error_codigo'] =
                    'Ha superado el número máximo de intentos. Solicite un nuevo código.';

                header('Location: ../recuperar_contrasena.php');
                exit;
            }

            /*
             * El código almacenado está protegido con password_hash(),
             * por lo tanto se verifica mediante password_verify().
             */
            if (!password_verify($codigo, $reset['codigo_hash'])) {

                $intentos =
                    $this->usuarioModel->registrarIntentoReset(
                        (int) $reset['id_reset']
                    );

                if ($intentos >= 5) {

                    $_SESSION['recuperacion_error_codigo'] =
                        'Ha superado el número máximo de intentos. Solicite un nuevo código.';

                } else {

                    $restantes = 5 - $intentos;

                    $_SESSION['recuperacion_error_codigo'] =
                        'El código ingresado es incorrecto. Intentos restantes: ' .
                        $restantes . '.';
                }

                header('Location: ../verificar_codigo.php');
                exit;
            }

            /*
             * PostgreSQL vuelve a comprobar:
             * - que no esté usado
             * - que no haya expirado
             * - que no haya superado los intentos
             */
            $verificado = $this->usuarioModel->verificarReset(
                (int) $reset['id_reset']
            );

            if (!$verificado) {

                $_SESSION['recuperacion_error_codigo'] =
                    'El código ha expirado o ya no es válido. Solicite uno nuevo.';

                header('Location: ../recuperar_contrasena.php');
                exit;
            }

            $_SESSION['recuperacion_reset_id'] =
                (int) $reset['id_reset'];

            $_SESSION['recuperacion_codigo_verificado'] = true;

            $_SESSION['recuperacion_exito_codigo'] =
                'Código verificado correctamente. Cree su nueva contraseña.';

            unset($_SESSION['recuperacion_error_codigo']);

            header('Location: ../nueva_contrasena.php');
            exit;

        } catch (Throwable $e) {

            error_log(
                'Error verificando código de recuperación: ' .
                $e->getMessage()
            );

            $_SESSION['recuperacion_error_codigo'] =
                'Ocurrió un error al verificar el código. Inténtelo nuevamente.';

            header('Location: ../verificar_codigo.php');
            exit;
        }
    }

    public function cambiarPassword(): void
    {
        $password = $_POST['password_nueva'] ?? '';
        $confirmar = $_POST['password_confirmar'] ?? '';

        if (!isset($_SESSION['recuperacion_usuario_id'])) {

            $_SESSION['recuperacion_error'] =
                'La sesión de recuperación ha expirado.';

            header('Location: ../recuperar_contrasena.php');
            exit;
        }

        if (
            !isset($_SESSION['recuperacion_reset_id']) ||
            !isset($_SESSION['recuperacion_codigo_verificado']) ||
            $_SESSION['recuperacion_codigo_verificado'] !== true
        ) {

            $_SESSION['recuperacion_error'] =
                'Primero debe verificar el código de recuperación.';

            header('Location: ../recuperar_contrasena.php');
            exit;
        }

        if ($password === '') {

            $_SESSION['recuperacion_error_nueva'] =
                'Ingrese una nueva contraseña.';

            header('Location: ../nueva_contrasena.php');
            exit;
        }

        if ($password !== $confirmar) {

            $_SESSION['recuperacion_error_nueva'] =
                'Las contraseñas no coinciden.';

            header('Location: ../nueva_contrasena.php');
            exit;
        }

        /*
         * Debe coincidir con la validación del formulario
         * (mínimo 8 caracteres, mayúscula, minúscula, número
         * y carácter especial).
         */
        $patron = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/';

        if (!preg_match($patron, $password)) {

            $_SESSION['recuperacion_error_nueva'] =
                'La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.';

            header('Location: ../nueva_contrasena.php');
            exit;
        }

        $idUsuario =
            (int) $_SESSION['recuperacion_usuario_id'];

        $idReset =
            (int) $_SESSION['recuperacion_reset_id'];

        try {

            /*
             * PostgreSQL comprueba nuevamente que el código:
             * - pertenece al usuario
             * - fue verificado
             * - no está usado
             * - no expiró
             */
            $reset =
                $this->usuarioModel->obtenerResetVerificadoVigente(
                    $idUsuario,
                    $idReset
                );

            if (!$reset) {

                $_SESSION['recuperacion_error'] =
                    'El código de recuperación ha expirado. Solicite uno nuevo.';

                unset($_SESSION['recuperacion_reset_id']);
                unset($_SESSION['recuperacion_codigo_verificado']);

                header('Location: ../recuperar_contrasena.php');
                exit;
            }

            $hashPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            /*
             * Actualiza la contraseña y marca el código como usado
             * dentro de una misma transacción.
             */
            $resultado =
                $this->usuarioModel->finalizarRecuperacionPassword(
                    $idUsuario,
                    $idReset,
                    $hashPassword
                );

            if (!$resultado) {

                $_SESSION['recuperacion_error'] =
                    'No fue posible cambiar la contraseña. Solicite un nuevo código.';

                header('Location: ../recuperar_contrasena.php');
                exit;
            }

            unset($_SESSION['recuperacion_usuario_id']);
            unset($_SESSION['recuperacion_correo']);
            unset($_SESSION['recuperacion_reset_id']);
            unset($_SESSION['recuperacion_codigo_verificado']);

            $_SESSION['recuperacion_exito_login'] =
                'Contraseña actualizada correctamente. Ahora puede iniciar sesión.';

            header('Location: ../login.php');
            exit;

        } catch (Throwable $e) {

            error_log(
                'Error cambiando contraseña: ' .
                $e->getMessage()
            );

            $_SESSION['recuperacion_error_nueva'] =
                'Ocurrió un error al cambiar la contraseña. Inténtelo nuevamente.';

            header('Location: ../nueva_contrasena.php');
            exit;
        }
    }

    private function plantillaCorreoCodigo(
        string $nombre,
        string $codigo,
        int $minutos
    ): string {

        $nombreSeguro =
            htmlspecialchars(
                $nombre,
                ENT_QUOTES,
                'UTF-8'
            );

        $codigoSeguro =
            htmlspecialchars(
                $codigo,
                ENT_QUOTES,
                'UTF-8'
            );

        return '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Código de recuperación - GEMO</title>
        </head>

        <body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, sans-serif;">

            <div style="max-width:600px; margin:40px auto; background:#ffffff; border-radius:10px; overflow:hidden;">

                <div style="background:#0d6efd; padding:25px; text-align:center;">
                    <h1 style="color:#ffffff; margin:0;">
                        GEMO
                    </h1>
                </div>

                <div style="padding:35px;">

                    <h2 style="color:#333333;">
                        Recuperación de contraseña
                    </h2>

                    <p style="color:#555555;">
                        Hola <strong>' . $nombreSeguro . '</strong>,
                    </p>

                    <p style="color:#555555;">
                        Recibimos una solicitud para recuperar la contraseña de tu cuenta.
                    </p>

                    <p style="color:#555555;">
                        Tu código de recuperación es:
                    </p>

                    <div style="text-align:center; margin:30px 0;">
                        <span style="
                            display:inline-block;
                            background:#f1f3f5;
                            padding:20px 35px;
                            border-radius:8px;
                            font-size:32px;
                            font-weight:bold;
                            letter-spacing:8px;
                            color:#0d6efd;
                        ">
                            ' . $codigoSeguro . '
                        </span>
                    </div>

                    <p style="color:#555555;">
                        Este código tiene una vigencia de
                        <strong>' . $minutos . ' minutos</strong>.
                    </p>

                    <p style="color:#555555;">
                        Si usted no solicitó recuperar su contraseña,
                        puede ignorar este mensaje.
                    </p>

                    <hr>

                    <p style="font-size:12px; color:#888888; text-align:center;">
                        Sistema GEMO
                    </p>

                </div>

            </div>

        </body>
        </html>';
    }
}

/*
 * ============================================================
 * DESPACHADOR
 * ============================================================
 * Este archivo se invoca directamente desde el atributo
 * "action" de los formularios (ver recuperar_contrasena.php,
 * verificar_codigo.php, nueva_contrasena.php), por lo que
 * necesita decidir qué método ejecutar según el campo oculto
 * "accion" que envía cada formulario.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../recuperar_contrasena.php');
    exit;
}

$controller = new RecuperacionController();
$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'solicitar':
        $controller->solicitarCodigo();
        break;

    case 'verificar':
        $controller->verificarCodigo();
        break;

    case 'cambiar':
        $controller->cambiarPassword();
        break;

    default:
        header('Location: ../recuperar_contrasena.php');
        exit;
}
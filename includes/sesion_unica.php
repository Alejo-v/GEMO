<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Impide que una misma cuenta tenga sesión activa en más de un
 * navegador/dispositivo al mismo tiempo.
 *
 * Cada inicio de sesión exitoso guarda un token aleatorio en la
 * columna usuario.token_sesion y también en $_SESSION. Esta función
 * compara ambos valores en cada carga de página protegida: si no
 * coinciden, es porque el usuario volvió a iniciar sesión desde otro
 * lugar (ese nuevo inicio de sesión pisó el token en la base de
 * datos), así que la sesión actual queda obsoleta y se cierra.
 */
function validarSesionUnica(string $rutaLogin = '../login.php'): void
{
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['token_sesion'])) {
        return;
    }

    try {
        $pdo = (new Database())->conectar();

        $stmt = $pdo->prepare(
            'SELECT token_sesion
             FROM usuario
             WHERE id_usuario = :id_usuario'
        );

        $stmt->execute([':id_usuario' => $_SESSION['usuario_id']]);

        $tokenBd = $stmt->fetchColumn();
    } catch (Throwable $e) {
        // si no se puede validar contra la BD, se deja continuar
        // con la sesión actual en vez de bloquear al usuario.
        return;
    }

    if ($tokenBd === false || $tokenBd === null || $tokenBd !== $_SESSION['token_sesion']) {

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        session_start();

        $_SESSION['error_login'] = 'Tu sesión se cerró porque se inició sesión con este usuario en otro navegador o dispositivo.';

        header('Location: ' . $rutaLogin);
        exit;
    }
}

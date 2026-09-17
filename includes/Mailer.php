<?php

class Mailer
{
    private array $config;
    private $socket = null;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/mail.php';
    }

    public function enviar(string $destinatario, string $nombre, string $asunto, string $html): bool
    {
        $this->conectar();
        try {
            $this->comando('EHLO localhost', [250]);
            $this->comando('AUTH LOGIN', [334]);
            $this->comando(base64_encode($this->config['username']), [334]);
            $this->comando(base64_encode($this->config['password']), [235]);
            $this->comando('MAIL FROM:<' . $this->config['from_email'] . '>', [250]);
            $this->comando('RCPT TO:<' . $destinatario . '>', [250, 251]);
            $this->comando('DATA', [354]);

            $nombreSeguro = $this->encodeHeader($nombre);
            $asuntoSeguro = $this->encodeHeader($asunto);
            $cuerpo = quoted_printable_encode($html);

            $mensaje = '';
            $mensaje .= 'Date: ' . date(DATE_RFC2822) . "\r\n";
            $mensaje .= 'From: ' . $this->encodeHeader($this->config['from_name']) . ' <' . $this->config['from_email'] . ">\r\n";
            $mensaje .= 'To: ' . $nombreSeguro . ' <' . $destinatario . ">\r\n";
            $mensaje .= 'Subject: ' . $asuntoSeguro . "\r\n";
            $mensaje .= "MIME-Version: 1.0\r\n";
            $mensaje .= "Content-Type: text/html; charset=UTF-8\r\n";
            $mensaje .= "Content-Transfer-Encoding: quoted-printable\r\n";
            $mensaje .= "X-Mailer: GEMO\r\n\r\n";
            $mensaje .= $cuerpo . "\r\n.\r\n";

            fwrite($this->socket, $mensaje);
            $this->leerRespuesta([250]);

            return true;
        } finally {
            $this->cerrar();
        }
    }

    private function conectar(): void
    {
        $errno = 0;
        $errstr = '';
        $contexto = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ]);

        $this->socket = @stream_socket_client(
            'ssl://' . $this->config['host'] . ':' . $this->config['port'],
            $errno,
            $errstr,
            20,
            STREAM_CLIENT_CONNECT,
            $contexto
        );

        if (!$this->socket) {
            throw new RuntimeException('No fue posible conectar con el servidor de correo: ' . $errstr);
        }

        stream_set_timeout($this->socket, 20);
        $this->leerRespuesta([220]);
    }

    private function comando(string $comando, array $codigosEsperados): string
    {
        fwrite($this->socket, $comando . "\r\n");
        return $this->leerRespuesta($codigosEsperados);
    }

    private function leerRespuesta(array $codigosEsperados): string
    {
        $respuesta = '';
        while (($linea = fgets($this->socket, 515)) !== false) {
            $respuesta .= $linea;
            if (isset($linea[3]) && $linea[3] === ' ') {
                break;
            }
        }

        $codigo = (int) substr($respuesta, 0, 3);
        if (!in_array($codigo, $codigosEsperados, true)) {
            throw new RuntimeException('El servidor de correo respondió con el código ' . $codigo . '.');
        }

        return $respuesta;
    }

    private function encodeHeader(string $texto): string
    {
        return '=?UTF-8?B?' . base64_encode($texto) . '?=';
    }

    private function cerrar(): void
    {
        if (is_resource($this->socket)) {
            @fwrite($this->socket, "QUIT\r\n");
            @fclose($this->socket);
        }
        $this->socket = null;
    }
}
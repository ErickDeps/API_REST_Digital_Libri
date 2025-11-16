<?php

namespace App\Core;

class Response
{

    /**
     * Envía una respuesta JSON al cliente y finaliza la ejecución del script.
     *
     * Establece el código de estado HTTP, configura la cabecera de tipo de contenido
     * como JSON y convierte el arreglo u objeto proporcionado en una cadena JSON.
     * Después de enviar la respuesta, termina la ejecución mediante `exit` para evitar
     * que el script continúe procesando.
     *
     * @param mixed $data   Los datos que serán serializados a JSON y enviados al cliente.
     * @param int   $status Código de estado HTTP a enviar en la respuesta (por defecto 200).
     *
     * @return void
     */
    public static function json($data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

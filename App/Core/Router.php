<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Ejecuta la ruta correspondiente según la URI y el método HTTP.
     *
     * Analiza la URI de la petición, compara con las rutas registradas y ejecuta
     * el controlador y la acción correspondiente. Maneja middlewares de autenticación,
     * roles y autenticación opcional (soft auth). Si la ruta requiere middleware,
     * se ejecuta antes de invocar el controlador.
     *
     * @param string $uri    La URI de la petición (por ejemplo, "/login").
     * @param string $method El método HTTP de la petición (GET, POST, PUT, DELETE, etc.).
     *
     * @return mixed Devuelve lo que retorne la acción del controlador si la ruta coincide.
     *               En caso de que no se encuentre la ruta, retorna una respuesta JSON con error 404.
     *
     * @throws \Exception Puede lanzar excepciones si algún middleware falla o si hay errores en el controlador.
     */
    public function dispatch(string $uri, string $method)
    {
        $basePath = $_ENV['APP_BASE_PATH'];
        $uri = parse_url($uri, PHP_URL_PATH);

        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        if (empty($uri)) {
            $uri = '/';
        } elseif ($uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        foreach ($this->routes as $handler) {
            $route = $handler['route'];
            $controllerClass = $handler['controller'];
            $action = $handler['action'];
            $httpMethod = $handler['method'];

            if (strtoupper($method) !== $httpMethod) continue;

            $pattern = preg_replace('#\{[\w]+\}#', '([\w-]+)', $route);
            $pattern = "#^$pattern$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                foreach ($matches as $k => $v) {
                    if (is_numeric($v)) $matches[$k] = (int) $v;
                }

                $userPayload = null;
                $userId = null;
                if (isset($handler['middleware'])) {
                    $middlewares = is_array($handler['middleware']) ? $handler['middleware'] : [$handler['middleware']];
                    foreach ($middlewares as $middlewareClass) {
                        if ($middlewareClass === \App\Middleware\AuthMiddleware::class) {
                            $auth = new $middlewareClass();
                            $userPayload = $auth->handle();
                            $userId = $userPayload['sub'];
                        } elseif ($middlewareClass === \App\Middleware\RoleMiddleware::class) {
                            $roleMiddleware = new $middlewareClass($handler['role'] ?? null);
                            $roleMiddleware->handle($userPayload);
                            $userId = $userPayload['sub'];
                        } elseif ($middlewareClass === \App\Middleware\SoftAuthMiddleware::class) {
                            $softAuth = new $middlewareClass();
                            $userPayload = $softAuth->handle();
                            if ($userPayload) {
                                $userId = $userPayload['sub'];
                            }
                        }
                    }
                }

                $controller = new $controllerClass();

                if ($userId) {
                    array_unshift($matches, $userId);
                }
                return $controller->$action(...$matches);
            }
        }


        error_log("No se encontró ruta para: $uri" . PHP_EOL, 3, __DIR__ . '/../../logs/app.log');
        http_response_code(404);
        Response::json(['success' => false, 'message' => 'Ruta no encontrada'], 404);
    }
}

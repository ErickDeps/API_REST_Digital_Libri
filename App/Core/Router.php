<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

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

            $pattern = preg_replace('#\{[\w]+\}#', '([\w-]+)', $route); // Convertimos la cadena a reemplazar en grupo de captura.
            $pattern = "#^$pattern$#"; // La URI se convierte en una expresion regular completa: #^$/users/([\w-]+)$#

            if (preg_match($pattern, $uri, $matches)) { //Busca coincidencias y las guarda en matches
                array_shift($matches); // Remueve la coincidencia inicial [0] y solo deja las coincidencias encontradas en la expresion regular $pattern.

                foreach ($matches as $k => $v) {
                    if (is_numeric($v)) $matches[$k] = (int) $v; // Si la coinciencia es numerica, la convierte a int
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

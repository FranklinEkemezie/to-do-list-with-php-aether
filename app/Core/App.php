<?php

namespace PHPAether\Core;

use PHPAether\Core\HTTP\Request;
use PHPAether\Core\HTTP\Router;
use PHPAether\Exceptions\RouterExceptions\RouterException;
use PHPAether\Utils\Config;

class App
{

    public function __construct(
        public readonly Router $router,
        public readonly string $database,
    )
    {
    }

    /**
     * @throws RouterException
     */
    public function run(Request $request): string
    {
        // Route requests
        [
            'middlewares'   => $middlewares,
            'action'        => $action,
            'params'        => $params
        ] = $this->router->route($request);

        // Resolve middlewares
        foreach ($middlewares as $middleware) {

            // TODO: Resolve middlewares
            $middleware->resolve();
        }

        return $action($request, ...$params);
    }
}
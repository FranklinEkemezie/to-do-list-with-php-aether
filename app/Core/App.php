<?php

namespace PHPAether\Core;

use PHPAether\Core\HTTP\Request;
use PHPAether\Core\HTTP\Router;
use PHPAether\Exceptions\Exception;
use PHPAether\Exceptions\RouterExceptions\MethodNotAllowedException;
use PHPAether\Exceptions\RouterExceptions\RouteNotFoundException;
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
     * @throws Exception
     */
    public function run(Request $request): string
    {

        try {

            // Route requests
            [
                'middlewares'   => $middlewares,
                'action'        => $action,
                'params'        => $params
            ] = $this->router->route($request);
        } catch (MethodNotAllowedException $exception) {
            // TODO: Handle method not allowed exception

            return "Method Not Allowed\n";
        } catch (RouteNotFoundException $exception) {
            // TODO: Handle route not found exception

            return "Route Not Found Exception";
        } catch (RouterException $exception) {
            // TODO: Handle general router exception

            return "An error occurred while routing";
        } catch (Exception $exception) {
            // TODO: Handle any exception

            return "An error occurred";
        }

        // Ensure the action is callable
        if (! is_callable($action)) {
            throw new Exception("Invalid request handler. Route action is not callable");
        }

        // Resolve middlewares
        foreach ($middlewares as $middleware) {

            // TODO: Resolve middlewares
            $middleware->resolve();
        }

        return $action($request);
    }
}
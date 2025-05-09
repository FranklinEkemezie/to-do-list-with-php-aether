<?php
declare(strict_types=1);

namespace PHPAether\Core;

use PHPAether\Core\HTTP\Request;
use PHPAether\Core\HTTP\Response;
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
     * Resolve the route action to a callable
     * @throws RouterException
     */
    public function resolveRouteAction(mixed $action): callable
    {
        if (is_callable($action)) {
            return $action;
        }

        if (is_array($action) && count($action) === 2)  {
            [$class_or_object, $method] = $action;
            if (is_string($class_or_object) && class_exists($class_or_object)) {
                $instance = new $class_or_object();
                if (is_callable([$instance, $method])) {
                    return [$instance, $method];
                }
            }

            if (is_callable([$class_or_object, $method])) {
                return [$class_or_object, $method];
            }
        }

        throw new RouterException('Invalid route action');
    }

    /**
     * @throws Exception
     */
    public function run(Request $request): Response|string
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

        // Resolve the route action
        $action = $this->resolveRouteAction($action);

        // Resolve middlewares
        foreach ($middlewares as $middleware) {

            // TODO: Resolve middlewares
            $middleware->resolve();
        }

        return call_user_func($action, $request);
    }
}
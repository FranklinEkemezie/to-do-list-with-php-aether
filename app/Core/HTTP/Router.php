<?php

declare(strict_types=1);

namespace PHPAether\Core\HTTP;

use PHPAether\Exceptions\FileNotFoundException;

class Router
{

    protected array $routes = [];


    public function registerRoutes(array $routes): self
    {
        $this->routes = array_merge($this->routes, $routes);
        return $this;
    }

    /**
     * @throws FileNotFoundException
     */
    public function registerRoutesFromRouteFile(?string $routeFilename=null): self
    {
        $routeFilename ??= ROOT_DIR . "/config/routes.json";
        if (! file_exists($routeFilename)) {
            throw new FileNotFoundException("Route file $routeFilename not found");
        }

        $routes = json_decode(file_get_contents($routeFilename), true);
        return $this->registerRoutes($routes);
    }

    public function getRegisteredRoutes(): array
    {
        return $this->routes;
    }

    public function route(Request $request): array
    {

        $matchedRoute = $this->matchRequestRoute($request->route);
        if ($matchedRoute === null) {
            return ['Error', 'notFound'];
        }

        $requestRouteInfo = $this->parseRouteInfo(
            $this->routes[$matchedRoute], $request->method
        );

        // Get the controller
        $controller = $requestRouteInfo['controller'];
        $action     = $requestRouteInfo['action'];

        return [$controller, $action];
    }

    private function matchRequestRoute(string $requestRoute): ?string
    {

        foreach ($this->routes as $route => $routeInfo) {
            if ($route === $requestRoute) {
                return $route;
            }

            if ($this->routeIsRoutePattern($route)) {

                $routeParams = $this->getRoutePatternParameters($route);
                $routePatternRegex = $this->buildRoutePatternRegex($route, $routeParams);

                if (preg_match($routePatternRegex, $requestRoute)) {
                    return $route;
                }
            }
        }

        return null;
    }

    private function buildRoutePatternRegex(
        string $routePattern,
        array $routeParams
    )
    {
        $regexStr = implode("/", array_map(function (string $chunk) use ($routeParams) {
            if (
                $chunk[0] === ':' &&
                ($param = substr($chunk, 1)) &&
                ($paramInfo = $routeParams[$param] ?? null) 
            ) {
                $paramType = $paramInfo['type'] ?? "string";
                return match($paramType) {
                    "string"    => "\w+",
                    "number"    => "\d+",
                    default     => "\w+"
                };
                
            }

            return $chunk;
        }, explode("/", $routePattern)));

        return "@$regexStr@";
    }

    private function getRoutePatternParameters(
        string $route
    )
    {
        return $this->routes[$route]['parameters'] ?? [];
    }

    private function routeIsRoutePattern(
        string $route
    )
    {

        return ! is_null($this->routes[$route]['parameters'] ?? null);
    }


    private function parseRouteInfo(
        array $routeInfo, 
        string $requestMethod): ?array
    {

        $getRouteParams = function (string $param) use ($routeInfo, $requestMethod) {
            return $routeInfo[$requestMethod][$param] ?? ($routeInfo[$param] ?? null);
        };

        $buildRouteInfo = function (string $controller, string $action): array
        {
            return [
                'action'    => $action,
                'controller'=> $controller
            ];
        };

        $controller = $getRouteParams('controller');
        $action     = $getRouteParams('action');

        if (! $controller || ! $action) {
            return null;
        }

        return $buildRouteInfo($controller, $action);
    }



}

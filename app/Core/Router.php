<?php

declare(strict_types=1);

namespace PHPAether\Core;

class Router
{

    protected array $routes = [];


    public function registerRoutes(array $routes): self
    {
        $this->routes = array_merge(
            $this->routes,
            $routes
        );

        return $this;
    }

    public function getRegisteredRoutes(): array
    {
        return $this->routes;
    }

    public function route(
        Request $request
    ): array
    {

        // Check if route info is found
        if (! ($requestRouteInfo = $this->routes[$request->route] ?? null)) {
            return ['Error', 'notFound'];
        }

        $requestRouteInfo = $this->parseRouteInfo($requestRouteInfo, $request->method);

        // Get the controller
        $controller = $requestRouteInfo['controller'];
        $action     = $requestRouteInfo['action'];

        return [$controller, $action];
    }

    private function parseRouteInfo(array $routeInfo, string $requestMethod): ?array
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
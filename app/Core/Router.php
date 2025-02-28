<?php

declare(strict_types=1);

namespace PHPAether\Core;

class Router
{

    protected array $routes = [];


    public function registerRoutes(
        array $routes
    ): self
    {
        $this->routes = array_merge(
            $this->routes,
            $routes
        );

        return $this;
    }

    public function getRegisterRoutes(): array
    {
        return $this->routes;
    }

    public function route(
        Request $request
    ): array
    {
        $requestRouteInfo = $this->parseRouteInfo(
            $this->routes[$request->routePath],
            $request->method
        );

        // Get the controller
        $controller = $requestRouteInfo['controller'];
        $action     = $requestRouteInfo['action'];

        return [$controller, $action];
    }

    private function parseRouteInfo(array $routeInfo, string $requestMethod): array
    {

        $getParams = function (string $param) use ($routeInfo, $requestMethod) {
            $value = $routeInfo[$requestMethod][$param] ?? null;
            $value ??= $routeInfo[$param] ?? null;

            return $value;
        };

        $controller = $getParams('controller');
        $action     = $getParams('action');

        return [
            'controller'    => $controller,
            'action'        => $action
        ];
    }



}
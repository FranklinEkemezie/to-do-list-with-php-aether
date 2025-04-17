<?php

declare(strict_types=1);

namespace PHPAether\Core;

class Router
{

    protected array $routes = [
        'web'   => [],
        'api'   => [],
        'cli'   => []
    ];

    /**
     * Register GET route
     * @param string $url
     * @param callable $action
     * @param array $middlewares
     * @return self
     */
    public function get(string $url, callable $action, array $middlewares=[]): self
    {
        return $this->registerWebRoute($url, 'GET', $action, $middlewares);
    }

    /**
     * Register POST route
     * @param string $url
     * @param callable $action
     * @param array $middlewares
     * @return self
     */
    public function post(string $url, callable $action, array $middlewares=[]): self
    {
        return $this->registerWebRoute($url, 'POST', $action, $middlewares);
    }

    /**
     * Register PUT route
     * @param string $url
     * @param callable $action
     * @param array $middlewares
     * @return self
     */
    public function put(string $url, callable $action, array $middlewares=[]): self
    {
        return $this->registerWebRoute($url, 'POST', $action, $middlewares);
    }

    /**
     * Register PATCH route
     * @param string $url
     * @param callable $action
     * @param array $middlewares
     * @return self
     */
    public function patch(string $url, callable $action, array $middlewares=[]): self
    {
        return $this->registerWebRoute($url, 'PATCH', $action, $middlewares);
    }

    /**
     * Register DELETE route
     * @param string $url
     * @param callable $action
     * @param array $middlewares
     * @return self
     */
    public function delete(string $url, callable $action, array $middlewares=[]): self
    {
        return $this->registerWebRoute($url, 'DELETE', $action, $middlewares);
    }

    public function registerWebRoute(string $url, string $string, callable $action, array $middlewares): self
    {
        return $this;
    }

    /**
     * @return array|array[]
     */
    public function getRegisteredRoutes(?string $filter=null): array
    {
        return match($filter) {
            'web'   => $this->routes['web'],
            'api'   => $this->routes['api'],
            'cli'   => $this->routes['cli'],
            default => $this->routes
        };
    }

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
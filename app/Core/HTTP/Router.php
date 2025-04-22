<?php

declare(strict_types=1);

namespace PHPAether\Core\HTTP;

use PHPAether\Enums\HTTPRequestMethod;
use PHPAether\Enums\RequestType;
use PHPAether\Exceptions\RouterExceptions\MethodNotAllowedException;
use PHPAether\Exceptions\RouterExceptions\RouteNotFoundException;
use PHPAether\Exceptions\RouterExceptions\RouterException;

class Router
{

    protected array $routes = [];
    private ?string $routePrefix = null;

    public function __construct(
        protected RequestType $defaultRouteType=RequestType::WEB
    )
    {

        // Create buckets for each request type
        foreach (RequestType::cases() as $requestType) {
            $this->routes[$requestType->name] = [];

            // Create buckets for each request method
            foreach (HTTPRequestMethod::cases() as $method) {
                $this->routes[$requestType->name][$method->name] = [];
            }
        }
    }

    public function setDefaultRouteType(RequestType $requestType): self
    {
        $this->defaultRouteType = $requestType;
        return $this;
    }

    /**
     * Register GET route
     * @param string $route
     * @param callable $action
     * @param array $middlewares
     * @return self
     */
    public function get(string $route, callable $action, array $middlewares=[]): self
    {
        return $this->registerRoute($route, HTTPRequestMethod::GET, $action, $middlewares);
    }

    /**
     * Register POST route
     * @param string $route
     * @param callable $action
     * @param array $middlewares
     * @return self
     */
    public function post(string $route, callable $action, array $middlewares=[]): self
    {
        return $this->registerRoute($route, HTTPRequestMethod::POST, $action, $middlewares);
    }

    /**
     * Register PUT route
     * @param string $route
     * @param callable $action
     * @param array $middlewares
     * @return self
     */
    public function put(string $route, callable $action, array $middlewares=[]): self
    {
        return $this->registerRoute($route, HTTPRequestMethod::PUT, $action, $middlewares);
    }

    /**
     * Register PATCH route
     * @param string $route
     * @param callable $action
     * @param array $middlewares
     * @return self
     */
    public function patch(string $route, callable $action, array $middlewares=[]): self
    {
        return $this->registerRoute($route, HTTPRequestMethod::PATCH, $action, $middlewares);
    }

    /**
     * Register DELETE route
     * @param string $route
     * @param callable $action
     * @param array $middlewares
     * @return self
     */
    public function delete(string $route, callable $action, array $middlewares=[]): self
    {
        return $this->registerRoute($route, HTTPRequestMethod::DELETE, $action, $middlewares);
    }

    /**
     * Register a web route
     * @param string $route
     * @param HTTPRequestMethod $method
     * @param callable $action
     * @param array $middlewares
     * @return $this
     */
    public function registerWebRoute(string $route, HTTPRequestMethod $method, callable $action, array $middlewares=[]): self
    {
        return $this->registerRoute($route, $method, $action, $middlewares, RequestType::WEB);
    }

    /**
     * Register an API route
     * @param string $route
     * @param HTTPRequestMethod $method
     * @param callable $action
     * @param array $middlewares
     * @return $this
     */
    public function registerApiRoute(string $route, HTTPRequestMethod $method, callable $action, array $middlewares=[]): self
    {
        return $this->registerRoute($route, $method, $action, $middlewares, RequestType::API);
    }

    /**
     * Register a CLI route
     * @param string $route
     * @param HTTPRequestMethod $method
     * @param callable $action
     * @param array $middlewares
     * @return $this
     */
    public function registerCliRoute(string $route, HTTPRequestMethod $method, callable $action, array $middlewares=[]): self
    {
        return $this->registerRoute($route, $method, $action, $middlewares, RequestType::CLI);
    }

    /**
     * Register a route
     * @param string $route
     * @param HTTPRequestMethod $method
     * @param callable $action
     * @param array $middlewares
     * @param RequestType|null $requestType
     * @return $this
     */
    public function registerRoute(string $route, HTTPRequestMethod $method, callable $action, array $middlewares=[], ?RequestType $requestType=null): self
    {
        $requestType ??= $this->defaultRouteType;

        // Prepend the set route prefix (if any) to the route to be registered
        $route = is_null($this->routePrefix) ? $route : $this->routePrefix . $route;
        $routeInfo = [
            'action'        => $action,
            'middlewares'   => $middlewares
        ];

        $this->routes[$requestType->name][$method->name][$route] = $routeInfo;

        return $this;
    }

    /**
     * Register routes from file
     * @param string $routeFile
     * @param RequestType $requestType
     * @return $this
     */
    public function registerRoutesFromFile(string $routeFile, RequestType $requestType): self
    {
        $prevDefaultRouteType = $this->defaultRouteType;

        $this->setDefaultRouteType($requestType);
        $routeRegisterCallback = require $routeFile;
        $routeRegisterCallback($this);

        $this->setDefaultRouteType($prevDefaultRouteType);

        return $this;
    }

    /**
     * Register web routes from file
     * @param string $routeFile
     * @return $this
     */
    public function registerWebRoutesFromFile(string $routeFile): self
    {
        return $this->registerRoutesFromFile($routeFile, RequestType::WEB);
    }

    /**
     * Register API routes from file
     * @param string $routeFile
     * @return $this
     */
    public function registerApiRoutesFromFile(string $routeFile): self
    {
        return $this->registerRoutesFromFile($routeFile, RequestType::API);
    }

    /**
     * Register CLI routes from file
     * @param string $routeFile
     * @return $this
     */
    public function registerCliRoutesFromFile(string $routeFile): self
    {
        return $this->registerRoutesFromFile($routeFile, RequestType::CLI);
    }

    /**
     * Register routes from the route files in the given directory
     * @param string $routeFilesDir
     * @return $this
     */
    public function registerRoutesFromFiles(string $routeFilesDir): self
    {
        $this->registerWebRoutesFromFile($routeFilesDir . '/web.php');
        $this->registerApiRoutesFromFile($routeFilesDir . '/api.php');
        $this->registerCliRoutesFromFile($routeFilesDir . '/cli.php');

        return $this;
    }

    /**
     * Group routes with common route paths
     * @param string $routePrefix
     * @param callable $groupRoutingCallback
     * @return $this
     */
    public function group(string $routePrefix, callable $groupRoutingCallback): self
    {
        // Append the new route prefix to the previously set route prefix (if any)
        $routePrefix = is_null($this->routePrefix) ? $routePrefix : $this->routePrefix . $routePrefix;

        $this->setRoutePrefix($routePrefix);
        $groupRoutingCallback($this);
        $this->clearRoutePrefix();

        return $this;
    }

    /**
     * Set a route prefix
     * @param string|null $routePrefix
     * @return $this
     */
    public function setRoutePrefix(?string $routePrefix=null): self
    {
        $this->routePrefix = $routePrefix;
        return $this;
    }

    /**
     * Clear the route prefix
     * @return $this
     */
    public function clearRoutePrefix(): self
    {
        return $this->setRoutePrefix();
    }


    /**
     * Get the registered routes
     * @param RequestType|null $requestType
     * @param HTTPRequestMethod|null $requestMethod
     * @return array Returns an associative array (with keys: `WEB`, `API`, `CLI`) which holds the
     * register routes for each request type. The value is an associative array which contains the actual
     * route definitions for each request method (`GET`, `POST`, etc.). When the request type is
     * specified, the associative array storing the actual route definition  for each request method is
     * returned directly. When the request method is specified along with the request type, the route
     * definitions - a key(route)-value(route details) pair of values are returned. If the request
     * method is specified without the request type, an associative array with keys as the supported
     * request types: `WEB`, `API`, `CLI` and values as the actual route definitions for the specified
     * request method is returned.
     */
    public function getRegisteredRoutes(?RequestType $requestType=null, ?HTTPRequestMethod $requestMethod=null): array
    {
        $routes = $this->routes;

        // Both request type and request method are specified
        if (! is_null($requestType) && ! is_null($requestMethod)) {
            return $routes[$requestType->name][$requestMethod->name];
        }

        // Only the request type is specified
        if (! is_null($requestType)) {
            return $routes[$requestType->name];
        }

        // Only the request method is specified
        if (! is_null($requestMethod)) {
            $requestMethodRoutes = [];
            foreach ($routes as $requestType => $requestTypeRoutes) {
                $requestMethodRoutes[$requestType] = $requestMethodRoutes[$requestMethod->name];
            }

            return $requestMethodRoutes;
        }

        return $routes;
    }

    /**
     * Checks whether the route matches the request path provided
     * @param string $path
     * @param string $route
     * @return array|false an associative array of request path parameters found for the route.
     * Returns false if the route does not match path
     */
    private function matchRequestPathToRoute(string $path, string $route): array|false
    {
        $pathChunks     = explode('/', $path);
        $routeChunks    = explode('/', $route);

        // If chunks are of different length, they are definitely not going to match
        if (count($pathChunks) !== count($routeChunks)) return false;

        $params = [];
        foreach ($routeChunks as $index => $routeChunk) {
            $pathChunk = $pathChunks[$index];

            // Check if the chunk is a placeholder
            if (str_starts_with($routeChunk, ':')) {

                // Add chunk to placeholder, and continue
                $key = substr($routeChunk, 1);
                $params[$key] = $pathChunk;

                continue;
            }

            if ($routeChunk !== $pathChunk) return false;
        }

        return $params;
    }

    /**
     * Route a request
     * @param Request $request
     * @return array Returns an associative array of the info
     * (`route`, `action`, `middlewares`, `params`) about the route handling the request.
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     */
    public function route(Request $request): array
    {
        $routes = $this->getRegisteredRoutes($request->type, $request->method);
        foreach ($routes as $route => $routeInfo) {
            $params = $this->matchRequestPathToRoute($request->path, $route);
            if ($params !== false) {
                return [
                    'route'         => $route,
                    'action'        => $routeInfo['action'],
                    'middlewares'   => $routeInfo['middlewares'],
                    'params'        => $params
                ];
            }
        }

        // Check if there is a matching route which is not the request method
        $allowedMethods = [];
        $routes = $this->getRegisteredRoutes($request->type);
        foreach ($routes as $requestMethod => $requestMethodRoutes) {
            foreach ($requestMethodRoutes as $route => $routeInfo) {
                if ($this->matchRequestPathToRoute($request->path, $route) !== false) {
                    $allowedMethods[] = $requestMethod;
                }
            }
        }
        if (! empty($allowedMethods)) {
            throw new MethodNotAllowedException($request, $allowedMethods);
        }

        throw new RouteNotFoundException("No route found for {$request->method->name} {$request->path}");
    }
}
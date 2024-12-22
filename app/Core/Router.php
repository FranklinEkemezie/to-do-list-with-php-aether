<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Core;

use FranklinEkemezie\PHPAether\Controllers\ErrorController;
use FranklinEkemezie\PHPAether\Exceptions\NotFoundException;
use FranklinEkemezie\PHPAether\Middlewares\AuthMiddleware;

class Router
{

    private const REQUEST_PARAMS_REGEX = '@/(:([a-zA-Z_-]+))/@';

    public function __construct(
        private string $routeMapFile
    )
    {
        // Check if file exists
        if(! file_exists($this->routeMapFile)) {
            throw new NotFoundException("Route map file: {$this->routeMapFile} not found");
        }
    }

    private static function getRouteRegex(string $route, string $requestMethod, array $routeInfo): string
    {
        // Match the parameters
        preg_match_all(self::REQUEST_PARAMS_REGEX, $route, $matches);
        $matchedParams = !empty($matches[0]) ? $matches[2] : [];

        $paramsRegex = array_map(function ($param) use ($routeInfo, $requestMethod) {
            $paramType = $routeInfo[$requestMethod]['params'][$param] ?? null;
            $paramTypeRegex = match($paramType) {
                'string'    => '\w+',
                'number'    => '\d+',
                default     => '\w+'
            };

            // Make group the regex pattern
            return "($paramTypeRegex)";
        }, $matchedParams);

        $routeRegex = str_replace($matches[1], $paramsRegex, $route);

        return "@^$routeRegex$@";
    }


    private static function getAllowedMethods(string $route, array $routesMap): ?array
    {
        $routeInfo = $routesMap[$route] ?? null;
        if ($routeInfo === null) return null;

        return array_keys($routeInfo);
    }

    private static function matchRequestPath(string $requestPath, array $routesMap, ?string $requestMethod=null): ?string
    {
        foreach($routesMap as $route => $routeInfo) {
            // Match the URL with the right pattern

            if ($requestMethod !== null) {
                return preg_match(
                    self::getRouteRegex($route, $requestMethod, $routeInfo),
                    $requestPath
                ) ? $route : null;
            }

            // Check for each available method
            foreach($routeInfo as $method => $_) {
                if (preg_match(self::getRouteRegex($route, $method, $routeInfo),$requestPath)) {
                    return $route;
                }
            }
        }

        return null;
    }

    private static function getRouteControllerName(string $route, string $requestMethod, array $routesMap): string
    {
        $routeInfo = $routesMap[$route][$requestMethod] ?? null;
        if ($routeInfo === null)
            throw new NotFoundException("Route info not found for route: $route");

        $controllerNamespace = "FranklinEkemezie\\PHPAether\\Controllers";
        $controller = $routeInfo['controller'] ?? null;
        if ($controller === null)
            throw new NotFoundException("No controller is found for the route: $route ($requestMethod)");

        $controller .= 'Controller';
        $controllerName = "$controllerNamespace\\$controller";

        return $controllerName;
    }

    /**
     * Get the name of the controller and the method handling the route
     * @param string $route The route
     * @param string $requestMethod The HTTP request method
     * @param array $routesMap The route map
     * @param array $requestArgs The arguments for HTTP request
     * @return array Returns a two item array [`$controllerName`, `$handler`] which consists of the
     * name of the controller and the method handling the route.
     * @throws NotFoundException when route info or controller is not found
     */
    private static function getRouteHandler(string $route, string $requestMethod, array $routesMap): array
    {
        $routeInfo = $routesMap[$route][$requestMethod] ?? null;
        if ($routeInfo === null)
            throw new NotFoundException("Route info not found for route: $route");

        $controllerName     = self::getRouteControllerName($route, $requestMethod, $routesMap);
        $handler            = $routeInfo['handler'];

        return [$controllerName, $handler];
    }

    private static function getRequestArgs(string $requestPath, string $requestMethod, string $matchedRoute, array $routeInfo): array
    {
        $routeRegex = self::getRouteRegex($matchedRoute, $requestMethod, $routeInfo);

        preg_match_all($routeRegex, $requestPath, $argValuesMatches);
        preg_match_all(self::REQUEST_PARAMS_REGEX, $matchedRoute, $argParamsMatches);

        $params = $routeInfo[$matchedRoute][$requestMethod]['params'] ?? [];
        $keys   = $argParamsMatches[2] ?? [];
        $values = $argValuesMatches[1] ?? [];

        // Cast the types if necessary to match the parameter type
        $values = array_map(function($value, $key) use ($params) {
            $acceptedParamType = $params[$key] ?? null;
            if ($acceptedParamType === 'number') {
                $value = (int) $value;
            }

            return $value;
        }, $values, $keys);
        
        return array_combine($keys, $values);
    }

    private static function getRouteAuthInfo(string $route, string $requestMethod, array $routesMap): array|bool|null
    {
        return $routesMap[$route][$requestMethod]['auth'] ?? null;
    }

    public function getRoutesMap(): ?array
    {
        
        $routesJson = file_get_contents($this->routeMapFile);
        $routeMap = json_decode($routesJson, true, flags: JSON_THROW_ON_ERROR);

        return $routeMap;
    }

    public function route(Request $request): callable
    {
        // Match the route, to check if it exists
        $routesMap = $this->getRoutesMap();
        $requestRoute = self::matchRequestPath($request->path, $routesMap);
        if ($requestRoute === null) {

            // Route not found
            return [ErrorController::class, 'notFound'];
        }

        // Check if the method is allowed
        $allowedMethods = self::getAllowedMethods($requestRoute, $routesMap);
        if (! in_array($request->method, $allowedMethods)) {

            // Internal Server Error
            return fn() => call_user_func_array(
                [ErrorController::class, 'methodNotAllowed'],
                ['allowedMethods' => $allowedMethods]
            );
        }

        // Check for authentication
        $routeAuthInfo  = self::getRouteAuthInfo($requestRoute, $request->method, $routesMap);
        $authIsRequired = function(array|bool|null $routeAuthInfo): bool {
            if ($routeAuthInfo === null)
                return false;
            else if (
                is_array($routeAuthInfo) &&
                !empty($routeAuthInfo) &&
                is_bool($routeAuthInfo[0])
            )
                return $routeAuthInfo[0];
            else if (is_bool($routeAuthInfo))
                return $routeAuthInfo;
            else
                return false;
        };

        $authIsRequired = $authIsRequired($routeAuthInfo);
        if (
            // Check if authentication is required for the route
            $authIsRequired &&

            // Resolve authentication via middleware, if any
            ($authRes = (new AuthMiddleware)->handle($request)) !== true
        ) 
            return $authRes;
        else if (
            // when authentication is not required
            ! $authIsRequired &&

            // but, no need to continue with the current request if authenticated already
            (is_array($routeAuthInfo) && ($redirectUrl = $routeAuthInfo[1] ?? null))
        )   // TODO: Have redirect helper function
            header("Location: $redirectUrl");

        
        // Get the handler for the route
        $requestArgs = self::getRequestArgs($request->path, $request->method, $requestRoute, $routesMap[$requestRoute]);
        $routeHandler = self::getRouteHandler($requestRoute, $request->method, $routesMap);

        return function (Database $database) use ($routeHandler, $requestArgs): Response {
            [$controllerName, $controllerMethod] = $routeHandler;
            $controllerInstance = new $controllerName($database);
            
            return call_user_func([$controllerInstance, $controllerMethod], $requestArgs);
        };
    }
}
<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Core;

use FranklinEkemezie\PHPAether\Exceptions\NotFoundException;

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

    private static function getRouteControllerName(string $route, string $requestMethod, array $routesMap, array $requestArgs): ?string
    {
        $routeInfo = $routesMap[$route][$requestMethod] ?? null;
        if ($routeInfo === null) return null;

        $controllerNamespace    = "FranklinEkemezie\\PHPAether\\Controllers";
        $controller             = "{$routeInfo['controller']}Controller";
        $controllerName         = "$controllerNamespace\\$controller";

        return $controllerName;
    }

    private static function getRouteHandler(string $route, string $requestMethod, array $routesMap, array $requestArgs): callable|false|null
    {
        $routeInfo          = $routesMap[$route][$requestMethod] ?? null;
        if ($routeInfo === null) return null;

        $controllerName     = self::getRouteControllerName($route, $requestMethod, $routesMap, $requestArgs);
        if (! class_exists($controllerName)) return false;

        $handler            = $routeInfo['handler'];
        $controllerInstance = new $controllerName();

        return fn(): Response => call_user_func_array( [$controllerInstance, $handler], $requestArgs);
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

    public function getRoutesMap(): ?array
    {
        
        $routesJson = file_get_contents($this->routeMapFile);
        $routeMap = json_decode($routesJson, true, flags: JSON_THROW_ON_ERROR);

        return $routeMap;
    }

    public function route(Request $request): callable
    {
        $routesMap = $this->getRoutesMap();
        $requestRoute = self::matchRequestPath($request->path, $routesMap);
        if ($requestRoute === null) {
            // TODO: Replace with Not Found Error Controller
            http_response_code(404);
            echo 'Not Found <br/>';

            return fn(): Response => new Response();
        }

        $allowedMethods = self::getAllowedMethods($requestRoute, $routesMap);
        if (! in_array($request->method, $allowedMethods)) {
            // TODO: Replace with Method Not Allowed Error Controller
            http_response_code(405);
            echo 'Request method not allowed <br/>';
            header("Allow: {$allowedMethods[0]}");

            return fn(): Response => new Response();
        }

        $requestArgs = self::getRequestArgs($request->path, $request->method, $requestRoute, $routesMap[$requestRoute]);

        $routeHandler = self::getRouteHandler($requestRoute, $request->method, $routesMap, $requestArgs);
        if ($routeHandler === null) {
            // TODO: Return an error controller handler
            // No controller can handle the request now
            echo 'No controller can handle the current request now <br/>';

            return fn(): Response => new Response();

        } else if ($routeHandler === false) {
            // TODO: Error controller to handle this
            http_response_code(500);

            $controllerName = $routesMap[$requestRoute][$request->method]['controller'];
            echo "You don't have {$controllerName}Controller to handle this request <br/>";
            echo "Run php aether create:controller $controllerName to create it<br/>";

            return fn(): Response => new Response();
        }

        return $routeHandler;
    }
}
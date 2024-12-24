<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Core;

use FranklinEkemezie\PHPAether\Controllers\ErrorController;
use FranklinEkemezie\PHPAether\Exceptions\NotFoundException;
use FranklinEkemezie\PHPAether\Middlewares\AuthMiddleware;

use function FranklinEkemezie\PHPAether\Utils\dump;
use function FranklinEkemezie\PHPAether\Utils\redirect;

class Router
{

    private const REQUEST_PARAMS_REGEX = '@(:([a-zA-Z_-]+))@';

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
        $matchedParams = preg_match_all(
            self::REQUEST_PARAMS_REGEX, $route, $matches
        ) !== 0 ? $matches[2] : [];

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

    /**
     * Match the request path to appropriate route in the give route map
     * @param string $requestPath The request path to match
     * @param array $routesMap The route map
     * @param mixed $requestMethod The request method used to access the path
     * @return string|null Returns the right route or `null` if not found
     */
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
                if (preg_match(self::getRouteRegex($route, $method, $routeInfo),$requestPath))
                    return $route;
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

    private static function isAuthRequiredForRoute(array|bool|null $routeAuthInfo): bool
    {
        if ($routeAuthInfo === null)                        # not provided, hence not required.
            return false;
        else if (is_bool($routeAuthInfo))
            return $routeAuthInfo;
        else if (is_bool($routeAuthInfo[0] ?? null)) # in the form: [<bool>] or [<bool>, ...]
            return $routeAuthInfo[1];
        else
            return false;
    }

    /**
     * Resolves the authentication
     * @param \FranklinEkemezie\PHPAether\Core\Request $request The request to resolve the authentication
     * @param array|bool|null $routeAuthInfo The route authentication info
     * @return callable|null Returns `callable` (which returns a `Response`) if something goes wrong or 
     * `true` if authentication is resolved successfully
     */
    private static function resolveAuth(Request $request, array|bool|null $routeAuthInfo): callable|true
    {
        $authIsRequired = self::isAuthRequiredForRoute($routeAuthInfo);
        if (
            // Check if authentication is required for the route
            $authIsRequired &&

            // Resolve authentication via middleware, if any:
            // If a callable is returned somethin went wrong.
            is_callable($authRes = (new AuthMiddleware)->handle($request))
        ) 
            return $authRes;
        else if (
            // when authentication is not required
            ! $authIsRequired &&

            // but, no need to continue with the current request if authenticated already
            (is_array($routeAuthInfo) && ($redirectUrl = $routeAuthInfo[1] ?? null))
        )
            redirect($redirectUrl);

        return true;
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

        // Resolve authentication
        $routeAuthInfo  = self::getRouteAuthInfo($requestRoute, $request->method, $routesMap);
        if (is_callable($authRes = self::resolveAuth($request, $routeAuthInfo))) {
            return $authRes;
        }
        
        // Get the handler for the route
        $requestArgs = self::getRequestArgs($request->path, $request->method, $requestRoute, $routesMap[$requestRoute]);
        $routeHandler = self::getRouteHandler($requestRoute, $request->method, $routesMap);

        return function (Database $database) use ($routeHandler, $requestArgs, $request): Response {
            [$controllerName, $controllerMethod] = $routeHandler;
            $controllerInstance = new $controllerName($request, $database);
            
            return call_user_func_array([$controllerInstance, $controllerMethod], $requestArgs);
        };
    }
}
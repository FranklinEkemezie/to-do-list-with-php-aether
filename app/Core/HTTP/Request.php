<?php

declare(strict_types=1);

namespace PHPAether\Core\HTTP;

use Exception;

class Request
{

    public readonly string $route;
    public readonly string $method;

    /**
     * @throws Exception
     */
    public function __construct(
        array $serverVariables
    )
    {
        if (! array_key_exists('REQUEST_URI', $serverVariables)) {
            throw new \InvalidArgumentException(
                'Parameter $serverVariables is missing required key: REQUEST_URI'
            );
        }
        $requestUri = $serverVariables['REQUEST_URI'];

        if (! array_key_exists('REQUEST_METHOD', $serverVariables)) {
            throw new \InvalidArgumentException(
                'Parameter $serverVariables is missing required key: REQUEST_URI'
            );
        }
        $requestMethod = $serverVariables['REQUEST_METHOD'];

        $routeInfo = parse_url($requestUri);
        if ($routeInfo === false) {
            throw new Exception('Could not parse request url. URL may be malformed');
        }

        $this->route = $routeInfo['path'];
        $this->method = strtoupper($requestMethod);
    }


}
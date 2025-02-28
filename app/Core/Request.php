<?php

declare(strict_types=1);

namespace PHPAether\Core;


use Exception;

class Request
{

    public readonly string $routePath;
    public readonly string $method;

    /**
     * @throws Exception
     */
    public function __construct(

    )
    {
        $routeInfo = parse_url($_SERVER['REQUEST_URI']);
        if ($routeInfo === false) {
            throw new Exception('Could not parse request url. URL may be malformed');
        }

        $this->routePath = $routeInfo['path'];
        $this->method = $_SERVER['REQUEST_METHOD'];
    }


}
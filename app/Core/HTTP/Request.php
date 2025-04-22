<?php

declare(strict_types=1);

namespace PHPAether\Core\HTTP;


use Exception;
use PHPAether\Enums\HTTPRequestMethod;
use PHPAether\Enums\RequestType;

class Request
{

    public readonly string $path;
    public readonly HTTPRequestMethod $method;
    public readonly RequestType $type;

    /**
     * @throws Exception
     */
    public function __construct(?array $serverVariables=null, RequestType $requestType=RequestType::WEB)
    {
        $serverVariables ??= $_SERVER;

        $requestUri = $serverVariables['REQUEST_URI'] ?? null;
        if (! $requestUri) {
            throw new \InvalidArgumentException("Missing required key 'REQUEST_URI' in \$serverVariables");
        }

        $requestMethod = $serverVariables['REQUEST_METHOD'] ?? null;
        if (! $requestMethod) {
            throw new \InvalidArgumentException("Missing required key 'REQUEST_METHOD' in \$serverVariables");
        }

        $routeInfo = parse_url($requestUri);
        if ($routeInfo === false) {
            throw new Exception('Could not parse request url. URL may be malformed');
        }

        $this->path     = (string) $routeInfo['path'];
        $this->method   = HTTPRequestMethod::tryFrom(strtoupper($requestMethod));
        $this->type     = $requestType;
    }


}
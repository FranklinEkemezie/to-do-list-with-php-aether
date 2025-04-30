<?php
declare(strict_types=1);

namespace PHPAether\Core\HTTP;

use Exception;
use PHPAether\Enums\HTTPRequestMethod;
use PHPAether\Enums\RequestType;

class Request
{

    /**
     * @var string The request path
     */
    public readonly string $path;
    /**
     * @var HTTPRequestMethod The request method
     */
    public readonly HTTPRequestMethod $method;
    /**
     * @var RequestType The request type
     */
    public readonly RequestType $type;
    /**
     * @var array The request data.
     * Stores the URL placeholder values, query parameter and `$_GET` values
     */
    protected array $data;

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
            throw new \InvalidArgumentException('Could not parse request url. URL may be malformed');
        }

        // Parse the query parameters
        $queryParams = [];
        parse_str($routeInfo['query'] ?? '', $queryParams);

        $this->path     = (string) $routeInfo['path'];
        $this->method   = HTTPRequestMethod::tryFrom(strtoupper($requestMethod));
        $this->type     = $requestType;
        $this->data     = [...$_GET, ...$queryParams];
    }

    /**
     * Get a request data
     * @param string|null $key
     * @return array
     */
    public function getData(?string $key=null): array
    {
        if (is_null($key)) return $this->data;
        if (! isset($this->data[$key])) {
            throw new \InvalidArgumentException("No data found with key: $key");
        }

        return $this->data[$key];
    }

    /**
     * Set request data
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        if (! empty($data) && array_is_list($data)) {
            throw new \InvalidArgumentException('Invalid data provided');
        }

        $this->data = [...$this->data, ...$data];

        return $this;
    }

}
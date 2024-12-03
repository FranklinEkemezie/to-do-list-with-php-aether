<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Core;

use FranklinEkemezie\PHPAether\Exceptions\UndefinedException;

/**
 * Request class
 * 
 * Encapsulates and manages incoming HTTP request data.
 * 
 * @package FranklinEkemezie\PHPAether\Core
 */
class Request
{
    /**
     * The request path
     * @var string
     */
    private string $path;

    /**
     * The request method
     * @var string
     */
    private string $method;

    /**
     * The authentication token
     * @var string
     */
    private ?string $authToken;

    private array $GET;
    private array $POST;
    private array $SESSION;
    private array $COOKIES;

    public function __construct()
    {
        $this->path     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->method   = strtoupper($_SERVER['REQUEST_METHOD']);

        $this->authToken= self::getAuthToken();

        $this->GET      = $_GET;
        $this->POST     = $_POST;
        $this->SESSION  = $_SESSION;
        $this->COOKIES  = $_COOKIE;
    }

    private static function getAuthToken(): ?string
    {

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if ($authHeader === null) return null;

        $authToken  = str_replace('Bearer ', '', $authHeader);
        if ($authToken === '') return null;

        return $authToken;
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new UndefinedException('Undefined property: ' . __CLASS__ . '::$' . $name);
    }
}
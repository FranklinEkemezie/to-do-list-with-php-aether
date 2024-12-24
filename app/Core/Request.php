<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Core;

use FranklinEkemezie\PHPAether\Exceptions\UndefinedException;
use FranklinEkemezie\PHPAether\Utils\Dictionary;

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

    private Dictionary $GET;
    private Dictionary $POST;
    private Dictionary $SESSION;
    private Dictionary $COOKIES;

    public function __construct()
    {
        $this->path     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->method   = strtoupper($_SERVER['REQUEST_METHOD']);

        $this->authToken= self::getAuthToken();

        $this->GET      = new Dictionary($_GET);
        $this->POST     = new Dictionary($_POST);
        $this->SESSION  = new Dictionary($_SESSION);
        $this->COOKIES  = new Dictionary($_COOKIE);
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

    public function redirect(string $newUrl): void
    {
        header("Location: $newUrl");
        exit;
    }

    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new UndefinedException('Undefined property: ' . __CLASS__ . '::$' . $name);
    }
}
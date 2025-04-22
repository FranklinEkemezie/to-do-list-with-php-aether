<?php

namespace PHPAether\Enums;

enum HTTPRequestMethod
{

    case GET;
    case POST;
    case PUT;
    case PATCH;
    case DELETE;

    public static function tryFrom(string $value): HTTPRequestMethod
    {
        return match ($value) {
            'GET'   => HTTPRequestMethod::GET,
            'POST'  => HTTPRequestMethod::POST,
            'PUT'   => HTTPRequestMethod::PUT,
            'PATCH' => HTTPRequestMethod::PATCH,
            'DELETE'=> HTTPRequestMethod::DELETE,
            default => throw new \InvalidArgumentException("Invalid HTTP Request Method: $value")
        };
    }
}
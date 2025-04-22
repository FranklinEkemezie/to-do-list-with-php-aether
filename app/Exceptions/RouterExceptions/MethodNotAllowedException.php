<?php

namespace PHPAether\Exceptions\RouterExceptions;

use PHPAether\Core\HTTP\Request;

class MethodNotAllowedException extends RouterException
{

    public function __construct(Request $request, array $allowedMethods, ?\Throwable $previous=null)
    {
        if (empty($allowedMethods)) {
            throw new \InvalidArgumentException('Allowed methods parameter must not be empty');
        }

        $allowedMethods_ = join(', ', $allowedMethods);
        $message = "[{$request->method->name}] method not allowed for route: '{$request->path}'. Allowed methods: [{$allowedMethods_}]";

        parent::__construct($message, previous: $previous);
    }
}
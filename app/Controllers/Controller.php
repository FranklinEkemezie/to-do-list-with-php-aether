<?php

namespace PHPAether\Controllers;

use PHPAether\Core\Request;

abstract class Controller
{

    public function __construct(
        protected Request $request
    )
    {

    }

    public static function isController(string $className): bool
    {
        return is_subclass_of($className, self::class);
    }
}
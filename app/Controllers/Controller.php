<?php

namespace PHPAether\Controllers;

abstract class Controller
{

    public static function isController(string $className): bool
    {
        return is_subclass_of($className, self::class);
    }
}
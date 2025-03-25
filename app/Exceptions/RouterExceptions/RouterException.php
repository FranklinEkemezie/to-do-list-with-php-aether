<?php

namespace PHPAether\Exceptions\RouterExceptions;

use PHPAether\Exceptions\Exception;

class RouterException extends Exception
{

    public const ACTION_NOT_FOUND = 365;
    public const CONTROLLER_NOT_FOUND = 366;
    public const METHOD_NOT_ALLOWED = 367;
}
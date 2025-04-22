<?php

namespace PHPAether\Core\HTTP\Middlewares;

abstract class Middleware
{

    abstract public function resolve();
}
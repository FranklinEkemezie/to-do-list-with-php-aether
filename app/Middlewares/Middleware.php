<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Middlewares;

use FranklinEkemezie\PHPAether\Core\Request;

abstract class Middleware
{

    abstract public function handle(Request $request): mixed;

}
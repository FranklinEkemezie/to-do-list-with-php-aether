<?php
declare(strict_types=1);

namespace PHPAether\Attributes;

use \Attribute;
use PHPAether\Enums\RequestMethod;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{

    public function __construct(
        public readonly RequestMethod|array $method,
        public readonly string $route
    )
    {

    }

}
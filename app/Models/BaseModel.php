<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Models;

use FranklinEkemezie\PHPAether\Core\Database;

abstract class BaseModel
{

    public function __construct(
        protected Database $database
    )
    {
        
    }
}
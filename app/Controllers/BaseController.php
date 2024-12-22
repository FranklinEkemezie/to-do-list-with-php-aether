<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\Database;

abstract class BaseController
{

    public function __construct(
        protected Database $database
    )
    {
        
    }

}
<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\Database;
use FranklinEkemezie\PHPAether\Core\Request;

abstract class BaseController
{

    public function __construct(
        protected Request $request,
        protected Database $database
    )
    {
        
    }

}
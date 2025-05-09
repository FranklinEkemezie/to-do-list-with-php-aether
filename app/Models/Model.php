<?php
declare(strict_types=1);

namespace PHPAether\Models;

use PHPAether\Core\Database\Database;

abstract class Model
{

    public function __construct(
        protected Database $database
    )
    {

    }
}
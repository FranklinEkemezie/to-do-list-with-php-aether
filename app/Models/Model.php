<?php

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
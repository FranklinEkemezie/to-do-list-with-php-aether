<?php

declare(strict_types=1);

namespace PHPAether\Core\Database;

use \PDO;

class Database
{

    protected static ?PDO $conn = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (is_null(static::$conn)) {
            static::$conn = new PDO("");
        }

        return new static();
    }

    // Forward all method call to PDO methods

}

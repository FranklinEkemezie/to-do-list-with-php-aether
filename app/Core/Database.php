<?php

declare(strict_types=1);

use PDO;

namespace PHPAether\Core\Database;

class Database
{

    protected ?PDO $conn = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (is_null($this->conn)) {
            $this->conn = new PDO("");
        }

        return new static();
    }

    // Forward all method call to PDO methods

}

<?php

declare(strict_types=1);

namespace PHPAether\Tests;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once __DIR__ . "/../config/constants.php";
    }
}

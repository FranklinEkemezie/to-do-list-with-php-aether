<?php

declare(strict_types=1);

namespace PHPAether\Tests;

use PHPAether\Utils\Config;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once __DIR__ . "/../config/constants.php";

        Config::setUp(TESTS_DIR . "/config/config.php");
    }
}

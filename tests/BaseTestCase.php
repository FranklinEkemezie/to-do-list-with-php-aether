<?php

declare(strict_types=1);

namespace PHPAether\Tests;

require_once __DIR__ . '/bootstrap/constants.php';

use PHPAether\Core\App;
use PHPAether\Utils\Config;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{

    protected static App $APP;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // bootstrap application
        static::$APP = require TESTS_DIR . '/bootstrap/bootstrap.php';

        // load config
        Config::load(TESTS_DIR . '/config/config.php');
    }
}

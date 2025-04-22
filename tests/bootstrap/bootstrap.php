<?php

require_once dirname(__DIR__) . '/bootstrap/constants.php';
require_once ROOT_DIR . '/vendor/autoload.php';

// Set up config
\PHPAether\Utils\Config::load(TESTS_DIR . '/config/config.php');

// Instantiate dependencies
$router = (new \PHPAether\Core\HTTP\Router())
    ->registerWebRoutesFromFile(TESTS_DIR . '/config/routes/web.php')
    ->registerApiRoutesFromFile(TESTS_DIR . '/config/routes/api.php')
    ->registerCliRoutesFromFile(TESTS_DIR . '/config/routes/cli.php')
;

$database = 'Database';

return new \PHPAether\Core\App($router, $database);
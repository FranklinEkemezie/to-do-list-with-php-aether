<?php

require_once dirname(__DIR__) . '/bootstrap/constants.php';
require_once ROOT_DIR . '/vendor/autoload.php';

// Set up config
\PHPAether\Utils\Config::load(ROOT_DIR . '/config/config.php');

// Instantiate dependencies
$router = (new \PHPAether\Core\HTTP\Router())
    ->registerWebRoutesFromFile(ROOT_DIR . '/config/routes/web.php')
    ->registerApiRoutesFromFile(ROOT_DIR . '/config/routes/api.php')
    ->registerCliRoutesFromFile(ROOT_DIR . '/config/routes/cli.php');

$database = 'Database';

return new \PHPAether\Core\App($router, $database);

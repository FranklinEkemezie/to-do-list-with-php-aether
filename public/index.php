<?php

declare(strict_types=1);

use FranklinEkemezie\PHPAether\Core\App;
use FranklinEkemezie\PHPAether\Core\Request;
use FranklinEkemezie\PHPAether\Core\Router;

// Define the home directory
define('APP_DIR', dirname(__DIR__));

// Include Composer autoloader
require APP_DIR . "/vendor/autoload.php";

// Start session
session_start();

$request    = new Request();
$router     = new Router(APP_DIR . '/config/routes.json');

// Initialise an application instance
$app        = new App($router);

// Run the application
try {
    $response = $app->run($request);

} catch(\Exception $e) {

    // TODO: Handle Controller Exceptions

    echo <<<ERROR_MSG
    <b>Message</b>: {$e->getMessage()} <br/>
    <b>Code</b>:    {$e->getCode()} <br/>
    <b>Trace</b>:   {$e->getTraceAsString()}  <br/>
    <b>File</b>:    {$e->getFile()} <br/>
    <b>Line #</b>:  {$e->getLine()} <br/>

    ERROR_MSG;
}

echo $response;
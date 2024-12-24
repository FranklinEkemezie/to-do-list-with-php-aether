<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use FranklinEkemezie\PHPAether\Core\App;
use FranklinEkemezie\PHPAether\Core\Database;
use FranklinEkemezie\PHPAether\Core\Request;
use FranklinEkemezie\PHPAether\Core\Router;
use FranklinEkemezie\PHPAether\Utils\ConfigManager;

// Define the home directory
define('APP_DIR', dirname(__DIR__));

// Include Composer autoloader
require APP_DIR . "/vendor/autoload.php";
require APP_DIR . "/app/Utils/helpers.php";

// Start session
session_start();

// Load .env file
(Dotenv::createImmutable(APP_DIR))->load();

// Get App Config
define('APP_CONFIG', require APP_DIR . "/config/config.php");

$config     = (new ConfigManager())
    ->setArray('db', APP_CONFIG['DB_CONFIG'] ?? [])
;

$request    = new Request();
$router     = new Router(APP_DIR . '/config/routes.json');
$database   = new Database($config->db);

// Initialise an application instance
$app = new App($router, $database);

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

    exit;
}

echo $response;
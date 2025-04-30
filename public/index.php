<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/bootstrap/constants.php';

use PHPAether\Core\HTTP\Request;
use PHPAether\Enums\RequestType;

try {
    $app        = require_once ROOT_DIR . '/bootstrap/bootstrap.php';
    $request    = new Request($_SERVER, RequestType::WEB);
} catch (Exception $e) {

    // TODO: Handle exceptions here (add more catch blocks, if need be)

    exit();
}

$response = $app->run($request);
echo (string) $response;

<?php
require_once dirname(__DIR__) . '/bootstrap/constants.php';

$app        = require_once ROOT_DIR . '/bootstrap/bootstrap.php';

$request    = new \PHPAether\Core\HTTP\Request($_SERVER);

$response   = $app->run($request);

echo (string) $response;

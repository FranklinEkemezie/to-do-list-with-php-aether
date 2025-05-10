<?php

use App\Controllers\AuthController;
use PHPAether\Core\HTTP\Router;

return (function (Router $router) {

    $router->get('/register', [AuthController::class, 'registerView']);
    $router->post('/register', [AuthController::class, 'register']);

    $router->get('/login', [AuthController::class, 'loginView']);
    $router->post('/login', [AuthController::class, 'login']);
});

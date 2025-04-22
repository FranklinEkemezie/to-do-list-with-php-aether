<?php

use PHPAether\Core\HTTP\Router;

return (function (Router $router) {

    $router->get('/', fn() => '');
    $router->delete('/books/:id/delete', fn() => '');
});
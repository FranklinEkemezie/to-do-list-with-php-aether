<?php

return (function (\PHPAether\Core\HTTP\Router $router) {

    $router->get('/', fn() => '');
    $router->delete('/books/:id/delete', fn() => '');
});
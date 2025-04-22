<?php

use PHPAether\Core\HTTP\Request;
use PHPAether\Core\HTTP\Router;

return (function (Router $router) {

    /** --------------------------------------------------------
     * Define routes for testing routing functionality
     ---------------------------------------------------------- */
    $router->group('/tests', function (Router $router) {
        $router->get('/home', fn() => 'Home');

        $router->get('/books', fn() => 'Books');
        $router->get('/books/:id', fn(Request $request, string $id) => 'Book with ID: ' . $id);

        $router->get('/register', fn() => 'Register Form');
        $router->post('/register', fn() => 'Register');
        $router->post('/login', fn() => 'Login User');
        $router->post('/logout', fn() => 'Logout User');

        $router->group('/user', function (Router $router) {
            $router->get('/dashboard', fn() => 'User Dashboard');

            $router->group('/profile', function (Router $router) {
                $router->get('/', fn() => 'User Profile');
                $router->get('/edit', fn() => 'Edit User');
                $router->put('/edit', fn() => 'Edit User');
                $router->delete('/delete', fn() => 'Delete User');
            });
        });

    });

    /** --------------------------------------------------------
     * Define routes for testing controller functionality
    ---------------------------------------------------------- */

});
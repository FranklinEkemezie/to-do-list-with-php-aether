<?php
declare(strict_types=1);

require_once ROOT_DIR . '/vendor/autoload.php';

use PHPAether\Core\HTTP\Router;
use PHPAether\Tests\TestControllers\BookController;
use PHPAether\Tests\TestControllers\HomeController;

return (function (Router $router) {

    /** --------------------------------------------------------
     * Define routes for testing routing functionality
     ---------------------------------------------------------- */
    $router->group('/tests', function (Router $router) {
        $router->get('/home', [HomeController::class, 'index']);

        $router->get('/books', [BookController::class, 'index']);
        $router->get('/books/:id', [BookController::class, 'getBook']);

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
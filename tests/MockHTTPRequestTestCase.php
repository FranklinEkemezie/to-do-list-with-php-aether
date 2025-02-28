<?php

namespace PHPAether\Tests;

use PHPUnit\Framework\TestCase;

class MockHTTPRequestTestCase extends TestCase
{

    protected array $routes;

    protected function setUp(): void
    {
        parent::setUp();

        // Routes
        $this->routes = [
            '/' => [
                'controller'    => 'Home',
                'action'        => 'index',
                'methods'       => ['GET']
            ],
            '/login' => [
                'controller'    => 'Auth',

                'GET'   => [
                    'action'    => 'loginView'
                ],
                'POST'  => [
                    'action'    => 'login'
                ]
            ]
        ];

        // Set up SERVER variables
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

    }

    public static function httpGETRequestTestCases(): array
    {
        return [

        ];
    }
    public static function httpPOSTRequestTestCases(): array
    {
        return [

        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clear Server variables
        unset(
            $_SERVER['REQUEST_URI'],
            $_SERVER['REQUEST_METHOD']
        );

    }
}
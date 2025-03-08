<?php

namespace PHPAether\Tests;

use PHPUnit\Framework\TestCase;

class MockHTTPRequestTestCase extends TestCase
{

    protected const TEST_ROUTES = [
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
        ],
        '/register' => [
            'controller'    => 'Auth',
            'action'        => 'register',
            'methods'       => ['POST']
        ],
        '/user/profile/update' => [
            'controller' => 'User',
            'GET'   => [
                'action' => 'userProfileView'
            ],
            'PUT'  => [
                'action' => 'updateUserProfile'
            ]
        ],
        '/user/dashboard' => [
            'controller'    => 'User',
            'action'        => 'userDashboardView'
        ]
    ];


    public static function setUpHTTPRequestTest(
        string $route, string $method
    ): void
    {
        $_SERVER['REQUEST_URI'] = $route;
        $_SERVER['REQUEST_METHOD'] = $method;
    }

    public static function httpRequestDataProvider(): \Generator
    {

        $dataProviders = [
            'GET'   => [self::class, 'httpGETRequestDataProvider'],
            'POST'  => [self::class, 'httpPOSTRequestDataProvider'],
            'PUT'   => [self::class, 'httPUTRequestDataProvider'],
        ];

        foreach ($dataProviders as $requestMethod => $dataProvider) {

            $testCases = $dataProvider();
            foreach ($testCases as $testCase) {
                $route = $testCase[0];
                $testCase = self::buildHTTPRequestDataProviderTestCase(
                    $requestMethod, ...$testCase
                );

                yield "HTTP Request Test Case [$requestMethod] $route" => $testCase;
            }
        }
    }

    public static function buildHTTPRequestDataProviderTestCase(
        string $requestMethod,
        string $route,
        string $expectedRoute,
        array $expectedAction,
        string $expectedResponse
    ): array
    {
        return [
            'route'     => $route,
            'method'    => strtoupper($requestMethod),
            // expected values
            'expected'  => [
                'route'     => $expectedRoute,
                'action'    => $expectedAction,
                'response'  => $expectedResponse
            ]
        ];
    }

    public static function httpGETRequestDataProvider(): array
    {
        return  [
            // [$route, $_route, $_action, $_response] (_ means "expected")
            ['/', '/', ['Home', 'index'], ''],
            ['/login?r_url=/user/dashboard', '/login', ['Auth', 'loginView'], ''],
            ['/user/dashboard', '/user/dashboard', ['User', 'userDashboardView'], ''],
            ['/leaderboard?league=ruby', '/leaderboard', ['Error', 'notFound'], '']
        ];
    }
    public static function httpPOSTRequestDataProvider(): array
    {
        return [
            // [$route, $_route, $_action, $_response] (_ means "expected")
            ['/register', '/register', ['Auth', 'register'], ''],
            ['/login', '/login', ['Auth', 'login'], ''],
            ['/cart', '/cart', ['Error', 'notFound'], '']
        ];
    }

    public static function httPUTRequestDataProvider(): array
    {
        return [
            // [$route, $_route, $_action, $_response] (_ means "expected")
            ['/user/profile/update', '/user/profile/update', ['User', 'updateUserProfile'], '']
        ];
    }

}
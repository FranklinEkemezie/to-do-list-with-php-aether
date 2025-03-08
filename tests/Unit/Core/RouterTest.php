<?php

declare(strict_types=1);

namespace PHPAether\Tests\Unit\Core;

use PHPAether\Core\Request;
use PHPAether\Core\Router;
use PHPAether\Exceptions\FileNotFoundException;
use PHPAether\Tests\MockHTTPRequestTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;

class RouterTest extends MockHTTPRequestTestCase
{

    protected Router $router;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = new Router();
        $this->router->registerRoutes(static::TEST_ROUTES);
    }

    #[Test]
    public function it_registers_routes_from_routes(): void
    {
        $router = new Router();
        $router->registerRoutes(static::TEST_ROUTES);

        $this->assertSame(
            static::TEST_ROUTES,
            $router->getRegisteredRoutes()
        );

    }

    /**
     * @throws FileNotFoundException
     */
    #[Test]
    public function it_registers_routes_from_route_file(): void
    {
        $router = new Router();

        $routeFilename = TESTS_DIR . "/config/routes.json";
        $router->registerRoutesFromRouteFile($routeFilename);

        $routes = json_decode(file_get_contents($routeFilename), true);
        $this->assertEquals($routes, $router->getRegisteredRoutes());
    }

    /**
     * @throws Exception
     */
    #[Test]
    #[DataProvider('httpRequestDataProvider')]
    public function it_routes_request(string $method, string $route, array $expected)
    {
        // Set up HTTP request test case
        static::setUpHTTPRequestTest($route, $method);

        ['action' => $expectedAction] = $expected;

        $requestMock = $this->getMockBuilder(Request::class)
            ->setConstructorArgs([$_SERVER])
            ->onlyMethods([])
            ->getMock()
        ;

        [$controllerName, $action] = $this->router->route($requestMock);
        $this->assertSame($expectedAction, [$controllerName, $action]);
    }
}
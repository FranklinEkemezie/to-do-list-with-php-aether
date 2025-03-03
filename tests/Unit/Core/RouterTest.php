<?php

declare(strict_types=1);

namespace PHPAether\Tests\Unit\Core;

use PHPAether\Core\Request;
use PHPAether\Core\Router;
use PHPAether\Tests\MockHTTPRequestTestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;

class RouterTest extends MockHTTPRequestTestCase
{

    protected Router $router;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = new Router();
        $this->router->registerRoutes($this->routes);
    }

    #[Test]
    public function it_registers_routes_from_routes(): void
    {
        $router = new Router();
        $router->registerRoutes($this->routes);

        $this->assertSame(
            $this->routes,
            $router->getRegisterRoutes()
        );

    }

    /**
     * @throws Exception
     */
    #[Test]
    public function it_routes_request()
    {

        $requestMock = $this->getMockBuilder(Request::class)->getMock();

        [$controllerName, $action] = $this->router->route($requestMock);

        $expected = ['Home', 'index'];
        $this->assertSame($expected, [$controllerName, $action]);
    }
}
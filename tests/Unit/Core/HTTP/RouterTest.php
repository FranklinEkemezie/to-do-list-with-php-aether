<?php
declare(strict_types=1);

namespace PHPAether\Tests\Unit\Core\HTTP;

use PHPAether\Core\HTTP\Router;
use PHPAether\Enums\HTTPRequestMethod;
use PHPAether\Enums\RequestType;
use PHPAether\Exceptions\RouterExceptions\MethodNotAllowedException;
use PHPAether\Exceptions\RouterExceptions\RouteNotFoundException;
use PHPAether\Exceptions\RouterExceptions\RouterException;
use PHPAether\Tests\MockHTTPRequestTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class RouterTest extends MockHTTPRequestTestCase
{

    protected Router $router;

    public function setUp(): void
    {
        parent::setUp();

        $this->router = new Router();
    }

    public static function registersRouteDataProvider(): array
    {
        $action1 = fn() => 'Action 1';
        $action2 = fn() => 'Action 2';

        return [
            [
                'route'     => '/index',
                'action'    => $action1,
                'middlewares'=> []
            ],
            [
                'route'     => '/books/:id',
                'action'    => $action2
            ]
        ];
    }

    public static function buildItRegistersRouteExpected(string $route, HTTPRequestMethod $method, callable $action, array $middlewares=[]): array
    {
        return [
            "$route"    => [
                'action'        => $action,
                'middlewares'   => $middlewares
            ]
        ];
    }

    #[Test]
    #[DataProvider('registersRouteDataProvider')]
    public function it_registers_get_route(string $route, callable $action, array $middlewares=[]): void
    {
        $router = $this->router->get($route, $action);
        $expected = static::buildItRegistersRouteExpected($route, HTTPRequestMethod::GET, $action, $middlewares);

        $this->assertSame($expected, $router->getRegisteredRoutes(RequestType::WEB, HTTPRequestMethod::GET));
    }

    #[Test]
    #[DataProvider('registersRouteDataProvider')]
    public function it_registers_post_route(string $route, callable $action, array $middlewares=[]): void
    {

        $router = $this->router->post($route, $action);
        $expected = static::buildItRegistersRouteExpected($route, HTTPRequestMethod::POST, $action, $middlewares);

        $this->assertSame($expected, $router->getRegisteredRoutes(RequestType::WEB, HTTPRequestMethod::POST));
    }

    #[Test]
    #[DataProvider('registersRouteDataProvider')]
    public function it_registers_put_route(string $route, callable $action, array $middlewares=[]): void
    {
        $router = $this->router->put($route, $action);
        $expected = static::buildItRegistersRouteExpected($route, HTTPRequestMethod::PUT, $action, $middlewares);

        $this->assertSame($expected, $router->getRegisteredRoutes(RequestType::WEB, HTTPRequestMethod::PUT));
    }

    #[Test]
    #[DataProvider('registersRouteDataProvider')]
    public function it_registers_patch_route(string $route, callable $action, array $middlewares=[]): void
    {
        $router = $this->router->patch($route, $action);
        $expected = static::buildItRegistersRouteExpected($route, HTTPRequestMethod::PATCH, $action, $middlewares);

        $this->assertSame($expected, $router->getRegisteredRoutes(RequestType::WEB, HTTPRequestMethod::PATCH));
    }

    #[Test]
    #[DataProvider('registersRouteDataProvider')]
    public function it_registers_delete_route(string $route, callable $action, array $middlewares=[]): void
    {
        $router = $this->router->delete($route, $action);
        $expected = static::buildItRegistersRouteExpected($route, HTTPRequestMethod::DELETE, $action, $middlewares);

        $this->assertSame($expected, $router->getRegisteredRoutes(RequestType::WEB, HTTPRequestMethod::DELETE));
    }

    #[Test]
    public function it_registers_web_routes_from_file(): void
    {
        $this->router->registerWebRoutesFromFile(TESTS_DIR . '/config/routes/web.php');
        $this->assertNotEmpty($this->router->getRegisteredRoutes(RequestType::WEB));
    }

    #[Test]
    public function it_registers_api_routes_from_file(): void
    {
        $this->router->registerApiRoutesFromFile(TESTS_DIR . '/config/routes/api.php');
        $this->assertNotEmpty($this->router->getRegisteredRoutes(RequestType::API));
    }

    #[Test]
    public function it_registers_cli_routes_from_file(): void
    {
        $this->router->registerCliRoutesFromFile(TESTS_DIR . '/config/routes/cli.php');
        $this->assertNotEmpty($this->router->getRegisteredRoutes(RequestType::CLI));
    }

    /**
     * @param callable $requestBuilder
     * @param array|null $expected
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     */
    #[Test]
    #[DataProvider('mockHTTPRequestTestCases')]
    public function it_routes_request_to_route(callable $requestBuilder, ?array $expected=[])
    {
        $expectedRoute = $expected['router']['route'] ?? null;

        // Mock a request object; register routes
        $request = $requestBuilder($this);
        $this->router->registerRoutesFromFiles(TESTS_DIR . '/config/routes');

        $actualRoute = $this->router->route($request)['route'];
        $this->assertSame($expectedRoute, $actualRoute);
    }

    /**
     * @throws RouterException
     */
    #[Test]
    public function it_throws_method_not_allowed_for_invalid_routes(): void
    {
        // Register routes
        $this->router->post('/tests/some/login', fn() => '');

        // Hit the route but with a different method
        $request = $this->createMockRequest('/tests/some/login', 'GET');

        $this->expectException(MethodNotAllowedException::class);
        $this->router->route($request);
    }

    /**
     * @throws RouterException
     */
    #[Test]
    public function it_throws_route_not_found_for_invalid_routes(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $request = $this->createMockRequest('/tests/some/products/2', 'GET');
        $this->router->route($request);
    }

    /**
     * @throws RouterException
     */
    #[Test]
    public function it_matches_wildcard_route(): void
    {
        // Register a wildcard route
        $this->router->get('/tests/books/:id', fn() => '');
        $request = $this->createMockRequest('/tests/books/5', 'GET');

        $this->assertEquals(['id' => 5], $this->router->route($request)['params']);
    }
}
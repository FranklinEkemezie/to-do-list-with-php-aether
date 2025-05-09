<?php
declare(strict_types=1);

namespace PHPAether\Tests\Unit\Core\HTTP;

use Exception;
use PHPAether\Core\HTTP\Request;
use PHPAether\Tests\MockHTTPRequestTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class RequestTest extends MockHTTPRequestTestCase
{

    /**
     * @throws Exception
     */
    public static function getRequest(string $url, string $method): Request
    {
        $_SERVER['REQUEST_URI']     = $url;
        $_SERVER['REQUEST_METHOD']  = $method;

        return new Request($_SERVER);
    }

    /**
     * @throws Exception
     */
    #[Test]
    #[DataProvider('mockHTTPRequestTestCases')]
    public function it_gets_route_path(callable $requestBuilder, array $expected): void
    {
        $expectedPath = static::getExpectedValue($expected, 'request', 'path');
        $this->assertSame($expectedPath, $requestBuilder($this)->path);
    }

    /**
     * @throws Exception
     */
    #[Test]
    #[DataProvider('mockHTTPRequestTestCases')]
    public function it_gets_route_params(callable $requestBuilder, array $expected): void
    {
        $expectedParams = static::getExpectedValue($expected, 'request', 'params', []);

        $request = $requestBuilder($this);

        print_r($request->getData());
        static::$APP->router->route($request);

        $this->assertSame($expectedParams, $request->getData());
    }
}

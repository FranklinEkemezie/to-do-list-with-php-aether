<?php

namespace Core\HTTP;

use PHPAether\Core\HTTP\Request;
use PHPAether\Tests\MockHTTPRequestTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class RequestTest extends MockHTTPRequestTestCase
{

    public static function requestTestCases(): array
    {
        return [
            ['/', 'GET', '/'],
            ['/login?r_url=/user/dashboard', 'POST', '/login'],
            ['/auth/otp/verify', 'GET', '/auth/otp/verify'],
            ['/user/profile', 'PUT', '/user/profile']
        ];
    }

    /**
     * @throws \Exception
     */
    #[Test]
    #[DataProvider('requestTestCases')]
    public function it_gets_route_path(
        string $requestUri,
        string $requestMethod,
        string $expectedRoutePath
    )
    {
        $_SERVER['REQUEST_METHOD'] = $requestMethod;
        $_SERVER['REQUEST_URI'] = $requestUri;

        $request = new Request($_SERVER);

        $this->assertSame($expectedRoutePath, $request->path);
    }

}

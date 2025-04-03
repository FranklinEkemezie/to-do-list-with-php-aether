<?php

namespace Core\HTTP;

use PHPAether\Core\HTTP\Request;
use PHPAether\Tests\MockHTTPRequestTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class RequestTest extends MockHTTPRequestTestCase
{


    /**
     * @throws \Exception
     */
    #[Test]
    #[DataProvider('httpRequestDataProvider')]
    public function it_gets_route_and_method(
        string $route, string $method, array $expected
    )
    {
        // Set up HTTP request test case
        static::setUpHTTPRequestTest($route, $method);

        ['route' => $expectedRoute] = $expected;

        $request = new Request($_SERVER);
        $this->assertSame($expectedRoute, $request->route);
    }

}

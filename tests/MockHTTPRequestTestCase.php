<?php

namespace PHPAether\Tests;

use PHPAether\Core\HTTP\Request;
use PHPUnit\Framework\MockObject\MockObject;

class MockHTTPRequestTestCase extends BaseTestCase
{

    public static function mockHTTPRequestTestCases(): \Generator
    {
        $testUrls = require TESTS_DIR . '/urls/urls.php';
        foreach ($testUrls as $requestMethod => $testUrlTestCases) {

            foreach ($testUrlTestCases as $testUrlTestCase) {
                $testUrlTestCase['method'] = $requestMethod;

                $url = $testUrlTestCase['url'];
                yield "[$requestMethod] $url" => $testUrlTestCase;
            }
        }
    }

    public function createMockRequest(string $url, string $method): Request&MockObject
    {
        // Prepare server variables
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['REQUEST_METHOD'] = $method;

        return $this->getMockBuilder(Request::class)
            ->setConstructorArgs([$_SERVER])
            ->getMock();
    }
}
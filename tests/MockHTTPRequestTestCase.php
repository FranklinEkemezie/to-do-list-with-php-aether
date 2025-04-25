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

                $url            = $testUrlTestCase['url'];
                $data           = $testUrlTestCase['info']['data'] ?? [];
                $requestBuilder = fn(self $testCase): Request => (
                    $testCase->createMockRequest($url, $requestMethod, $data)
                );

                yield "[$requestMethod] $url" => [
                    'requestBuilder'    => $requestBuilder,
                    'expected'          => $testUrlTestCase['expected']
                ];
            }
        }
    }

    public function createMockRequest(string $url, string $method, array $data=[]): Request&MockObject
    {
        // Prepare server variables
        $_SERVER['REQUEST_URI'] = $url;
        $_SERVER['REQUEST_METHOD'] = $method;

        if ($method === 'GET') $_GET = $data;
        else $_POST = $data;

        return $this->getMockBuilder(Request::class)
            ->setConstructorArgs([$_SERVER])
            ->getMock();
    }

    public static function getExpectedValue(array $expected, string $category, string $key, mixed $default=null): mixed
    {
        return $expected[$category][$key] ?? $default;
    }
}
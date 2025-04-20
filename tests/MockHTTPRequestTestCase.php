<?php

namespace PHPAether\Tests;

use PHPAether\Core\App;
use PHPAether\Core\HTTP\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MockHTTPRequestTestCase extends TestCase
{
    protected static App $APP;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Bootstrap application
        static::$APP = require_once __DIR__ . '/bootstrap/bootstrap.php';
    }

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
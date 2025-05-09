<?php
declare(strict_types=1);

namespace PHPAether\Tests\Unit\Views;

use PHPAether\Tests\MockHTTPRequestTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ViewTest extends MockHTTPRequestTestCase
{

    public static function viewTestDataProvider(): \Generator
    {
        foreach (static::mockHTTPRequestTestCases() as $testCase) {
            ['requestBuilder' => $requestBuilder, 'expected' => $expected] = $testCase;

            $viewBuilder = fn(self $test) => (
                static::$APP->run($requestBuilder($test))->body
            );

            yield [
                'viewBuilder'   => $viewBuilder,
                'expected'      => $expected['view'] ?? []
            ];
        }
    }

    #[Test]
    #[DataProvider('viewTestDataProvider')]
    public function it_renders_view(callable $viewBuilder, array $expected): void
    {
        $this->assertSame($expected['body'] ?? null, (string) $viewBuilder($this));
    }
}


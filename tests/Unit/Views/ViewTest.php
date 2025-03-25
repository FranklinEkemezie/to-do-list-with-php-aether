<?php

declare(strict_types=1);

namespace PHPAether\Tests\Unit\Views;

use PHPUnit\Framework\Attributes\Test;

use PHPAether\Tests\BaseTestCase;
use PHPAether\Views\View;

class ViewTest extends BaseTestCase
{

    #[Test]
    public function it_gets_view(): void
    {
        $props = [
            "name"      => "John",
            "email"     => "john@doe.com"
        ];

        $view = new View('dashboard', TESTS_DIR . "/views/");

        $expected = "Welcome, John (john@doe.com)!";
        $this->assertSame(
            $expected,
            $view->render($props)
        );

    }

}


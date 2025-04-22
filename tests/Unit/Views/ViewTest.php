<?php

declare(strict_types=1);

namespace PHPAether\Tests\Unit\Views;

use PHPUnit\Framework\Attributes\Test;

use PHPAether\Tests\BaseTestCase;
use PHPAether\Views\View;

class ViewTest extends BaseTestCase
{

    #[Test]
    public function it_parses_variable(): void
    {
        $address = [
            'city'      => "Diao City",
            'zip'       => "456123"
        ];
        $props = [
            'name'      => "John",
            'email'     => "john@doe.com",
            'user'      => [
                'id'    => "23abc"
            ],
            'address'   => $address
        ];

        $viewTestDir = TESTS_DIR . "/views";
        $view = new View('variables', $viewTestDir);
        $expectedView = new View('variables_expected', $viewTestDir);

        $this->assertSame(
            $expectedView->render(),
            $view->render($props)
        );

    }

    #[Test]
    public function it_parses_for_loops(): void
    {
        $address = [
            'city'      => "Diao City",
            'zip'       => "456123"
        ];
        $props = [
            'name'      => "John",
            'email'     => "john@doe.com",
            'user'      => [
                'id'    => "23abc"
            ],
            'address'   => $address
        ];

        $viewTestDir = TESTS_DIR . "/views";
        $view = new View('loop', $viewTestDir);
        $expectedView = new View('loop_', $viewTestDir);

        $this->assertSame(
            $expectedView->render(),
            $view->render($props)
        );

    }



}


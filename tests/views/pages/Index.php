<?php

namespace PHPAether\Tests\views\pages;

use PHPAether\Views\View;

class Index extends View {


    function render(): string
    {
        return 'Home Page';
    }
}
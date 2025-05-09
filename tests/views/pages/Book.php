<?php

namespace PHPAether\Tests\views\pages;

use PHPAether\Views\View;

/**
 * @property $id
 */
class Book extends View {


    public function render(): string
    {
        return 'Book with ID: ' . $this->id;
    }
}
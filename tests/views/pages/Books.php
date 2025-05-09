<?php

namespace PHPAether\Tests\views\pages;

use PHPAether\Views\View;

class Books extends View {

    public function render(): string
    {
        $ids =  array_map(function (array $bookProps): string {
            return $bookProps['id'];
        }, $this->props['books']);

        return 'Books with IDs: ' . join(', ', $ids);
    }
}
<?php

namespace App\Views\Components;

use PHPAether\Views\Component;

class AuthInputField extends Component
{

    public function render(): string
    {
        return <<<HTML
        <div class="input-field">
            <label>{$this->label}</label>
            <input type="{$this->type}" name="{$this->name}" placeholder="{$this->placeholder}" autocomplete="off" />
        </div>
        HTML;
    }
}

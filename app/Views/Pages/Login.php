<?php

namespace App\Views\Pages;

use App\Views\Components\AuthInputField;
use App\Views\Layouts\AuthLayout;

class Login extends AuthLayout
{

    public function pageTitle(): string
    {
        return "Login";
    }

    public function formTitle(): string
    {
        return "Welcome Back";
    }

    public function formSubtitle(): string
    {
        return "Login to manage your tasks";
    }

    public function inputFields(): string
    {
        $inputFields = [
            'email'     => new AuthInputField(['type' => 'email', 'name' => 'email', 'label' => 'Email', 'placeholder' => 'john@doe.com']),
            'password'  => new AuthInputField(['type' => 'password', 'name' => 'password', 'label' => 'Password',])
        ];

        return <<<HTML
        <!-- Email -->
        {$inputFields['email']}

        <!-- Password -->
        {$inputFields['password']}
        HTML;
    }

    public function footnote(): array
    {
        return [
            "Don't have an account?",
            "/register",
            "Register"
        ];
    }
}

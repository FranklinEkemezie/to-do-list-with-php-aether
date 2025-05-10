<?php

namespace App\Views\Pages;

use App\Views\Components\AuthInputField;
use App\Views\Layouts\AuthLayout;

class Register extends AuthLayout
{

    public function pageTitle(): string
    {
        return "Register";
    }

    public function formTitle(): string
    {
        return "Get Started";
    }

    public function formSubtitle(): string
    {
        return "Create your account to manage your tasks";
    }


    public function inputFields(): string
    {
        $inputFields = [
            'username'  => new AuthInputField(['type' => 'text', 'name' => 'username', 'label' => 'Username', 'placeholder' => 'JohnDoe13']),
            'email'     => new AuthInputField(['type' => 'email', 'name' => 'email', 'label' => 'Email', 'placeholder' => 'john@doe.com']),
            'password'  => new AuthInputField(['type' => 'password', 'name' => 'password', 'label' => 'Password'])
        ];

        return <<<HTML
        <!-- Username -->
        {$inputFields['username']}

        <!-- Email -->
        {$inputFields['email']}

        <!-- Password -->
        {$inputFields['password']}
        HTML;
    }

    public function footnote(): array
    {
        return [
            "Already have an account?",
            "/login",
            "Log In"
        ];
    }
}

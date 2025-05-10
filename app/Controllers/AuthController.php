<?php

declare(strict_types=1);

namespace App\Controllers;

use PHPAether\Controllers\Controller;
use PHPAether\Core\HTTP\Request;
use PHPAether\Core\HTTP\Response;
use PHPAether\Enums\ResponseStatus;
use App\Views\Pages\Login;
use App\Views\Pages\Register;

class AuthController extends Controller
{

    public function registerView(): Response
    {
        return new Response(ResponseStatus::OK, new Register());
    }

    public function register(): Response
    {
        return new Response(ResponseStatus::OK, 'Register');
    }

    public function loginView(Request $request): Response
    {
        return new Response(ResponseStatus::OK, new Login());
    }

    public function login(Request $request): Response
    {
        return new Response(ResponseStatus::OK, 'Login');
    }
}

<?php

namespace PHPAether\Tests\TestControllers;

use PHPAether\Attributes\Route;
use PHPAether\Controllers\Controller;
use PHPAether\Core\Response;
use PHPAether\Enums\RequestMethod;

class AuthController extends Controller
{

    #[Route(RequestMethod::GET, "/login")]
    #[Route(RequestMethod::GET, "/auth/login")]
    public function loginView(): Response
    {
        return new Response(body: "Login Form");
    }

    #[Route(RequestMethod::POST, "/login")]
    #[Route(RequestMethod::POST, "/auth/login")]
    public function login(): Response
    {
        return new Response(body: "Login successful");
    }

    #[Route(RequestMethod::GET, "/register")]
    #[Route(RequestMethod::GET, "/auth/register")]
    public function registerView(): Response
    {
        return new Response(body: "Register Form");
    }

    #[Route(RequestMethod::POST, "/register")]
    #[Route(RequestMethod::POST, "/auth/register")]
    #[Route(RequestMethod::PUT, "/register")]
    #[Route(RequestMethod::PUT, "/auth/register")]
    public function register(): Response
    {
        return new Response(body: "Registration successful");
    }

}
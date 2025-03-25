<?php

namespace PHPAether\Tests\TestControllers;

use PHPAether\Attributes\Route;
use PHPAether\Controllers\Controller;
use PHPAether\Core\Response;
use PHPAether\Enums\RequestMethod;

class HomeController extends Controller
{
    #[Route(RequestMethod::GET, "/")]
    #[Route(RequestMethod::GET, "/index")]
    public function index(): Response
    {
        return new Response(body: "Welcome");
    }

    #[Route(RequestMethod::GET, "/about")]
    public function about(): Response
    {
        return new Response(body: "About");
    }
}
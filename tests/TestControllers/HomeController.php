<?php

namespace PHPAether\Tests\TestControllers;

use PHPAether\Controllers\Controller;
use PHPAether\Core\HTTP\Request;
use PHPAether\Core\HTTP\Response;
use PHPAether\Enums\ResponseStatus;
use PHPAether\Tests\views\pages\Index;
use PHPAether\Views\View;

class HomeController extends Controller
{

    public function index(): Response
    {
        return new Response(ResponseStatus::OK, new Index());
    }
}
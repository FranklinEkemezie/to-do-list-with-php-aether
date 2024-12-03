<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\Response;

class HomeController extends Controller
{

    public function index(): Response
    {
        echo 'Hello from index Home controller <br/>';
        return new Response;
    }
}
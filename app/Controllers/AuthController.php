<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\Response;
use FranklinEkemezie\PHPAether\Services\AuthServices\AuthServiceInterface;

class AuthController extends BaseController
{

    private AuthServiceInterface $authService;

    public function __construct(
    )
    {

        $this->authService = new (APP_CONFIG['AUTH_SERVICE'])();
    }



    public function getLogin(): Response
    {

        // Return a LOGIN view page here
        return new Response();
    }

    public function login(): Response
    {

        // Login a user here
        echo 'Logging user in... <br/>';
        echo 'Using auth service: <br/>';
        print_r($this->authService); echo '<br/>';

        return new Response();
    }


}


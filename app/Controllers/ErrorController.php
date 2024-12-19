<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\Response;

abstract class ErrorController extends BaseController
{

    public static function  notFound(): Response
    {
        // TODO
        echo 'Not Found <br/>';
        http_response_code(404);
        return new Response;
    }

    public static function methodNotAllowed(array $allowedMethods): Response
    {
        http_response_code(405);

        echo 'Method not allowed <br/>';
        return new Response;
    }

    public static function internalServerError(string $errorMsg): Response
    {
        // TODO
        http_response_code(500);

        echo "$errorMsg <br/>";
        return new Response;
    }

    public static function unauthorised(): Response
    {

        // TODO
        http_response_code(401);

        echo "Authentication required <br/>";
        return new Response;
    }

}
<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\Response;

abstract class ErrorController extends BaseController
{

    // 4xx Client Errors

    /**
     * Send a 400 Bad Request Response.
     * The client sent an invalid request (e.g., missing/invalid parameters).
     * @param mixed $errorMsg
     * @param mixed $errors
     * @return \FranklinEkemezie\PHPAether\Core\Response
     */
    public static function badRequest(?string $errorMsg=null, $errors=[]): Response
    {
        return new Response(200, body: json_encode([
            'status'    => 'error',
            'message'   => $errorMsg ?? 'Invalid request',
            'errors'    => $errors
        ]));
    }

    /**
     * Send a 401 Unauthorised response
     * Authentication failed or token is missing/invalid
     * @return \FranklinEkemezie\PHPAether\Core\Response
     */
    public static function unauthorised(?string $errorMsg=null): Response
    {

        return new Response(401, body: json_encode([
            'status'    => 'error',
            'message'   => $errorMsg ?? 'Authentication required or invalid credentials'
        ]));
    }

    /**
     * Send a 403 Forbidden response
     * User does not have permissiont to access the resource
     * @param mixed $errorMsg
     * @return \FranklinEkemezie\PHPAether\Core\Response
     */
    public static function forbidden(?string $errorMsg=null): Response
    {
        return new Response(403, body: json_encode([
            'status'    => 'error',
            'message'   => $errorMsg ?? 'You do not have permission to perform this action'
        ]));
    }

    /**
     * Send a 404 Not Found response
     * Resource not found
     * @param mixed $errorMsg
     * @return \FranklinEkemezie\PHPAether\Core\Response
     */
    public static function  notFound(?string $errorMsg=null): Response
    {
        return new Response(404, body: json_encode([
            'status'    => 'error',
            'message'   => $errorMsg ?? 'The requested resource could not be found'
        ]));
    }

    /**
     * Send a 405 Method Not Allowed Response
     * The resource is found but the request method is not allowed.
     * @param mixed $errorMsg
     * @param array $allowedMethods
     * @return \FranklinEkemezie\PHPAether\Core\Response
     */
    public static function methodNotAllowed(?string $errorMsg=null, array $allowedMethods=[]): Response
    {
        $allowedMethods = array_map(fn(string $method) => strtoupper($method), $allowedMethods);
        $responseBody   = [
            'status'    => 'error',
            'message'   => $errorMsg ?? 'Method not allowed',
            'allowed_methods' => $allowedMethods
        ];

        return (new Response(405, body: json_encode($responseBody)))
            ->setHeader('Allow', implode(', ', $allowedMethods))
        ;
    }

    // 5xx Server Errors

    /**
     * Send a 500 Internal Server Error
     * A general error occurred on the server
     * @param mixed $errorMsg
     * @return \FranklinEkemezie\PHPAether\Core\Response
     */
    public static function internalServerError(?string $errorMsg=null): Response
    {
        return new Response(500, body: json_encode([
            'status'    => 'error',
            'error'     => $errorMsg ?? 'An unexpected error occurred on the server'
        ]));
    }

    /**
     * Send a 503 Service Unavailable Response.
     * Server is down or overloaded.
     * @param mixed $errorMsg
     * @return \FranklinEkemezie\PHPAether\Core\Response
     */
    public static function serviceUnavailable(?string $errorMsg=null): Response
    {
        return new Response(503, body: json_encode([
            'status'    => 'error',
            'message'   => $errorMsg ?? 'The service is temporarily unavailable'
        ]));
    }

}
<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\Response;

abstract class ErrorController extends BaseController
{

    // 4xx Client Errors

    /**
     * Send a 400 Bad Request Response.

     * Use case: The client sent an invalid request (e.g., missing/invalid parameters).
     * @param mixed $errorMsg
     * @param mixed $errors
     * @return Response
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

     * Use case: Authentication failed or token is missing/invalid
     * @return Response
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

     * Use case: User does not have permissiont to access the resource
     * @param mixed $errorMsg
     * @return Response
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

     * Use case: Resource not found
     * @param mixed $errorMsg
     * @return Response
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

     * Use case: The resource is found but the request method is not allowed.
     * @param mixed $errorMsg
     * @param array $allowedMethods
     * @return Response
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

    /**
     * Send a 422 Unprocessable Entity

     * Use case: Validation errors in the request
     * @param mixed $errorMsg
     * @param array $errors
     * @return Response
     */
    public static function unprocessableEntity(?string $errorMsg=null, array $errors): Response
    {
        return new Response(422, body: json_encode([
            'status'    => 'error',
            'message'   => $errorMsg ?? 'Validation errors occurred',
            'errors'    => $errors
        ]));
    }

    // 5xx Server Errors

    /**
     * Send a 500 Internal Server Error

     * Use case: A general error occurred on the server
     * @param mixed $errorMsg
     * @return Response
     */
    public static function internalServerError(?string $errorMsg=null): Response
    {
        return new Response(500, body: json_encode([
            'status'    => 'error',
            'message'   => $errorMsg ?? 'An unexpected error occurred on the server'
        ]));
    }

    /**
     * Send a 503 Service Unavailable Response.

     * Use case: Server is down or overloaded.
     * @param mixed $errorMsg
     * @return Response
     */
    public static function serviceUnavailable(?string $errorMsg=null): Response
    {
        return new Response(503, body: json_encode([
            'status'    => 'error',
            'message'   => $errorMsg ?? 'The service is temporarily unavailable'
        ]));
    }

}
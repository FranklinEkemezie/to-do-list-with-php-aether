<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Controllers;

use FranklinEkemezie\PHPAether\Core\{
    Database,
    Request,
    Response
};


abstract class BaseController
{

    public function __construct(
        protected Request $request,
        protected Database $database
    )
    {
        
    }

    /**
     * Send a 200 OK response

     * Use case: Request succeeded, and the response contains the requested data
     * @param mixed $message
     * @param array $payload
     * @return Response
     */
    protected static function respondOK(?string $message=null, array $payload): Response
    {
        return new Response(body: json_encode([
            'status'=> 'success',
            'data'  => [
                'message'   => $message ?? 'Request processed successfully',
                'payload'   => $payload
            ]
        ]));
    }

    /**
     * Send a 201 Create Response

     * Use case: Resource was successfully created
     * @param mixed $message
     * @param string|int $resourceId
     * @return Response
     */
    protected static function respondCreated(?string $message=null, string $resourceNameId='id', string|int $resourceId): Response
    {
        return new Response(201, body: json_encode([
            'status'    => 'success',
            'data'      => [
                'message'       => $message ?? 'Resource created successfully',
                $resourceNameId => $resourceId
            ]
        ]));
    }

    /**
     * Send a 204 No Content

     * Use case: Request processed successfully, but no data is returned
     * @return Response
     */
    protected static function responseNoContent(): Response
    {
        return new Response(204);
    }

}
<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Core;

class App
{

    public function __construct(
        private Router $router,
        private Database $database
    )
    {

    }

    public function run(Request $request): Response
    {
        // Route the request
        $handler = $this->router->route($request);

        // Execute the handler
        try {
            $response = $handler($this->database);
        } catch (\Exception $e) {
            throw new \Exception("Failed to run app: {$e->getMessage()}", (int) $e->getCode());
        }

        return $response;
    }
}
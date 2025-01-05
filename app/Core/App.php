<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Core;

use FranklinEkemezie\PHPAether\Controllers\ErrorController;
use FranklinEkemezie\PHPAether\Utils\Dictionary;

class App
{


    public function __construct(
        private Router $router,
        private Database $database,
        private Dictionary $env
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
            $errorMsg = $this->isDev() && $this->isInDebugMode() ? 
                $e->getMessage() . "\n" .  $e->getTraceAsString() : null
            ;

            return ErrorController::internalServerError($errorMsg);
        }

        return $response;
    }

    public function isDev(): bool
    {
        return $this->env->get('APP_ENV') === 'development';
    }

    public function isProd(): bool
    {
        return $this->env->get('APP_ENV') === 'production';
    }

    public function isInDebugMode(): bool
    {
        return $this->env->get('APP_DEBUG') === 'true';
    }
}
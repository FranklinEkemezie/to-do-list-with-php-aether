<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Middlewares;

use FranklinEkemezie\PHPAether\Controllers\ErrorController;
use FranklinEkemezie\PHPAether\Core\Request;
use FranklinEkemezie\PHPAether\Services\AuthServices\AuthServiceInterface;
use FranklinEkemezie\PHPAether\Services\AuthServices\JWTAuthService;

class AuthMiddleware extends Middleware
{

    public function __construct(
        private ?AuthServiceInterface $authService=null
    )
    {
        $this->authService ??= new JWTAuthService;
    }

    /**
     * Handles the authentication
     * @param \FranklinEkemezie\PHPAether\Core\Request $request The request to authenticate
     * @return true|callable Returns TRUE if authentication is passed, or controller callable when failed
     */
    public function handle(Request $request): true|callable
    {
        $token = $request->authToken;
        if (! $token || !$this->authService->verifyToken($token)) {

            return [ErrorController::class, 'unauthorised'];
        }
        
        return true;
    }
}
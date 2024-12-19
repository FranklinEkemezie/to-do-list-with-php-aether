<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Services\AuthServices;

use FranklinEkemezie\PHPAether\Entities\AbstractEntities\Authenticable;
use FranklinEkemezie\PHPAether\Utils\SessionManager;

/**
 * SessionAuthService class
 * 
 * Session Authentication service using Session based authentication
 */
class SessionAuthService implements AuthServiceInterface
{

    private SessionManager $sessionManager;

    public function __construct()
    {
        $this->sessionManager = new SessionManager();
    }

    public function authenticate(Authenticable $user): bool
    {
        // TODO
        return false;
    }

    public function generateToken(int|string $userId, int $expiresAfter=24*60*60): string
    {

        // Token feature is typically not used in session based authentication
        // Implementation coming later
        return '';
    }

    public function verifyToken(string $token): array|null
    {
        // Token feature is typically not used in session based authentication
        // Implementation coming later
        return null;
    }

    public function isAuthenticated(): bool
    {
        // TODO
        $authInfo = $this->sessionManager::get('auth');
        return false;
    }

    public function hasRole(): bool
    {
        // TODO
        return false;
    }

    public function hasPermission(): bool
    {
        // TODO
        return false;
    }

    public function logout(): void
    {
        $this->sessionManager::clear('auth');
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
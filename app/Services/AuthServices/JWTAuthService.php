<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Services\AuthServices;

/**
 * JWTAuthService class
 * 
 * JWT Authentication service using JWT web authentication
 */
class JWTAuthService implements AuthServiceInterface
{

    public function authenticate(): bool
    {
        return false;
    }

    public function generateToken(): string
    {
        // TODO
        return '';
    }

    public function verifyToken(string $token): array|null
    {
        // TODO
        return null;
    }

    public function isAuthenticated(): bool
    {
        // TODO
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
        // TODO
    }

    public function hashPassword(string $password): string
    {
        // TODO
        return '';
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        // TODO
        return false;
    }
}
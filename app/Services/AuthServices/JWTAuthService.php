<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Services\AuthServices;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use FranklinEkemezie\PHPAether\Entities\AbstractEntities\Authenticable;
use FranklinEkemezie\PHPAether\Exceptions\UndefinedException;

/**
 * JWTAuthService class
 * 
 * JWT Authentication service using JWT web authentication
 */
class JWTAuthService implements AuthServiceInterface
{

    private string $jwtSecret;

    public function __construct()
    {
        $jwtSecret = $_ENV['JWT_SECRET'] ?? null;
        if ($jwtSecret === null) {
            throw new UndefinedException("Environment variable 'JWT_SECRET' not found in .env");
        }

        $this->jwtSecret = $jwtSecret;
    }

    public function authenticate(Authenticable $user): bool
    {
        return false;
    }

    public function generateToken(int|string $userId, int $expiresAfter=24*60*60): string
    {
        $payload = [
            'iss'   => $_ENV['APP_NAME'],
            'sub'   => $userId,
            'iat'   => time(),
            'exp'   => time() + $expiresAfter
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function verifyToken(string $token): ?array
    {
        try {
            return (array) JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
        } catch (\Exception) {
            return null;
        }
    }

    public function isAuthenticated(): bool
    {
        return $this->isAuthenticated() !== null;
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
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
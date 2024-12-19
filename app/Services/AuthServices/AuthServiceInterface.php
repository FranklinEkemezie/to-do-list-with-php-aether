<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Services\AuthServices;

use FranklinEkemezie\PHPAether\Entities\AbstractEntities\Authenticable;

interface AuthServiceInterface
{

    /**
     * Authenticate a user
     * @return bool
     */
    public function authenticate(Authenticable $user): bool;


    /**
     * Generate an authentication token for a given user
     * after successful login
     * @param int|string $userId The ID of the user
     * @param int $expiresAfter The number of seconds after which the token expires
     * @return string
     */
    public function generateToken(int|string $userId, int $expiresAfter=24*60*60): string;


    /**
     * Verifies the validity of a token
     * @param string $token
     * @return void
     */
    public function verifyToken(string $token): ?array;


    /**
     * Checks if the current request has a valid authenticated user
     * @return bool
     */
    public function isAuthenticated(): bool;


    /**
     * Checks if the authenticated user has the specified role.
     * @return bool
     */
    public function hasRole(): bool;


    /**
     * Checks if the user has a specific permission
     * @return bool
     */
    public function hasPermission(): bool;

    
    /**
     * Logs the current user out. Invalidates the current
     * session or token
     * @return void
     */
    public function logout(): void;


    /**
     * Hashes a password securely
     * @param string $password
     * @return string
     */
    public function hashPassword(string $password): string;


    /**
     * Verfies if a given password matches a hash
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword(string $password, string $hash): bool;

    
}
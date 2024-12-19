<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Utils;

use FranklinEkemezie\PHPAether\Exceptions\UndefinedException;

class SessionManager
{


    /**
     * Check if a session variable exists
     * @param string $varName
     * @return bool
     */
    public static function has(string $varName): bool
    {
        return isset($_SESSION[$varName]);
    }

    /**
     * Get the value of a session variable
     * @param string $varName
     * @return mixed
     */
    public static function get(string $varName, $throwError=false): mixed
    {
        if(! self::has($varName) && $throwError)
            throw new UndefinedException("Session variable with name '$varName' does not exist");

        return $_SESSION[$varName] ?? null;
    }

    /**
     * Clear a session variable
     * @param string $varName The name of the session variable
     * @return void
     */
    public static function clear(string $varName): void
    {
        unset($_SESSION[$varName]);
        return;
    }


    /**
     * Destroy the Session
     * @return void
     */
    public static function destroy(): void
    {
        session_unset();
    }
}

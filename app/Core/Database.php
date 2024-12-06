<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Core;

use FranklinEkemezie\PHPAether\Exceptions\DatabaseException;
use FranklinEkemezie\PHPAether\Exceptions\UndefinedException;
use PDO;

/**
 * Database class
 * 
 * Creates a connection to the database
 * 
 * A simple wrapper around PDO
 */
class Database
{

    private static ?PDO $dbConn = null;

    /**
     * Database class constructor
     * 
     * @param array $dbConfig An associative array of configuration data for the
     * database connection. `$dbConfig` must have the following keys:
     * - `driver`   - The database driver to use e.g. mysql
     * - `host`     - The server hosting the database - localhost. You can specify the IP address instead, if available
     * - `port`     - The port to connect to (optional, the default is used)
     * - `database` - The name of the database to connect to.
     * - `username` - The name of the database user
     * - `password` - The password of the database user
     */
    public function __construct(array $dbConfig)
    {

        if (isset(static::$dbConn))
            return;

        // Get the database config data
        $driver     = $dbConfig['driver'] ?? null;
        $host       = $dbConfig['host'] ?? null;
        $port       = $dbConfig['port'] ?? 3306;
        $database   = $dbConfig['database'] ?? null;
        $username   = $dbConfig['username'] ?? null;
        $password   = $dbConfig['password'] ?? null;
        $options    = $dbConfig['options'] ?? [];

        if ($port !== null) $host .= ":$port";

        $dbConfigIsValid = array_reduce(
            [$driver, $host, $database, $username, $password],
            function(bool $carry, string $item): bool {
                if ($carry === false) return false;
                return $item !== null;
            },
            true
        );

        if (! $dbConfigIsValid) {
            throw new \InvalidArgumentException('Parameter #1 ($dbConfig) does not contain the required database configuration');
        }

        $dsn = "{$driver}:host={$host};dbname={$database}";
        try {
            $dbConn = new PDO($dsn, $username, $password, $options);

            // set the PDO default attributes
            $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbConn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $dbConn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            static::$dbConn = $dbConn;

        } catch (\PDOException $e) {

            throw new DatabaseException("Database connection failed: {$e->getMessage()}");
        }
        
    }

    // Proxy database calls to PDO
    public function __call(string $method, array $args)
    {
        if (method_exists(static::$dbConn, $method)) {
            return call_user_func_array([static::$dbConn, $method], $args);
        }

        throw new UndefinedException('Undefined method: ' . __CLASS__ . '::$' . $method);
    }

}
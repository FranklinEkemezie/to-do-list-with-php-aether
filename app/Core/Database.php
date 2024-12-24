<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Core;

use FranklinEkemezie\PHPAether\Exceptions\DatabaseException;
use FranklinEkemezie\PHPAether\Exceptions\DuplicateEntryException;
use FranklinEkemezie\PHPAether\Exceptions\UndefinedException;
use FranklinEkemezie\PHPAether\Utils\Dictionary;
use FranklinEkemezie\PHPAether\Utils\QueryBuilder\QueryBuilder;
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
    public function __construct(array|Dictionary $dbConfig, array $options=[])
    {
        if (isset(static::$dbConn))
            return;

        // Get the database config data
        $getConfig = fn(string $name, mixed $defaultValue=null): mixed => 
            is_array($dbConfig) ?
                ($dbConfig[$name] ?? $defaultValue) :
                ($dbConfig?->$name ?? $defaultValue)
        ;

        $driver     = $getConfig('driver');
        $host       = $getConfig('host');
        $port       = $getConfig('port', 3306);
        $database   = $getConfig('database');
        $username   = $getConfig('username');
        $password   = $getConfig('password');

        if ($port !== null) $host .= ":$port";

        $dbConfigIsValid = array_reduce(
            [$driver, $host, $database, $username, $password],
            function(bool $carry, mixed $item): bool {
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

    public function getDB(): self
    {
        return $this;
    }


    public function executeSQLQuery(QueryBuilder $query): mixed
    {
        try {
            if ($query->getType() === 'select')
                return $this->query((string) $query)?->fetchAll() ?? null;
            else
                return $this->exec((string) $query);
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            $code = (int) $e->getCode();

            // Check for duplicate error (for MySQL)
            $pattern = "/^SQLSTATE\[(\d+)\]: Integrity constraint violation: (\d+) Duplicate entry '(.*)' for key '(.*)'$/";
            if (preg_match($pattern, $message, $matches) === 1) {
                [ , $code_, , $value, $key] = $matches;

                throw new DuplicateEntryException(
                    "Database Exception: Duplicate entry '$value' for key '$key'",
                    (int) $code_
                );
            }

            throw new DatabaseException("Database Exception: $message", (int) $code);
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
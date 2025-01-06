<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Core;

use FranklinEkemezie\PHPAether\Exceptions\{
    DatabaseException,
    DuplicateEntryException,
    UndefinedException
};
use FranklinEkemezie\PHPAether\Utils\{
    Collection,
    Dictionary
};
use FranklinEkemezie\PHPAether\Utils\QueryBuilder\{
    QueryBuilder,
    SelectQueryBuilder,
    InsertQueryBuilder,
    UpdateQueryBuilder,
    DeleteQueryBuilder
};

use PDO;
use PDOException;

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

        } catch (PDOException $e) {
            throw new DatabaseException("Database connection failed: {$e->getMessage()}");
        }
        
    }

    public function getDB(): self
    {
        return $this;
    }


    /**
     * Summary of executeSQLQuery
     * @param SelectQueryBuilder|InsertQueryBuilder|UpdateQueryBuilder|DeleteQueryBuilder $query
     * @param bool $fetchAll
     * @throws \FranklinEkemezie\PHPAether\Exceptions\DatabaseException
     * @throws \FranklinEkemezie\PHPAether\Exceptions\DuplicateEntryException
     * @return \FranklinEkemezie\PHPAether\Utils\Collection|\FranklinEkemezie\PHPAether\Utils\Dictionary|null|true
     */
    public function executeSQLQuery(
        QueryBuilder $query,
        bool $fetchAll=true
    ): Collection|Dictionary|null|true
    {

        try {
            /** @var \PDOStatement */
            $stmt = $this->prepare($query->buildQuery(true));

            switch ($query->getType()) {
                // For SELECT statements
                case QueryBuilder::TYPE_SELECT:
                    $stmt->execute($query->getParams());

                    if (! $fetchAll) {
                        $res = $stmt->fetch() ?: null;
                        return $res !== null ? new Dictionary($res) : null;
                    }

                    $res = $stmt->fetchAll();
                    return ! empty($res) ? new Collection(...$res) : null;
                
                // For INSERT statements
                case QueryBuilder::TYPE_INSERT:
                    /** 
                     * @var bool $startedNewTransaction 
                     * Whether the transaction was initiated here
                     */
                    $startedNewTransaction = false;

                    if (! $this->inTransaction()) {
                        $this->beginTransaction();

                        // this way, we know we started the transaction here,
                        // so, we don't commit changes pre-maturely
                        $startedNewTransaction = true;
                    }
                        $this->beginTransaction();
                    try {
                        foreach($query->getParams() as $params) {
                            $stmt->execute($params);
                        }
                    } catch (PDOException $e) {
                        $this->rollBack();
                        throw $e;
                    }

                    if ($startedNewTransaction) {
                        $this->commit();
                    }
                    
                    return true; 
                           
                // For UPDATE and DELETE statements
                case QueryBuilder::TYPE_UPDATE:
                case QueryBuilder::TYPE_DELETE:
                    return $stmt->execute($query->getParams());
                
                // Invalid/Unsupported statements
                default:
                    throw new DatabaseException("Invalid/Unsupported SQL query: {$query->getType()}");
            }
                
        } catch (PDOException $e) {
            // Check for duplicate error (for MySQL)
            if ($exceptionInfo = static::isDuplicateEntryException($e)) {
                throw new DuplicateEntryException(
                    "Database Exception: Duplicate entry '{$exceptionInfo['value']}' for key '{$exceptionInfo['key']}'",
                    (int) $exceptionInfo['code'],
                    $e
                );
            }

            throw new DatabaseException(
        "Database Exception: {$e->getMessage()}", (int) $e->getCode(), $e
            );
        } 
    }

    /**
     * Check if the exception thrown during a database operation is caused by
     * MySQL duplicate entry constrainst
     * @param PDOException $e The exception
     * 
     */
    protected static function isDuplicateEntryException(PDOException $e): array|false
    {
        $message = $e->getMessage();
        $pattern = "/^SQLSTATE\[(\d+)\]: Integrity constraint violation: (\d+) Duplicate entry '(.*)' for key '(.*)'$/";

        if (preg_match($pattern, $message, $matches) !== 1)
            return false;

        [ , $code, , $value, $key] = $matches;
        return [
            'code'  => $code,
            'key'   => $key,
            'value' => $value
        ];
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
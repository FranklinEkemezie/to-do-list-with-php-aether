<?php

namespace PHPAether\Core\Database\QueryBuilder;

use PHPAether\Enums\QueryBuilderType;
use PHPAether\Exceptions\DatabaseExceptions\QueryBuilderException;

abstract class QueryBuilder
{

    protected array $parameters = [];

    /**
     * @throws QueryBuilderException
     */
    public static function build(QueryBuilderType $queryType, string $table, string ...$tables): QueryBuilder
    {
        return match ($queryType->value) {
            'SELECT'    => static::select($table, ...$tables),
            'INSERT'    => static::insert($table),
            'UPDATE'    => static::update($table, ...$tables),
            'DELETE'    => static::delete($table)
        };
    }

    /**
     * @throws QueryBuilderException
     */
    public static function select(string ...$tables): SelectQueryBuilder
    {
        return new SelectQueryBuilder(...$tables);
    }

    public static function insert(string $table): InsertQueryBuilder
    {
        return new InsertQueryBuilder($table);
    }

    public static function update(string $table): UpdateQueryBuilder
    {
        return new UpdateQueryBuilder($table);
    }

    public static function delete(string $table): DeleteQueryBuilder
    {
        return new DeleteQueryBuilder($table);
    }

    public static function isValidIdentifier(mixed $identifier): bool
    {
        return is_string($identifier) && preg_match("/^[a-zA-Z0-9_]+$/", $identifier) === 1;
    }

    abstract public function sql(): string;

    public function __toString()
    {
        return $this->sql();
    }

}
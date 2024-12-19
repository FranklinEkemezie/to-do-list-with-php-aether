<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Utils\QueryBuilder;

use FranklinEkemezie\PHPAether\Exceptions\QueryBuilderException;

abstract class QueryBuilder
{
    protected string $table;
    protected array $whereCondition;

    public static function build(string $queryType): QueryBuilder
    {
        return match(strtolower($queryType)) {
            'select' => new SelectQueryBuilder(),
            'insert' => new InsertQueryBuilder(),
            'update' => new UpdateQueryBuilder(),
            'delete' => new DeleteQueryBuilder()
        };
    }

    private function checkActionAllowedOnQuery(array $acceptQueries, bool $throw=true): bool
    {
        if (in_array($this->getType(), $acceptQueries))
            return true;

        if ($throw)
            throw new QueryBuilderException("Action not allowed on query");

        return false;
    }

    abstract public function getType(): string;

    /**
     * Set the table
     * 
     * @param string $tableName The name of the table
     * @return \FranklinEkemezie\PHPAether\Utils\QueryBuilder\QueryBuilder
     */
    public function table(string $tableName): self
    {
        $this->table = $tableName;
        return $this;
    }

    protected function getTable(): string
    {
        if (! isset($this->table))
            throw new QueryBuilderException('Table not set');

        return $this->table;
    }

    // WHERE methods: SELECT, UPDATE, DELETE

    public function whereRaw(string $condition, bool $joinWithAnd=false): self
    {
        $this->checkActionAllowedOnQuery(['select', 'update', 'delete']);

        if ((! isset($this->whereCondition))) $this->whereCondition = [];

        array_push($this->whereCondition, [$joinWithAnd ? 'and' : 'or', $condition]);
        return $this;
    }

    public function whereColumnIs(string $column, mixed $value, bool $joinWithAnd=false): self
    {
        if (is_string($value)) $value = "'$value'";

        return $this->whereRaw("$column = $value", $joinWithAnd);
    }

    public function whereColumsAre(array $conditions, bool $joinWithAnd=false): self
    {
        return $this->whereRaw(
            join(' AND ', array_map(
                fn($col): string => "$col = {$conditions[$col]}",
                array_keys($conditions)
            )),
            $joinWithAnd
        );
    }

    public function whereColumnsCanBe(array $conditions, bool $joinWithAnd=false): self
    {
        return $this->whereRaw(
            join(' OR ', array_map(
                fn($col) => "$col = {$conditions[$col]}",
                array_keys($conditions)
            )),
            $joinWithAnd
        );
    }

    public function whereColumnLike(string $column, string $pattern, bool $joinWithAnd=false): self
    {
        return $this->whereRaw("$column LIKE '$pattern'", $joinWithAnd);
    }

    public function whereColumnsLike(array $patterns, bool $joinWithAnd=false): self
    {
        return $this->whereRaw(
            join(' AND ', array_map(
                fn($col) => "$col LIKE '{$patterns[$col]}'",
                array_keys($patterns)
            )),
            $joinWithAnd
        );
    }

    public function whereColumnsCanBeLike(array $patterns, bool $joinWithAnd=false): self
    {
        return $this->whereRaw(
            join(' OR ', array_map(
                fn($col) => "$col LIKE '{$patterns[$col]}'",
                array_keys($patterns)
            )),
            $joinWithAnd
        );
    }

    // Build methods

    protected function buildCondition(array $condition): string
    {
        $conditionString = $condition[0][1] ?? null;
        if ($conditionString === null) return 'false';

        $index = 1;
        while ($index < count($condition)) {
            [$joinWithAnd, $conditionStr] = $condition[$index];

            $andOr = strtoupper($joinWithAnd ? 'and' : 'or');
            $conditionString .= " $andOr $conditionStr";

            $index++;
        }

        return $conditionString;
    }

    protected function buildWhereCondition(): string
    {
        if (! isset($this->whereCondition))
            throw new QueryBuilderException('WHERE condition not set');
        
        return $this->buildCondition($this->whereCondition);
    }

    abstract public function buildQuery(): string;

    public function __toString(): string
    {
        return $this->buildQuery();
    }
}
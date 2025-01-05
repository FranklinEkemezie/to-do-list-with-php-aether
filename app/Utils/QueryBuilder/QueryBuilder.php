<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Utils\QueryBuilder;

use FranklinEkemezie\PHPAether\Exceptions\QueryBuilderException;

abstract class QueryBuilder
{

    // Query TYPE
    public const TYPE_SELECT = 'select';
    public const TYPE_INSERT = 'insert';
    public const TYPE_UPDATE = 'update';
    public const TYPE_DELETE = 'delete';

    protected string $table;
    protected array $whereCondition;
    protected array $params = [];

    /**
     * 
     * @param string $queryType
     * @return \FranklinEkemezie\PHPAether\Utils\QueryBuilder\QueryBuilder
     */
    public static function build(string $queryType): QueryBuilder
    {
        return match(strtolower($queryType)) {
            static::TYPE_SELECT => new SelectQueryBuilder(),
            static::TYPE_INSERT => new InsertQueryBuilder(),
            static::TYPE_UPDATE => new UpdateQueryBuilder(),
            static::TYPE_DELETE => new DeleteQueryBuilder(),
            default  => throw new QueryBuilderException(
                "Invalid or unsupported query type - $queryType"
            )
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

    public function getParams(): array
    {
        return $this->params;
    }

    protected static function isExpression(mixed $expr): bool
    {
        return is_string($expr) && (
            preg_match("/^\(.+\)$/", $expr) === 1
        );
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

        // Set parameters
        $this->params[$column] = $value;

        return $this->whereRaw("$column = :$column", $joinWithAnd);
    }

    public function whereColumnsAre(array $conditions, bool $joinWithAnd=false): self
    {
        return $this->whereRaw(
            join(' AND ', array_map(
                function(string $col) use ($conditions): string {

                    // Set parameters
                    $this->params[$col] = $conditions[$col];

                    return "$col = :$col";
                },
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

    // params
    public function setParams(array $params=[]): self
    {
        $this->params = array_merge($this->params, $params);
        return $this;    
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

    abstract public function buildSQL(): string;

    public function buildQuery(bool $parameterised=false): string
    {
        $sql = $this->buildSQL();

        // Replace :col parameters in SQL, 
        // if parameterisation is not required
        if (! $parameterised) {
            foreach($this->params as $col => $value) {
                if (is_string($value) && ! $this::isExpression($value))
                    $value = "'$value'";

                $sql = str_replace(
                    ":$col", (string) $value, $sql
                );
            }
        }

        return $sql;
    }

    public function __toString(): string
    {
        return $this->buildQuery();
    }
}
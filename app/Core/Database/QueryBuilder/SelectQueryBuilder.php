<?php

namespace PHPAether\Core\Database\QueryBuilder;

use PHPAether\Enums\QueryBuilderType;
use PHPAether\Exceptions\DatabaseExceptions\QueryBuilderException;

class SelectQueryBuilder extends QueryBuilder
{

    public const QUERY_TYPE = QueryBuilderType::SELECT;

    protected array $tables;
    protected array $columns;
    protected array $alias = [];
    protected array $whereConditions = [];


    /**
     * @throws QueryBuilderException
     */
    public function __construct(string ...$tables)
    {
        if (empty($tables)) {
            throw new QueryBuilderException("Parameter \$tables cannot be empty");
        }

        foreach ($tables as $table) {
            if (! self::isValidIdentifier($table)) {
                throw new QueryBuilderException("Table name '$table' is not valid");
            }
        }

        $this->tables = $tables;
    }
    /**
     * @throws QueryBuilderException
     */
    public function columns(string ...$columns): static
    {
        if (empty($columns)) return $this;

        $this->columns ??= [];
        foreach ($columns as $column) {
            $column = str_contains($column, "|") ? $column : "$column|";
            [$column, $alias] = explode("|", $column);

            if (! self::isValidIdentifier($column)) {
                throw new QueryBuilderException("Invalid column name: $column");
            }

            $this->columns[] = $column;
            $this->alias[$column] = $alias ?: null;
        }

        return $this;
    }

    public function where(string $condition): static
    {
        $this->whereConditions[] = $condition;

        return $this;
    }

    public function whereColumnIs(string $column, string|int $value): static
    {
        return $this->where("$column = '$value'");
    }

    public function whereColumnsAre(array $conditions): static
    {
        return $this;
    }

    public function whereColumnsCanBe(array $conditions): static
    {

        return $this;
    }

    private function buildColumns(): string
    {
        return join(", ", array_map(function (string $column) {
            $alias = $this->alias[$column] ?? null;
            return $alias !== null ? "$column AS $alias" : $column;
        }, $this->columns));
    }

    public function sql(): string
    {
        $tableReferences = join(", ", $this->tables);
        $selectExpressions = $this->buildColumns();
        $whereCondition = join(" AND ", $this->whereConditions);

        return "SELECT $selectExpressions FROM $tableReferences WHERE $whereCondition;";
    }
}
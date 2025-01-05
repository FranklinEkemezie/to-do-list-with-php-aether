<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Utils\QueryBuilder;

use FranklinEkemezie\PHPAether\Exceptions\QueryBuilderException;

class InsertQueryBuilder extends QueryBuilder
{

    private array|string $columns;
    private array $values;

    protected function __construct() {}

    public function getType(): string
    {
        return $this::TYPE_INSERT;
    }

    public function columns(string ...$columns): self
    {

        if (isset($this->columns))
            throw new QueryBuilderException('Query columns already set.');

        $this->columns = empty($columns) ? '*' : $columns;
        return $this;
    }

    public function values(mixed ...$values): self
    {
        if (empty($values))
            throw new QueryBuilderException('Cannot provide empty set of values');

        if (! isset($this->columns))
            throw new QueryBuilderException('Query columns not set.');

        if (is_array($this->columns) && count($this->columns) !== count($values))
            throw new QueryBuilderException('The number of values do not match with the provided columns');

        if (! isset($this->values)) $this->values = [];
        array_push($this->values, $values);
        
        return $this;
    }

    public function setValues(array $columnValues): self
    {
        $columns = array_keys($columnValues);
        $values = array_values($columnValues);

        return $this->values(...$values)->columns(...$columns);
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getParams(): array
    {
        return array_reduce($this->values, function(array $params, array $values) {
            $params[] = array_combine($this->columns, $values);
            return $params;
        }, []);
    }

    // Build methods
    private function buildColumns(): string
    {
        if (! isset($this->columns))
            throw new QueryBuilderException('Query columns not set.');

        return join(', ', $this->columns);
    }

    private function buildValues(): string
    {
        if (! isset($this->values))
            throw new QueryBuilderException('Query values not set.');

        return "(" . join(', ', array_map(
            fn(string $value, string $column): string => ":$column",
            $this->values[0], $this->columns
        )) . ")";
    }

    public function buildSQL(): string
    {
        $table      = $this->getTable();
        $columns    = $this->buildColumns();
        $values     = $this->buildValues();

        return "INSERT INTO $table ($columns) VALUES $values;";
    }
}
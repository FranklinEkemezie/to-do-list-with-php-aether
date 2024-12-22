<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Utils\QueryBuilder;

use FranklinEkemezie\PHPAether\Exceptions\QueryBuilderException;

class UpdateQueryBuilder extends QueryBuilder
{

    private array $values;

    public function __construct() {}

    public function getType(): string
    {
        return 'update';
    }

    public function update(array $values): self
    {
        if (! isset($this->values))
            $this->values = [];

        $this->values = array_merge($this->values, $values);
        return $this;
    }

    // Build methods
    public function buildUpdates(): string
    {
        $updateValues = $this->values;
        return join(', ', array_map(
            function(string $column) use ($updateValues): string {
                $value = $updateValues[$column];

                if (is_string($value)) $value = "'$value'";
                return "$column = $value";
            }, 
            array_keys($updateValues)
        ));
    }

    public function buildQuery(): string
    {
        // Basic SQL UPDATE syntax
        $table      = $this->getTable();
        $updates    = $this->buildUpdates();
        $condition  = ! isset($this->whereCondition) ? 'false' : $this->buildWhereCondition();

        return "UPDATE $table SET $updates WHERE $condition;";
    }
}
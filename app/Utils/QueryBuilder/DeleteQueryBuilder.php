<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Utils\QueryBuilder;

class DeleteQueryBuilder extends QueryBuilder
{

    public function __construct() {}

    public function getType(): string
    {
        return 'delete';
    }

    public function buildQuery(): string
    {
        // Basic SQL DELETE syntax
        $table      = $this->getTable();
        $condition  = ! isset($this->whereCondition) ? 'false' : $this->buildWhereCondition();

        return "DELETE FROM $table WHERE $condition;";
    }
    
}